<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_guest_landing_has_sign_in_and_labels_demonstration_content(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('href="/login"', false)
            ->assertSee('Interactive preview')
            ->assertSee('One module')
            ->assertDontSee('href="/teacher"', false)
            ->assertDontSee('href="/learn"', false);
    }

    public function test_signed_in_student_can_return_to_their_learning_dashboard(): void
    {
        $this->actingAs(User::factory()->make(['role' => 'student']));

        $this->get('/')->assertOk()
            ->assertSee('href="/learn"', false)
            ->assertSee('Open dashboard')
            ->assertDontSee('href="/teacher"', false);
    }

    public function test_signed_in_teacher_can_return_to_the_teacher_dashboard(): void
    {
        $this->actingAs(User::factory()->make(['role' => 'teacher']));

        $this->get('/')->assertOk()
            ->assertSee('href="/teacher"', false)
            ->assertSee('Open dashboard')
            ->assertDontSee('href="/learn"', false);
    }
}
