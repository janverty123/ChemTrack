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

    public function test_signed_in_student_opening_the_site_goes_straight_to_their_dashboard(): void
    {
        $this->actingAs(User::factory()->make(['role' => 'student']));

        $this->get('/')->assertRedirect('/learn');
    }

    public function test_signed_in_teacher_opening_the_site_goes_straight_to_the_teacher_dashboard(): void
    {
        $this->actingAs(User::factory()->make(['role' => 'teacher']));

        $this->get('/')->assertRedirect('/teacher');
    }
}
