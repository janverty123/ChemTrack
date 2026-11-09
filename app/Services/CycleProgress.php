<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CycleProgress
{
    public const STAGES = ['pre_test' => 'Pre-test', 'module' => 'Module', 'video' => 'Video lesson', 'post_test' => 'Post-test'];

    public function stage(Activity $activity): string
    {
        return in_array($activity->type, ['lesson', 'checkpoint']) ? 'module' : $activity->type;
    }

    public function configured(Activity $activity): bool
    {
        if ($activity->type === 'video') {
            return (bool) $activity->video_url;
        }
        if ($activity->type === 'lesson') {
            return (bool) ($activity->document_path || trim($activity->content ?? ''));
        }

        return $activity->assessment_source === 'google_forms' ? (bool) $activity->google_form_url : count($activity->questions ?? []) > 0;
    }

    public function summary(Programme $programme, int $cycle, array $completed): array
    {
        abort_unless(in_array($cycle, [1, 2], true), 422);
        $activities = $programme->activities->where('cycle', $cycle);
        $stages = [];
        foreach (self::STAGES as $key => $label) {
            $items = $activities->filter(fn ($activity) => $this->stage($activity) === $key)->values();
            $configured = $items->isNotEmpty() && $items->every(fn ($activity) => $this->configured($activity));
            if ($programme->cycle_workflow && $programme->activities->contains(fn ($activity) => $activity->cycle === null)) {
                $configured = false;
            }
            if ($key === 'module') {
                $configured = $configured && $items->contains('type', 'lesson');
            }
            $complete = $configured && $items->every(fn ($activity) => in_array($activity->id, $completed));
            $stages[$key] = compact('label', 'items', 'configured', 'complete');
        }
        $done = collect($stages)->where('complete', true)->count();

        return ['cycle' => $cycle, 'done' => $done, 'total' => 4, 'percent' => $done * 25, 'stages' => $stages, 'ready' => collect($stages)->every(fn ($stage) => $stage['configured'])];
    }

    public function released(Programme $programme): bool
    {
        return DB::table('cycle_releases')->where('programme_id', $programme->id)->exists();
    }

    public function lockReason(User $student, Activity $activity, array $completed): ?string
    {
        // Recorded work stays reviewable when migrating an older, incomplete programme map.
        if (in_array($activity->id, $completed)) {
            return null;
        }
        $programme = $activity->programme;
        if ($programme->cycle_workflow && ! $activity->cycle) {
            return 'Your teacher must assign this activity to a cycle first.';
        }
        if ((int) $activity->cycle === 2) {
            if ($this->summary($programme, 1, $completed)['done'] !== 4) {
                return 'Finish Cycle 1 before starting Cycle 2.';
            }
            if (! $this->released($programme)) {
                return 'Waiting for your teacher to open Cycle 2.';
            }
        }
        if ($programme->cycle_workflow && $activity->cycle) {
            $summary = $this->summary($programme, (int) $activity->cycle, $completed);
            $current = $this->stage($activity);
            foreach ($summary['stages'] as $key => $stage) {
                if ($key === $current) {
                    foreach ($stage['items'] as $item) {
                        if ($item->id === $activity->id) {
                            return null;
                        }
                        if (! in_array($item->id, $completed)) {
                            return 'Complete the previous module part first.';
                        }
                    }
                }
                if (! $stage['complete']) {
                    return $stage['configured'] ? 'Complete the '.$stage['label'].' first.' : 'Your teacher has not configured the '.$stage['label'].' yet.';
                }
            }
        }
        $previous = $programme->activities->takeUntil(fn ($item) => $item->id === $activity->id);

        return $previous->contains(fn ($item) => ! in_array($item->id, $completed)) ? 'Complete the preceding activity first.' : null;
    }

    public function release(User $teacher, Programme $programme): void
    {
        abort_unless($teacher->role === 'teacher', 403);
        abort_unless($programme->teacher_id === $teacher->id, 404);
        DB::transaction(function () use ($teacher, $programme) {
            $programme = Programme::whereKey($programme->id)->lockForUpdate()->firstOrFail();
            if (! $programme->published || ! $this->summary($programme, 1, [])['ready'] || ! $this->summary($programme, 2, [])['ready']) {
                throw ValidationException::withMessages(['cycle' => 'Configure and publish all four stages in both cycles before opening Cycle 2.']);
            }
            DB::table('cycle_releases')->insertOrIgnore(['programme_id' => $programme->id, 'released_by' => $teacher->id, 'released_at' => now()]);
        }, 3);
    }
}
