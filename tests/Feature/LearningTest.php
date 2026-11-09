<?php

namespace Tests\Feature;

use App\Http\Controllers\LearningController;
use App\Models\Activity;
use App\Models\Attempt;
use App\Models\Programme;
use App\Models\User;
use App\Services\Learning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LearningTest extends TestCase
{
    use RefreshDatabase;

    private function programme(User $student): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student->update(['teacher_id' => $teacher->id]);
        $programme = Programme::create(['teacher_id' => $teacher->id, 'title' => 'Chemistry', 'description' => 'Demo', 'published' => true]);
        $lesson = $programme->activities()->create(['title' => 'Atoms', 'type' => 'lesson', 'position' => 1, 'content' => 'An atom has a nucleus.']);
        $quiz = $programme->activities()->create(['title' => 'Check understanding', 'type' => 'checkpoint', 'position' => 2, 'questions' => [
            ['prompt' => 'Which particle is negative?', 'options' => ['Proton', 'Electron', 'Neutron'], 'correct' => 1],
            ['prompt' => 'Which particle is positive?', 'options' => ['Proton', 'Electron', 'Neutron'], 'correct' => 0],
        ]]);
        $programme->students()->attach($student);

        return [$programme, $lesson, $quiz, $teacher];
    }

    public function test_enrollment_and_sequential_eligibility_are_enforced(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        [$programme, $lesson, $quiz] = $this->programme($student);
        $this->actingAs($student)->get('/learn')->assertOk()->assertSee('Chemistry');
        $this->get('/learn/activities/'.$quiz->id)->assertForbidden();
        $this->post('/learn/activities/'.$lesson->id.'/complete')->assertRedirect('/learn');
        $this->get('/learn/activities/'.$quiz->id)->assertOk()->assertDontSee('"correct"', false);
        $outsider = User::factory()->create(['role' => 'student']);
        $this->actingAs($outsider)->get('/learn/activities/'.$lesson->id)->assertNotFound();
        $this->post('/learn/activities/'.$quiz->id.'/save', ['answers' => [1, 0]])->assertNotFound();
    }

    public function test_answers_resume_and_final_score_cannot_be_overwritten(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        [, $lesson, $quiz] = $this->programme($student);
        $this->actingAs($student)->post('/learn/activities/'.$lesson->id.'/complete');
        $this->get('/learn/activities/'.$quiz->id)->assertOk();
        $this->postJson('/learn/activities/'.$quiz->id.'/save', ['answers' => [1]])->assertOk();
        $this->assertSame([1], Attempt::first()->answers);
        $this->get('/learn/activities/'.$quiz->id)->assertViewHas('attempt', fn ($a) => $a->answers === [1]);
        $this->post('/learn/activities/'.$quiz->id.'/submit', ['answers' => [1, 2]])->assertRedirect();
        $this->assertSame(50, Attempt::first()->score);
        $this->post('/learn/activities/'.$quiz->id.'/submit', ['answers' => [0, 0]])->assertRedirect();
        $this->assertSame(50, Attempt::first()->score);
        $this->postJson('/learn/activities/'.$quiz->id.'/save', ['answers' => [0, 0]])->assertStatus(409);
        $this->assertSame(1, Attempt::count());
    }

    public function test_invalid_and_incomplete_answers_are_rejected_and_zero_is_a_valid_score(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        [, $lesson, $quiz] = $this->programme($student);
        $this->actingAs($student)->post('/learn/activities/'.$lesson->id.'/complete');
        $this->postJson('/learn/activities/'.$quiz->id.'/save', ['answers' => [99]])->assertUnprocessable();
        $this->post('/learn/activities/'.$quiz->id.'/submit', ['answers' => [1]])->assertSessionHasErrors('answers');
        $this->post('/learn/activities/'.$quiz->id.'/submit', ['answers' => [0, 2]])->assertRedirect();
        $this->assertSame(0, Attempt::first()->score);
    }

    public function test_started_programmes_cannot_be_edited_and_teacher_reports_are_scoped(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        [$programme, $lesson, $quiz, $teacher] = $this->programme($student);
        $other = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($other)->get('/teacher/programmes/'.$programme->id.'/edit')->assertNotFound();
        $this->get('/teacher/programmes/'.$programme->id.'/results')->assertNotFound();
        $this->actingAs($student)->post('/learn/activities/'.$lesson->id.'/complete');
        $this->actingAs($teacher)->get('/teacher/programmes/'.$programme->id.'/edit')->assertForbidden();
        $this->get('/teacher/programmes/'.$programme->id.'/results')->assertOk()->assertSee($student->studentReference())->assertDontSee($student->name);
        $this->get('/teacher/programmes/'.$programme->id.'/export')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_unpublished_content_and_nested_answer_payloads_are_rejected(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        [$programme, $lesson, $quiz] = $this->programme($student);
        $this->actingAs($student)->post('/learn/activities/'.$lesson->id.'/complete');
        $this->postJson('/learn/activities/'.$quiz->id.'/save', ['answers' => [['nested']]])->assertUnprocessable();
        $other = Programme::create(['teacher_id' => $programme->teacher_id, 'title' => 'Not published', 'published' => false]);
        $activity = $other->activities()->create(['title' => 'Draft lesson', 'type' => 'lesson', 'position' => 1]);
        $other->students()->attach($student);
        $this->get('/learn/activities/'.$activity->id)->assertNotFound();
    }

    public function test_activity_edits_are_rejected_after_learning_starts(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        [, $lesson] = $this->programme($student);
        $this->actingAs($student)->post('/learn/activities/'.$lesson->id.'/complete');
        $this->expectException(ValidationException::class);
        $lesson->update(['content' => 'Changed after completion']);
    }

    public function test_assessment_view_uses_questions_refreshed_when_attempt_starts(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        [, $lesson, $quiz] = $this->programme($student);
        $lesson->delete();
        $newQuestions = [['prompt' => 'New approved question', 'options' => ['Yes', 'No'], 'correct' => 0]];
        Activity::findOrFail($quiz->id)->update(['questions' => $newQuestions]);
        $request = Request::create('/learn/activities/'.$quiz->id);
        $request->setUserResolver(fn () => $student);
        $view = (new LearningController)->show($request, $quiz, new Learning);
        $this->assertSame('New approved question', $view->getData()['questions'][0]['prompt']);
    }

    public function test_stale_lesson_cannot_complete_an_activity_changed_to_an_assessment(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        [, $lesson] = $this->programme($student);
        Activity::findOrFail($lesson->id)->update(['type' => 'checkpoint', 'questions' => [
            ['prompt' => 'New assessment', 'options' => ['A', 'B'], 'correct' => 0],
        ]]);
        $this->expectException(HttpException::class);
        (new Learning)->complete($student, $lesson);
    }
}
