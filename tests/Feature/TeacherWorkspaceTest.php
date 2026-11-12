<?php

namespace Tests\Feature;

use App\Filament\Resources\Programmes\Pages\EditProgramme;
use App\Filament\Resources\Programmes\Pages\ViewProgramme;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\Completion;
use App\Models\Programme;
use App\Models\ReadingProgress;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private function workspace(): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $other = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id, 'student_code' => 'ST81-G', 'name' => 'Private name sentinel', 'email' => 'private-student@example.test']);
        $peer = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id, 'student_code' => 'ST82-B']);
        $foreign = User::factory()->create(['role' => 'student', 'teacher_id' => $other->id, 'student_code' => 'ST99-G']);
        $programme = Programme::create(['teacher_id' => $teacher->id, 'title' => 'Owned chemistry module', 'published' => true]);
        $lesson = $programme->activities()->create(['title' => 'Sample atoms reading', 'type' => 'lesson', 'cycle' => 1, 'content' => 'Synthetic lesson.']);
        $programme->students()->attach([$student->id, $peer->id]);
        $outside = Programme::create(['teacher_id' => $other->id, 'title' => 'Foreign programme sentinel', 'published' => true]);
        $outside->students()->attach($student);

        return [$teacher, $other, $student, $peer, $foreign, $programme, $lesson];
    }

    public function test_teacher_overview_links_to_owned_programmes_and_student_progress_only(): void
    {
        [$teacher, , $student] = $this->workspace();
        $this->actingAs($teacher)->get('/teacher')->assertOk()
            ->assertSee('Teaching workspace')
            ->assertSee('Owned chemistry module')
            ->assertSee('ST81-G')->assertSee('ST82-B')
            ->assertSee('/teacher/users/'.$student->id, false)
            ->assertDontSee('Foreign programme sentinel')->assertDontSee('ST99-G')
            ->assertDontSee('Private name sentinel')->assertDontSee('private-student@example.test');
    }

    public function test_student_overview_keeps_reading_separate_and_never_sends_legacy_identity(): void
    {
        [$teacher, , $student, , , , $lesson] = $this->workspace();
        ReadingProgress::create(['user_id' => $student->id, 'activity_id' => $lesson->id, 'content_version' => $lesson->contentVersion(), 'position' => 100, 'furthest_position' => 100, 'saved_at' => now()]);
        $this->actingAs($teacher)->get('/teacher/users/'.$student->id)->assertOk()
            ->assertSee('ST81-G')->assertSee('Owned chemistry module')
            ->assertSee('0 of 4 stages completed')->assertSee('100% viewed')
            ->assertSee('In progress')->assertDontSee('Foreign programme sentinel')
            ->assertDontSee('ST82-B')->assertDontSee('Private name sentinel')
            ->assertDontSee('private-student@example.test');
    }

    public function test_student_overview_is_denied_to_foreign_teachers_and_students(): void
    {
        [$teacher, $other, $student, , $foreign] = $this->workspace();
        $this->actingAs($other)->get('/teacher/users/'.$student->id)->assertNotFound();
        $this->actingAs($teacher)->get('/teacher/users/'.$foreign->id)->assertNotFound();
        $this->actingAs($student)->get('/teacher/users/'.$student->id)->assertForbidden();
    }

    public function test_teacher_cycle_filter_changes_selected_cycle_completion(): void
    {
        [$teacher, , $student, , , $programme, $lesson] = $this->workspace();
        $programme->activities()->create(['title' => 'Second cycle reading sentinel', 'type' => 'lesson', 'cycle' => 2, 'position' => 2, 'content' => 'Synthetic lesson.']);
        Completion::create(['user_id' => $student->id, 'activity_id' => $lesson->id, 'completed_at' => now()]);
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(ViewUser::class, ['record' => $student->id])
            ->assertSee('Cycle 1 completion: 25 percent, 1 of 4 stages completed')
            ->assertSee('Sample atoms reading')->assertDontSee('Second cycle reading sentinel')
            ->call('selectCycle', 2)
            ->assertSee('Cycle 2 completion: 0 percent, 0 of 4 stages completed')
            ->assertSee('Second cycle reading sentinel')->assertDontSee('Sample atoms reading');
    }

    public function test_teacher_ring_keeps_viewed_reading_separate_from_completion(): void
    {
        [$teacher, , $student, , , , $lesson] = $this->workspace();
        ReadingProgress::create(['user_id' => $student->id, 'activity_id' => $lesson->id, 'content_version' => $lesson->contentVersion(), 'position' => 100, 'furthest_position' => 100, 'saved_at' => now()]);
        $this->actingAs($teacher)->get('/teacher/users/'.$student->id)->assertOk()
            ->assertSee('Cycle 1 completion: 0 percent, 0 of 4 stages completed')
            ->assertSee('100 percent viewed')->assertSee('In progress');
    }

    public function test_legacy_student_overview_uses_neutral_reference_and_has_an_empty_state(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id, 'name' => 'Legacy identity sentinel', 'email' => 'legacy-identity@example.test']);
        $this->actingAs($teacher)->get('/teacher/users/'.$student->id)->assertOk()
            ->assertSee($student->studentReference())->assertSee('No programme assigned yet.')
            ->assertDontSee('Legacy identity sentinel')->assertDontSee('legacy-identity@example.test');
    }

    public function test_programme_report_excludes_an_incorrectly_attached_foreign_student(): void
    {
        [$teacher, , , , $foreign, $programme] = $this->workspace();
        $programme->students()->attach($foreign);
        $this->actingAs($teacher)->get('/teacher/programmes/'.$programme->id.'/results')->assertOk()
            ->assertSee('ST81-G')->assertDontSee('ST99-G');
    }

    public function test_started_programme_can_be_viewed_but_cannot_be_edited(): void
    {
        [$teacher, $other, $student, , , $programme, $lesson] = $this->workspace();
        ReadingProgress::create(['user_id' => $student->id, 'activity_id' => $lesson->id, 'content_version' => $lesson->contentVersion(), 'position' => 20, 'furthest_position' => 20, 'saved_at' => now()]);
        $this->actingAs($teacher)->get('/teacher/programmes/'.$programme->id)->assertOk()
            ->assertSee('Content is read-only')->assertSee('Enroll students')
            ->assertDontSee('Edit content');
        $this->get('/teacher/programmes/'.$programme->id.'/edit')->assertForbidden();
        $this->actingAs($other)->get('/teacher/programmes/'.$programme->id)->assertNotFound();
    }

    public function test_programme_view_can_enroll_an_owned_student_without_changing_existing_assignments(): void
    {
        [$teacher, , , , , $programme] = $this->workspace();
        $newStudent = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id, 'student_code' => 'ST83-G']);
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(ViewProgramme::class, ['record' => $programme->id])
            ->callAction('enroll', data: ['students' => [$newStudent->id]])->assertHasNoActionErrors();
        $this->assertSame(3, $programme->students()->count());
        $this->assertTrue($programme->students()->whereKey($newStudent->id)->exists());
    }

    public function test_programme_view_rejects_enrolling_another_teachers_student(): void
    {
        [$teacher, , , , $foreign, $programme] = $this->workspace();
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(ViewProgramme::class, ['record' => $programme->id])
            ->callAction('enroll', data: ['students' => [$foreign->id]]);
        $this->assertSame(2, $programme->students()->count());
        $this->assertFalse($programme->students()->whereKey($foreign->id)->exists());
    }

    public function test_teacher_programme_view_loads_existing_native_questions(): void
    {
        [$teacher, , , , , $programme] = $this->workspace();
        $programme->activities()->create(['title' => 'Native checkpoint', 'type' => 'checkpoint', 'position' => 2, 'questions' => [
            ['prompt' => 'Question sentinel: which particle is negative?', 'options' => ['Proton', 'Electron'], 'correct' => 1],
        ]]);
        $this->actingAs($teacher)->get('/teacher/programmes/'.$programme->id)->assertOk()
            ->assertSee('Question sentinel: which particle is negative?')->assertSee('Electron');
    }

    public function test_saving_an_unstarted_programme_preserves_existing_questions_and_answer_keys(): void
    {
        [$teacher, , , , , $programme] = $this->workspace();
        $questions = [['prompt' => 'Which particle is negative?', 'options' => ['Proton', 'Electron'], 'correct' => 1]];
        $quiz = $programme->activities()->create(['title' => 'Existing checkpoint', 'type' => 'checkpoint', 'cycle' => 1, 'position' => 2, 'questions' => $questions]);
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(EditProgramme::class, ['record' => $programme->id])->call('save')->assertHasNoFormErrors();
        $this->assertSame($questions, $quiz->fresh()->questions);
    }
}
