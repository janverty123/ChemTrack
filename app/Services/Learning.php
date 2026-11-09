<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Attempt;
use App\Models\Completion;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Learning
{
    public function completedIds(User $user): array
    {
        return array_unique(array_merge(
            Completion::where('user_id', $user->id)->pluck('activity_id')->all(),
            Attempt::where('user_id', $user->id)->whereNotNull('submitted_at')->pluck('activity_id')->all(),
        ));
    }

    public function authorize(User $user, Activity $activity): void
    {
        abort_unless($user->role === 'student', 403);
        abort_unless($user->programmes()->whereKey($activity->programme_id)->where('published', true)->exists(), 404);
        $done = $this->completedIds($user);
        $reason = app(CycleProgress::class)->lockReason($user, $activity, $done);
        abort_if($reason !== null, 403, $reason ?? 'Activity locked.');
    }

    public function attempt(User $user, Activity $activity): Attempt
    {
        return DB::transaction(function () use ($user, $activity) {
            Programme::whereKey($activity->programme_id)->lockForUpdate()->firstOrFail();
            $activity->refresh();
            $this->authorize($user, $activity);
            abort_if(in_array($activity->type, ['lesson', 'video']), 422);
            abort_if($activity->assessment_source === 'google_forms', 422, 'This research test requires a verified Google Forms response.');

            return Attempt::firstOrCreate(['user_id' => $user->id, 'activity_id' => $activity->id], ['answers' => [], 'started_at' => now()]);
        }, 3);
    }

    public function save(User $user, Activity $activity, array $answers, bool $submit): Attempt
    {
        return DB::transaction(function () use ($user, $activity, $answers, $submit) {
            $attempt = $this->attempt($user, $activity);
            if ($attempt->submitted_at) {
                abort_unless($submit, 409, 'This assessment is already submitted.');

                return $attempt;
            }
            $questions = $activity->questions;
            foreach ($answers as $index => $answer) {
                if (! ctype_digit((string) $index) || ! isset($questions[$index]) ||
                    (! is_null($answer) && (! is_int($answer) && ! ctype_digit((string) $answer) || ! array_key_exists((int) $answer, $questions[$index]['options'])))) {
                    throw ValidationException::withMessages(['answers' => 'Choose a valid option for each question.']);
                }
            }
            $normalized = [];
            foreach ($questions as $index => $question) {
                $normalized[$index] = isset($answers[$index]) ? (int) $answers[$index] : null;
            }
            if ($submit && in_array(null, $normalized, true)) {
                throw ValidationException::withMessages(['answers' => 'Answer every question before submitting.']);
            }
            // Preserve a compact prefix on partial saves while retaining gaps between answers.
            while ($normalized && end($normalized) === null) {
                array_pop($normalized);
            }
            $attempt->answers = $normalized;
            $attempt->saved_at = now();
            if ($submit) {
                $correct = 0;
                foreach ($questions as $i => $question) {
                    $correct += $normalized[$i] === (int) $question['correct'] ? 1 : 0;
                }
                $attempt->score = (int) round($correct / count($questions) * 100);
                $attempt->submitted_at = now();
            }
            $attempt->save();

            return $attempt;
        }, 3);
    }

    public function complete(User $user, Activity $activity, bool $videoAcknowledged = false): void
    {
        DB::transaction(function () use ($user, $activity, $videoAcknowledged) {
            Programme::whereKey($activity->programme_id)->lockForUpdate()->firstOrFail();
            $activity->refresh();
            $this->authorize($user, $activity);
            abort_unless(in_array($activity->type, ['lesson', 'video']), 422);
            abort_if($activity->type === 'video' && ! $activity->video_url, 422, 'Your teacher has not added the video lesson yet.');
            if ($activity->type === 'video' && ! $videoAcknowledged) {
                throw ValidationException::withMessages(['video_acknowledged' => 'Confirm that you have finished the video lesson.']);
            }
            Completion::firstOrCreate(['user_id' => $user->id, 'activity_id' => $activity->id], ['completed_at' => now()]);
        }, 3);
    }
}
