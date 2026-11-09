<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_includes_unstarted_students_and_keeps_reading_separate_from_completion(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id, 'student_code' => 'ST91-G', 'name' => 'Never show this name']);
        $programme = Programme::create(['teacher_id' => $teacher->id, 'title' => 'Sample module', 'published' => true]);
        $lesson = $programme->activities()->create(['title' => '=unsafe formula', 'type' => 'lesson', 'cycle' => 1, 'content' => 'Example text']);
        $programme->students()->attach($student);
        $response = $this->actingAs($teacher)->get('/teacher/programmes/'.$programme->id.'/export')->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('ST91-G', $csv);
        $this->assertStringContainsString('not started', $csv);
        $this->assertStringNotContainsString($student->name, $csv);
        $this->assertStringContainsString("'=unsafe formula", $csv);
        $this->actingAs($student)->postJson('/learn/activities/'.$lesson->id.'/reading', ['version' => $lesson->contentVersion(), 'position' => 100])->assertOk();
        $this->actingAs($teacher)->get('/teacher/programmes/'.$programme->id.'/results')->assertOk()->assertSee('ST91-G')->assertDontSee($student->name)->assertSee('Reading estimate');
        $csv = $this->get('/teacher/programmes/'.$programme->id.'/export')->streamedContent();
        $this->assertStringContainsString('in progress', $csv);
        $this->assertStringNotContainsString('lesson acknowledgement', $csv);
    }
}
