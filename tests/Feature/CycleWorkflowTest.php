<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Completion;
use App\Models\Programme;
use App\Models\User;
use App\Services\CycleProgress;
use App\Services\Learning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CycleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function path(): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['teacher_id' => $teacher->id, 'role' => 'student']);
        $programme = Programme::create(['teacher_id' => $teacher->id, 'title' => 'Two cycles', 'cycle_workflow' => true]);
        foreach ([1, 2] as $cycle) {
            foreach (['pre_test', 'lesson', 'video', 'post_test'] as $index => $type) {
                $programme->activities()->create(['title' => "Cycle $cycle $type", 'cycle' => $cycle, 'type' => $type, 'position' => ($cycle - 1) * 4 + $index,
                    'content' => $type === 'lesson' ? 'Synthetic reading' : null,
                    'video_url' => $type === 'video' ? 'https://example.test/video' : null,
                    'questions' => in_array($type, ['pre_test', 'post_test']) ? [['prompt' => 'Synthetic question', 'options' => ['A', 'B'], 'correct' => 0]] : null]);
            }
        }
        $programme->update(['published' => true]);
        $programme->students()->attach($student);

        return [$teacher, $student, $programme->fresh('activities')];
    }

    private function finish(User $student, $activities): void
    {
        foreach ($activities as $activity) {
            if (in_array($activity->type, ['lesson', 'video'])) {
                Completion::create(['user_id' => $student->id, 'activity_id' => $activity->id, 'completed_at' => now()]);
            } else {
                Attempt::create(['user_id' => $student->id, 'activity_id' => $activity->id, 'answers' => [0], 'score' => 100, 'started_at' => now(), 'submitted_at' => now()]);
            }
        }
    }

    public function test_each_cycle_has_four_stages_and_switching_changes_its_total(): void
    {
        [, $student, $programme] = $this->path();
        $this->finish($student, $programme->activities->where('cycle', 1));
        $this->finish($student, $programme->activities->where('cycle', 2)->take(1));
        $progress = app(CycleProgress::class);
        $done = app(Learning::class)->completedIds($student);
        $this->assertSame(100, $progress->summary($programme, 1, $done)['percent']);
        $this->assertSame(25, $progress->summary($programme, 2, $done)['percent']);
        $this->actingAs($student)->get('/learn?cycle=2')->assertOk()->assertSee('Cycle 2 completion: 25 percent, 1 of 4 stages completed');
    }

    public function test_cycle_two_needs_both_cycle_one_and_teacher_release(): void
    {
        [$teacher, $student, $programme] = $this->path();
        $second = $programme->activities->where('cycle', 2)->first();
        $this->actingAs($student)->get('/learn/activities/'.$second->id)->assertForbidden();
        $this->finish($student, $programme->activities->where('cycle', 1));
        $this->get('/learn/activities/'.$second->id)->assertForbidden();
        $this->actingAs($teacher)->post('/teacher/programmes/'.$programme->id.'/open-cycle-two')->assertRedirect();
        $this->actingAs($student)->get('/learn/activities/'.$second->id)->assertOk();
        $unfinished = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id]);
        $programme->students()->attach($unfinished);
        $this->actingAs($unfinished)->get('/learn/activities/'.$second->id)->assertForbidden();
    }

    public function test_foreign_teacher_and_student_cannot_release_and_missing_setup_is_rejected(): void
    {
        [$teacher, $student, $programme] = $this->path();
        $this->actingAs($student)->post('/teacher/programmes/'.$programme->id.'/open-cycle-two')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'teacher']))->post('/teacher/programmes/'.$programme->id.'/open-cycle-two')->assertNotFound();
        $programme->activities->where('cycle', 2)->where('type', 'video')->first()->delete();
        $this->actingAs($teacher)->post('/teacher/programmes/'.$programme->id.'/open-cycle-two')->assertSessionHasErrors('cycle');
    }

    public function test_missing_stages_do_not_inflate_progress_and_checkpoints_belong_to_module(): void
    {
        [, $student, $programme] = $this->path();
        $checkpoint = $programme->activities()->create(['title' => 'Embedded assessment', 'cycle' => 1, 'type' => 'checkpoint', 'position' => 2, 'questions' => [['prompt' => 'A?', 'options' => ['A', 'B'], 'correct' => 0]]]);
        $lesson = $programme->activities->where('cycle', 1)->where('type', 'lesson')->first();
        $this->finish($student, collect([$lesson]));
        $summary = app(CycleProgress::class)->summary($programme->fresh('activities'), 1, app(Learning::class)->completedIds($student));
        $this->assertSame(0, $summary['percent']);
        $this->finish($student, collect([$checkpoint]));
        $this->assertSame(25, app(CycleProgress::class)->summary($programme->fresh('activities'), 1, app(Learning::class)->completedIds($student))['percent']);
        $empty = Programme::create(['teacher_id' => $programme->teacher_id, 'title' => 'No setup']);
        $this->assertSame(0, app(CycleProgress::class)->summary($empty, 1, [])['percent']);
    }

    public function test_unassigned_required_work_blocks_strict_readiness_and_eligibility(): void
    {
        [$teacher, $student, $programme] = $this->path();
        $unassigned = $programme->activities()->create(['title' => 'Unmapped checkpoint', 'type' => 'checkpoint', 'position' => 0, 'questions' => [['prompt' => 'A?', 'options' => ['A', 'B'], 'correct' => 0]]]);
        $this->assertFalse(app(CycleProgress::class)->summary($programme->fresh('activities'), 1, [])['ready']);
        $this->actingAs($student)->get('/learn/activities/'.$unassigned->id)->assertForbidden();
        $this->actingAs($teacher)->post('/teacher/programmes/'.$programme->id.'/open-cycle-two')->assertSessionHasErrors('cycle');
    }

    public function test_video_completion_requires_acknowledgement_after_refresh_and_retries_are_safe(): void
    {
        [, $student, $programme] = $this->path();
        $video = $programme->activities->where('cycle', 1)->where('type', 'video')->first();
        $this->finish($student, $programme->activities->where('cycle', 1)->whereIn('type', ['pre_test', 'lesson']));
        $this->actingAs($student)->post('/learn/activities/'.$video->id.'/complete')->assertSessionHasErrors('video_acknowledged');
        $this->post('/learn/activities/'.$video->id.'/complete', ['video_acknowledged' => 1])->assertRedirect();
        $this->post('/learn/activities/'.$video->id.'/complete', ['video_acknowledged' => 1])->assertRedirect();
        $this->assertSame(1, Completion::where('activity_id', $video->id)->count());
    }

    public function test_cycle_selection_is_remembered_and_csv_includes_missing_stage_statuses(): void
    {
        [$teacher, $student, $programme] = $this->path();
        $this->actingAs($student)->get('/learn?cycle=2')->assertOk();
        $this->get('/learn')->assertViewHas('cycle', 2)->assertSee('Cycle 2 completion: 0 percent');
        $programme->activities->where('cycle', 2)->where('type', 'video')->first()->delete();
        $csv = $this->actingAs($teacher)->get('/teacher/programmes/'.$programme->id.'/export')->assertOk()->streamedContent();
        $this->assertStringContainsString('Cycle completion percent', $csv);
        $this->assertStringContainsString('not available yet', $csv);
        $this->assertStringContainsString('Stage summary', $csv);
    }
}
