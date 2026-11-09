<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Completion;
use App\Models\Programme;
use App\Models\ReadingProgress;
use App\Models\User;

class ProgressReport
{
    public function forProgramme(User $teacher, Programme $programme, ?User $student = null): array
    {
        abort_unless($teacher->role === 'teacher' && $programme->teacher_id === $teacher->id, 404);
        if ($student) {
            abort_unless($student->role === 'student' && $student->teacher_id === $teacher->id, 404);
        }
        $programme->load(['activities', 'students' => fn ($query) => $query->where('teacher_id', $teacher->id)->where('role', 'student')->when($student, fn ($query) => $query->whereKey($student->id))]);
        $ids = $programme->activities->pluck('id');
        $attempts = Attempt::with('activity')->whereIn('activity_id', $ids)->when($student, fn ($query) => $query->where('user_id', $student->id))->get();
        $completions = Completion::whereIn('activity_id', $ids)->when($student, fn ($query) => $query->where('user_id', $student->id))->get();
        $readings = ReadingProgress::whereIn('activity_id', $ids)->when($student, fn ($query) => $query->where('user_id', $student->id))->get();
        $rows = [];
        $cycles = [];
        $stageRows = [];
        $cycleProgress = app(CycleProgress::class);
        foreach ($programme->students as $learner) {
            $completedIds = array_unique(array_merge($attempts->where('user_id', $learner->id)->whereNotNull('submitted_at')->pluck('activity_id')->all(), $completions->where('user_id', $learner->id)->pluck('activity_id')->all()));
            $cycles[$learner->id] = [1 => $cycleProgress->summary($programme, 1, $completedIds), 2 => $cycleProgress->summary($programme, 2, $completedIds)];
            foreach ([1, 2] as $cycle) {
                foreach ($cycles[$learner->id][$cycle]['stages'] as $stage => $summary) {
                    $stageRows[] = ['student' => $learner->studentReference(), 'cycle' => $cycle, 'stage' => $stage, 'cycle_percent' => $cycles[$learner->id][$cycle]['percent'], 'status' => $summary['complete'] ? 'complete' : ($summary['configured'] ? 'pending' : 'not available yet')];
                }
            }
            foreach ($programme->activities as $activity) {
                $attempt = $attempts->first(fn ($item) => $item->user_id === $learner->id && $item->activity_id === $activity->id);
                $completion = $completions->first(fn ($item) => $item->user_id === $learner->id && $item->activity_id === $activity->id);
                $reading = $readings->first(fn ($item) => $item->user_id === $learner->id && $item->activity_id === $activity->id);
                $activity->setRelation('programme', $programme);
                $locked = $cycleProgress->lockReason($learner, $activity, $completedIds) !== null;
                $isComplete = (bool) ($attempt?->submitted_at || $completion);
                $status = $isComplete ? 'complete' : ($locked ? 'locked' : (($attempt || $reading) ? 'in progress' : 'not started'));
                if (! $isComplete && ! $locked && $activity->assessment_source === 'google_forms') {
                    $status = 'verification not connected';
                }
                $viewed = $reading && $reading->content_version === $activity->contentVersion() ? $reading->progressPercent() : null;
                $answered = count(array_filter($attempt?->answers ?? [], fn ($answer) => $answer !== null));
                $progress = $isComplete ? 100 : ($activity->type === 'lesson' ? ($viewed ?? 0) : min(99, (int) floor($answered / max(1, count($activity->questions ?? [])) * 100)));
                $rows[] = [
                    'student' => $learner->studentReference(), 'activity' => $activity->title, 'type' => $activity->type,
                    'cycle' => $activity->cycle, 'cycle_percent' => $activity->cycle ? $cycles[$learner->id][$activity->cycle]['percent'] : null, 'stage' => $cycleProgress->stage($activity), 'stage_complete' => $activity->cycle ? $cycles[$learner->id][$activity->cycle]['stages'][$cycleProgress->stage($activity)]['complete'] : null, 'reading' => $viewed, 'progress' => $progress,
                    'score' => $attempt?->submitted_at ? $attempt->score : null,
                    'started_at' => $attempt?->started_at, 'submitted_at' => $attempt?->submitted_at,
                    'completed_at' => $attempt?->submitted_at ?? $completion?->completed_at,
                    'status' => $status, 'evidence' => $attempt?->submitted_at ? 'native assessment submission' : ($completion ? 'lesson acknowledgement' : ''),
                ];
            }
        }

        return compact('programme', 'attempts', 'completions', 'readings', 'rows', 'cycles', 'stageRows');
    }
}
