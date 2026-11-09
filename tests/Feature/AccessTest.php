<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/learn')->assertRedirect('/login');
    }

    public function test_student_can_login_but_cannot_enter_teacher_panel(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->post('/login', ['email' => $student->email, 'password' => 'password', 'role' => 'teacher'])
            ->assertRedirect('/learn');
        $this->assertAuthenticatedAs($student);
        $this->get('/teacher')->assertForbidden();
        $this->assertSame('student', $student->fresh()->role);
    }

    public function test_wrong_password_does_not_authenticate(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->post('/login', ['email' => $student->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_teacher_can_access_panel_and_logout(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->post('/login', ['email' => $teacher->email, 'password' => 'password'])
            ->assertRedirect('/teacher');
        $this->get('/teacher')->assertOk();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'someone@example.test', 'password' => 'wrong']);
        }
        $this->post('/login', ['email' => 'someone@example.test', 'password' => 'wrong'])->assertStatus(429);
    }
}
