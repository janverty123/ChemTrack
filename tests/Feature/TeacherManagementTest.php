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

    public function test_editing_student_name_does_not_expose_or_replace_password(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id]);
        $hash = $student->password;
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(EditUser::class, ['record' => $student->id])
            ->assertFormSet(['password' => null])
            ->fillForm(['name' => 'Updated student'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame($hash, $student->fresh()->password);
        $this->assertSame('Updated student', $student->fresh()->name);
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
}
