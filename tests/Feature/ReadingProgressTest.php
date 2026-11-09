<?php

namespace Tests\Feature;

use App\Models\Completion;
use App\Models\Programme;
use App\Models\ReadingProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReadingProgressTest extends TestCase
{
    use RefreshDatabase;

    private function learningPath(): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id]);
        $programme = Programme::create(['teacher_id' => $teacher->id, 'title' => 'One sample module', 'published' => true]);
        $lesson = $programme->activities()->create(['title' => 'Part one', 'type' => 'lesson', 'position' => 1, 'content' => 'First paragraph.\n\nSecond paragraph.']);
        $next = $programme->activities()->create(['title' => 'Part two', 'type' => 'lesson', 'position' => 2, 'content' => 'Continue after the first part.']);
        $programme->students()->attach($student);

        return [$student, $lesson, $next, $programme, $teacher];
    }

    public function test_reader_supplies_a_version_and_saves_progress_without_completing(): void
    {
        [$student, $lesson, $next] = $this->learningPath();
        $this->actingAs($student)->get('/learn/activities/'.$lesson->id)->assertOk()->assertViewHas('version');
        $url = '/learn/activities/'.$lesson->id.'/reading';
        $this->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 87])->assertOk()->assertJsonPath('progress', 87);
        $this->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 20])->assertOk()->assertJsonPath('furthest_position', 87);
        $this->assertSame(1, ReadingProgress::count());
        $this->assertEquals(20, ReadingProgress::first()->position);
        $this->assertSame(0, Completion::count());
        $this->get('/learn/activities/'.$next->id)->assertForbidden();
        $this->get('/learn/activities/'.$lesson->id)->assertViewHas('reading', fn ($reading) => (int) $reading->furthest_position === 87);
    }

    public function test_reading_rejects_stale_or_invalid_payloads_and_foreign_or_locked_access(): void
    {
        [$student, $lesson, $next, , $teacher] = $this->learningPath();
        $url = '/learn/activities/'.$lesson->id.'/reading';
        $this->actingAs($student)->postJson($url, ['version' => str_repeat('0', 64), 'position' => 50])->assertStatus(409);
        $this->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 101])->assertUnprocessable();
        $this->postJson('/learn/activities/'.$next->id.'/reading', ['version' => $next->contentVersion(), 'position' => 100])->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'student']))->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 1])->assertNotFound();
        $this->actingAs($teacher)->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 1])->assertForbidden();
        $this->assertSame(0, ReadingProgress::count());
    }

    public function test_backtracking_before_a_debounced_save_retains_reported_furthest_coverage(): void
    {
        [$student, $lesson] = $this->learningPath();
        $url = '/learn/activities/'.$lesson->id.'/reading';
        $this->actingAs($student)->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 20, 'furthest_position' => 100])->assertOk()->assertJsonPath('position', 20)->assertJsonPath('progress', 100);
        $this->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 10, 'furthest_position' => 10])->assertOk()->assertJsonPath('progress', 100);
        $this->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 10, 'furthest_position' => 101])->assertUnprocessable();
        $this->assertSame(0, Completion::count());
    }

    public function test_pdf_page_coverage_is_distinct_and_private_documents_are_eligible_only(): void
    {
        Storage::fake('local');
        [$student, $lesson, $next] = $this->learningPath();
        Storage::disk('local')->put('modules/sample.pdf', "%PDF-1.4\n% synthetic access-test fixture");
        $lesson->update(['document_path' => 'modules/sample.pdf']);
        $url = '/learn/activities/'.$lesson->id.'/reading';
        $this->actingAs($student)->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 100, 'current_page' => 3, 'page_count' => 4, 'viewed_pages' => [1, 3, 3]])->assertOk()->assertJsonPath('progress', 50);
        $this->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 25, 'current_page' => 1, 'page_count' => 4, 'viewed_pages' => [1]])->assertOk()->assertJsonCount(2, 'viewed_pages')->assertJsonPath('progress', 50);
        $this->postJson($url, ['version' => $lesson->contentVersion(), 'position' => 0, 'current_page' => 5, 'page_count' => 4, 'viewed_pages' => [5]])->assertUnprocessable();
        $this->get('/learn/activities/'.$lesson->id.'/document')->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs(User::factory()->create(['role' => 'student']))->get('/learn/activities/'.$lesson->id.'/document')->assertNotFound();
        $this->actingAs($student)->get('/learn/activities/'.$next->id.'/document')->assertForbidden();
    }

    public function test_reading_starts_the_existing_content_freeze(): void
    {
        [$student, $lesson, , $programme] = $this->learningPath();
        $this->actingAs($student)->postJson('/learn/activities/'.$lesson->id.'/reading', ['version' => $lesson->contentVersion(), 'position' => 10])->assertOk();
        $this->assertTrue($programme->hasStarted());
        $this->expectException(ValidationException::class);
        $lesson->update(['content' => 'Cannot replace viewed material silently.']);
    }

    public function test_cycle_filter_does_not_bypass_teacher_release_or_missing_stages(): void
    {
        [$student, $lesson, $next] = $this->learningPath();
        $lesson->update(['cycle' => 1]);
        $next->update(['cycle' => 2]);
        $this->actingAs($student)->get('/learn?cycle=2')->assertOk()->assertViewHas('cycle', 2)
            ->assertViewHas('states', fn ($states) => $states[$next->id]['locked'] === true);
        $this->get('/learn?cycle=99')->assertSessionHasErrors('cycle');
        $this->post('/learn/activities/'.$lesson->id.'/complete')->assertRedirect();
        $this->get('/learn?cycle=2')->assertViewHas('states', fn ($states) => $states[$next->id]['locked'] === true && $states[$lesson->id]['complete'] === true);
    }

    public function test_google_form_link_cannot_use_native_attempt_or_completion_endpoints(): void
    {
        [$student, $lesson, $next] = $this->learningPath();
        $lesson->update(['type' => 'pre_test', 'assessment_source' => 'google_forms', 'google_form_url' => 'https://docs.google.com/forms/d/e/synthetic/viewform']);
        $this->actingAs($student)->get('/learn/activities/'.$lesson->id)->assertOk()->assertViewHas('attempt', null);
        $this->postJson('/learn/activities/'.$lesson->id.'/save', ['answers' => []])->assertUnprocessable();
        $this->post('/learn/activities/'.$lesson->id.'/submit', ['answers' => []])->assertUnprocessable();
        $this->post('/learn/activities/'.$lesson->id.'/complete')->assertUnprocessable();
        $this->get('/learn/activities/'.$next->id)->assertForbidden();
        $this->assertSame(0, Completion::count());
    }
}
