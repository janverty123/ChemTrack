<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeploymentReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_limit_is_per_account_so_a_shared_school_network_is_not_locked_out(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->post('/login', ['identifier' => "ST{$i}-G", 'password' => 'wrong-password'])->assertStatus(302);
        }

        $student = User::factory()->create(['role' => 'student']);
        $this->post('/login', ['identifier' => $student->email, 'password' => 'password'])->assertRedirect('/learn');
    }

    public function test_route_rate_limits_do_not_share_one_counter(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['identifier' => 'someone@example.test', 'password' => 'wrong']);
        }
        $this->post('/login', ['identifier' => 'someone@example.test', 'password' => 'wrong'])->assertStatus(429);

        // Exhausting the login limit must not block password recovery from the same address.
        $this->post('/forgot-password', ['email' => 'someone@example.test'])->assertStatus(302);
    }

    public function test_responses_carry_baseline_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $this->get('/teacher/login')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_missing_pages_use_the_branded_error_page(): void
    {
        $this->get('/does-not-exist')->assertNotFound()->assertSee("We couldn't find that page")->assertSee('Go to ChemTrack home');
    }

    public function test_maintainer_can_create_a_teacher_account_from_the_console(): void
    {
        $this->artisan('chemtrack:create-teacher', ['email' => 'New.Teacher@Example.test', '--name' => 'Ms Reyes'])
            ->expectsQuestion('Password (at least 12 characters)', 'correct horse battery')
            ->expectsQuestion('Confirm password', 'correct horse battery')
            ->assertSuccessful();

        $teacher = User::where('email', 'new.teacher@example.test')->firstOrFail();
        $this->assertSame('teacher', $teacher->role);
        $this->assertTrue(Hash::check('correct horse battery', $teacher->password));
    }

    public function test_teacher_command_rejects_short_passwords_and_duplicate_emails(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        $this->artisan('chemtrack:create-teacher', ['email' => 'taken@example.test', '--name' => 'Duplicate'])
            ->expectsQuestion('Password (at least 12 characters)', 'correct horse battery')
            ->expectsQuestion('Confirm password', 'correct horse battery')
            ->assertFailed();

        $this->artisan('chemtrack:create-teacher', ['email' => 'short@example.test', '--name' => 'Short'])
            ->expectsQuestion('Password (at least 12 characters)', 'short')
            ->expectsQuestion('Confirm password', 'short')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'short@example.test']);
    }

    public function test_teacher_panel_login_sends_everyone_to_the_main_sign_in_page(): void
    {
        $this->get('/teacher')->assertRedirect('/teacher/login');
        $this->get('/teacher/login')->assertRedirect('/login');
    }

    public function test_teacher_with_two_factor_keeps_the_authenticator_step(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $teacher->forceFill(['app_authentication_secret' => 'JBSWY3DPEHPK3PXP'])->save();

        $this->post('/login', ['identifier' => $teacher->email, 'password' => 'password'])->assertRedirect('/teacher/login');
        $this->get('/teacher/login')->assertOk();
        $this->assertGuest();
    }

    public function test_signing_out_of_the_teacher_panel_returns_to_the_main_sign_in_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'teacher']));

        $this->post('/teacher/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_keep_me_signed_in_sets_a_remember_cookie(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->post('/login', ['identifier' => $student->email, 'password' => 'password', 'remember' => '1'])->assertRedirect('/learn');

        $this->assertNotEmpty(collect($response->headers->getCookies())->filter(fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_')));
    }

    public function test_each_cycle_shows_only_its_own_activities(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id]);
        $programme = Programme::forceCreate(['teacher_id' => $teacher->id, 'title' => 'Cycle separation', 'published' => true, 'cycle_workflow' => true]);
        $questions = [['prompt' => 'Sample?', 'options' => ['A', 'B'], 'correct' => 0]];
        $programme->activities()->create(['title' => 'First cycle pre-test sentinel', 'type' => 'pre_test', 'cycle' => 1, 'position' => 1, 'questions' => $questions]);
        $programme->activities()->create(['title' => 'Second cycle pre-test sentinel', 'type' => 'pre_test', 'cycle' => 2, 'position' => 2, 'questions' => $questions]);
        $programme->students()->attach($student->id);

        $this->actingAs($student)->get('/learn?cycle=1')->assertOk()
            ->assertSee('First cycle pre-test sentinel')->assertDontSee('Second cycle pre-test sentinel');
        $this->get('/learn?cycle=2')->assertOk()
            ->assertSee('Second cycle pre-test sentinel')->assertDontSee('First cycle pre-test sentinel');
    }
}
