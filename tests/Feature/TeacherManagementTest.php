<?php

namespace Tests\Feature;

use App\Filament\Resources\Programmes\Pages\CreateProgramme;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Programme;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_a_student_without_a_new_password_keeps_the_existing_password(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id]);
        $hash = $student->password;
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(EditUser::class, ['record' => $student->id])
            ->assertFormSet(['password' => null])
            ->fillForm(['category' => 'G'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame($hash, $student->fresh()->password);
        $this->assertSame('G', $student->fresh()->category);
    }

    public function test_teacher_can_create_ordered_lesson_and_assessment_content(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(CreateProgramme::class)
            ->fillForm(['title' => 'Teacher-authored programme', 'published' => false, 'activities' => [
                ['title' => 'Read this lesson', 'type' => 'lesson', 'cycle' => 1, 'content' => 'A teaching example.'],
                ['title' => 'A short check', 'type' => 'checkpoint', 'cycle' => 1, 'questions' => [
                    ['prompt' => 'Which particle is negative?', 'options' => [['option' => 'Proton'], ['option' => 'Electron']], 'correct' => 1],
                ]],
            ]])->call('create')->assertHasNoFormErrors();
        $programme = Programme::firstOrFail();
        $this->assertSame($teacher->id, $programme->teacher_id);
        $this->assertSame(['Read this lesson', 'A short check'], $programme->activities->pluck('title')->all());
        $this->assertSame('Electron', $programme->activities->last()->questions[0]['options'][1]);
    }

    public function test_correct_option_must_be_one_of_the_listed_options(): void
    {
        $this->actingAsTeacherInPanel();
        Livewire::test(CreateProgramme::class)
            ->fillForm(['title' => 'Draft with a check', 'published' => false, 'activities' => [
                ['title' => 'A short check', 'type' => 'checkpoint', 'cycle' => 1, 'questions' => [
                    ['prompt' => 'Which particle is negative?', 'options' => [['option' => 'Proton'], ['option' => 'Electron']], 'correct' => 2],
                ]],
            ]])->call('create')->assertHasFormErrors(['activities.0.questions.0.correct']);
        $this->assertSame(0, Programme::count());
    }

    public function test_google_form_link_must_point_to_google_forms(): void
    {
        $this->actingAsTeacherInPanel();
        Livewire::test(CreateProgramme::class)
            ->fillForm(['title' => 'Forms draft', 'published' => false, 'activities' => [
                ['title' => 'Research pre-test', 'type' => 'pre_test', 'cycle' => 1, 'assessment_source' => 'google_forms', 'google_form_url' => 'https://example.com/form'],
            ]])->call('create')->assertHasFormErrors(['activities.0.google_form_url']);
        $this->assertSame(0, Programme::count());
    }

    public function test_publishing_an_incomplete_programme_names_the_missing_stages(): void
    {
        $this->actingAsTeacherInPanel();
        $page = Livewire::test(CreateProgramme::class)
            ->fillForm(['title' => 'Half ready', 'published' => true, 'activities' => [
                ['title' => 'Read this lesson', 'type' => 'lesson', 'cycle' => 1, 'content' => 'A teaching example.'],
            ]])->call('create')->assertHasFormErrors(['published']);
        $message = $page->errors()->first('data.published');
        $this->assertStringContainsString('Cycle 1: Pre-test, Video lesson, Post-test', $message);
        $this->assertStringContainsString('Cycle 2: Pre-test, Module, Video lesson, Post-test', $message);
        $page->assertNotified('The programme was not saved');
        $this->assertSame(0, Programme::count());
    }

    private function actingAsTeacherInPanel(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'teacher']));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }
}
