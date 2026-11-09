<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\StudentInvitation;
use App\Models\User;
use App\Notifications\SignupCode;
use App\Services\StudentAccounts;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerifiedRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function invitation(): string
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $programme = Programme::create(['teacher_id' => $teacher->id, 'title' => 'Synthetic programme']);

        return app(StudentAccounts::class)->invite($teacher, $programme->id);
    }

    private function begin(?string $token = null, string $email = 'synthetic@example.test'): string
    {
        Notification::fake();
        $this->post('/register', ['invitation' => $token ?? $this->invitation(), 'email' => $email])->assertRedirect('/register/verify');

        return Notification::sent(new AnonymousNotifiable, SignupCode::class)->last()->code;
    }

    private function profile(array $changes = []): array
    {
        return array_replace(['full_name' => 'Synthetic Student', 'lrn' => '123456789012', 'category' => 'G', 'password' => 'synthetic-password', 'password_confirmation' => 'synthetic-password'], $changes);
    }

    public function test_email_is_verified_before_atomic_private_profile_and_enrollment(): void
    {
        $code = $this->begin();
        $this->assertSame(0, User::where('role', 'student')->count());
        $this->assertNull(StudentInvitation::first()->used_at);
        $this->get('/register/verify')->assertOk()->assertDontSee($code)->assertSee('s•••@example.test');
        $stored = DB::table('signup_challenges')->first();
        $this->assertNotSame($code, $stored->code_hash);
        $this->post('/register/profile', $this->profile())->assertRedirect('/register/verify');
        $this->post('/register/verify', ['code' => $code])->assertRedirect('/register/profile');
        $this->get('/register/profile')->assertOk()->assertSee('id="signup-password"', false)->assertSee('data-password-toggle', false)->assertSee('aria-current="step"', false);
        $this->post('/register/profile', $this->profile(['role' => 'teacher', 'student_code' => 'ST999-B']))->assertRedirect('/learn');
        $student = User::where('role', 'student')->firstOrFail();
        $this->assertSame('ST1-G', $student->name);
        $this->assertSame('ST1-G', $student->student_code);
        $this->assertSame('Synthetic Student', $student->private_identity['full_name']);
        $this->assertSame('123456789012', $student->private_identity['lrn']);
        $this->assertNotNull($student->email_verified_at);
        $this->assertNotNull($student->account_completed_at);
        $this->assertStringNotContainsString('123456789012', DB::table('users')->where('id', $student->id)->value('private_identity'));
        $this->assertStringNotContainsString('Synthetic Student', $student->toJson());
        $this->assertDatabaseHas('programme_user', ['user_id' => $student->id]);
        $this->assertNotNull(StudentInvitation::first()->used_at);
        $this->post('/logout');
        $this->post('/register/profile', $this->profile())->assertRedirect('/register/verify');
        $this->assertSame(1, User::where('role', 'student')->count());
    }

    public function test_wrong_codes_lock_after_five_attempts_and_expiry_is_enforced(): void
    {
        $code = $this->begin();
        $wrong = $code === '000000' ? '111111' : '000000';
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register/verify', ['code' => $wrong])->assertSessionHasErrors('code');
        }
        $this->post('/register/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertSame(5, DB::table('signup_challenges')->first()->failed_attempts);
        $this->travel(11)->minutes();
        $this->post('/register/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertNull(StudentInvitation::first()->used_at);
    }

    public function test_resend_waits_sixty_seconds_and_invalidates_old_code(): void
    {
        $old = $this->begin();
        $this->post('/register/resend')->assertSessionHasErrors('code');
        Notification::assertSentOnDemandTimes(SignupCode::class, 1);
        $this->travel(61)->seconds();
        $this->post('/register/resend')->assertRedirect('/register/verify');
        $new = Notification::sent(new AnonymousNotifiable, SignupCode::class)->last()->code;
        $this->post('/register/verify', ['code' => $old])->assertSessionHasErrors('code');
        $this->post('/register/verify', ['code' => $new])->assertRedirect('/register/profile');
    }

    public function test_foreign_session_cannot_use_verified_challenge(): void
    {
        $code = $this->begin();
        $this->post('/register/verify', ['code' => $code])->assertRedirect('/register/profile');
        $challenge = session('signup_challenge');
        session()->flush();
        $this->withSession(['signup_challenge' => $challenge, 'signup_binding' => 'foreign-browser-binding'])->post('/register/profile', $this->profile())->assertRedirect('/register/verify');
        $this->assertSame(0, User::where('role', 'student')->count());
    }

    public function test_invitation_and_identity_are_rechecked_at_final_creation(): void
    {
        $code = $this->begin();
        $this->post('/register/verify', ['code' => $code]);
        StudentInvitation::first()->update(['used_at' => now()]);
        $this->post('/register/profile', $this->profile())->assertSessionHasErrors('invitation');
        $this->assertSame(0, User::where('role', 'student')->count());
        $this->assertSame(0, DB::table('student_code_counters')->where('category', 'G')->value('last_number'));
    }

    public function test_duplicate_lrn_is_rejected_without_allocating_or_consuming(): void
    {
        $code = $this->begin();
        $this->post('/register/verify', ['code' => $code]);
        $this->post('/register/profile', $this->profile())->assertRedirect('/learn');
        $this->post('/logout');
        $this->travel(61)->seconds();
        $code = $this->begin(null, 'second@example.test');
        $this->post('/register/verify', ['code' => $code]);
        $this->post('/register/profile', $this->profile())->assertSessionHasErrors('lrn');
        $this->assertNull(StudentInvitation::latest('id')->first()->used_at);
        $this->assertSame(1, User::where('role', 'student')->count());
    }

    public function test_new_teacher_created_student_must_complete_account_and_keeps_its_code(): void
    {
        Notification::fake();
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = app(StudentAccounts::class)->create($teacher, 'B', 'synthetic-password');
        $this->actingAs($student)->get('/learn')->assertRedirect('/account/complete');
        $this->post('/account/complete', ['email' => 'complete@example.test'])->assertRedirect('/account/complete/verify');
        $code = Notification::sent(new AnonymousNotifiable, SignupCode::class)->last()->code;
        $this->post('/account/complete/verify', ['code' => $code])->assertRedirect('/account/complete/profile');
        $this->post('/account/complete/profile', $this->profile(['category' => 'G']))->assertRedirect('/learn');
        $this->assertSame('ST1-B', $student->fresh()->student_code);
        $this->assertSame('B', $student->fresh()->category);
        $this->assertTrue(Hash::check('synthetic-password', $student->fresh()->password));
    }

    public function test_forgot_password_response_is_generic_and_reset_revokes_sessions_and_token(): void
    {
        Notification::fake();
        $student = User::factory()->create(['role' => 'student']);
        $oldRemember = $student->remember_token;
        DB::table('sessions')->insert(['id' => 'synthetic-session', 'user_id' => $student->id, 'payload' => '', 'last_activity' => time()]);
        $this->post('/forgot-password', ['email' => $student->email])->assertRedirect()->assertSessionHas('status', 'If an account can receive email, a password reset link has been sent.');
        $token = Notification::sent($student, ResetPassword::class)->first()->token;
        $this->post('/forgot-password', ['email' => 'missing@example.test'])->assertSessionHas('status', 'If an account can receive email, a password reset link has been sent.');
        $this->get('/reset-password/'.$token.'?email='.urlencode($student->email))->assertOk();
        $this->post('/reset-password', ['token' => $token, 'email' => $student->email, 'password' => 'new-synthetic-password', 'password_confirmation' => 'new-synthetic-password'])->assertRedirect('/login');
        $this->assertTrue(Hash::check('new-synthetic-password', $student->fresh()->password));
        $this->assertNotSame($oldRemember, $student->fresh()->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'synthetic-session']);
        $this->post('/reset-password', ['token' => $token, 'email' => $student->email, 'password' => 'other-synthetic-password', 'password_confirmation' => 'other-synthetic-password'])->assertSessionHasErrors('email');
    }

    public function test_teacher_with_mfa_cannot_bypass_challenge_using_regular_login(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'app_authentication_secret' => 'synthetic-secret']);
        $this->post('/login', ['identifier' => $teacher->email, 'password' => 'password'])->assertRedirect('/teacher/login');
        $this->assertGuest();
    }

    public function test_successful_code_cannot_be_replayed_and_verified_session_expires(): void
    {
        $code = $this->begin();
        $this->post('/register/verify', ['code' => $code])->assertRedirect('/register/profile');
        $this->post('/register/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->travel(11)->minutes();
        $this->post('/register/profile', $this->profile())->assertRedirect('/register/verify');
        $this->assertNull(StudentInvitation::first()->used_at);
    }

    public function test_failed_final_enrollment_rolls_back_challenge_invitation_identity_and_counter(): void
    {
        $code = $this->begin();
        $this->post('/register/verify', ['code' => $code]);
        DB::unprepared("CREATE TRIGGER refuse_verified_enrollment BEFORE INSERT ON programme_user BEGIN SELECT RAISE(ABORT, 'synthetic enrollment failure'); END");
        $this->withoutExceptionHandling();
        try {
            $this->post('/register/profile', $this->profile());
            $this->fail('Enrollment should fail.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('synthetic enrollment failure', $exception->getMessage());
        } finally {
            DB::unprepared('DROP TRIGGER refuse_verified_enrollment');
        }
        $this->assertSame(0, User::where('role', 'student')->count());
        $this->assertNull(StudentInvitation::first()->used_at);
        $this->assertNull(DB::table('signup_challenges')->first()->consumed_at);
        $this->assertSame(0, DB::table('student_code_counters')->where('category', 'G')->value('last_number'));
        $this->post('/register/profile', $this->profile())->assertRedirect('/learn');
    }

    public function test_existing_legacy_account_access_is_preserved_during_completion_rollout(): void
    {
        $legacy = User::factory()->create(['role' => 'student']);
        $original = $legacy->only(['name', 'email', 'password', 'student_code']);
        $this->actingAs($legacy)->get('/learn')->assertOk();
        $this->get('/account/complete')->assertOk()->assertSee('Complete your account');
        $this->assertSame($original, $legacy->fresh()->only(['name', 'email', 'password', 'student_code']));
    }

    public function test_final_profile_validates_fields_without_using_unverified_identity(): void
    {
        $code = $this->begin();
        $this->post('/register/verify', ['code' => $code]);
        $this->post('/register/profile', $this->profile(['lrn' => 'wrong', 'full_name' => '', 'password_confirmation' => 'different']))->assertSessionHasErrors(['lrn', 'full_name', 'password']);
        $this->assertSame(0, User::where('role', 'student')->count());
        $this->assertNull(StudentInvitation::first()->used_at);
    }

    public function test_identity_uniqueness_race_returns_recoverable_error_and_rolls_back(): void
    {
        $code = $this->begin();
        $this->post('/register/verify', ['code' => $code]);
        DB::unprepared("CREATE TRIGGER synthetic_identity_race BEFORE UPDATE OF email ON users BEGIN SELECT RAISE(ABORT, 'UNIQUE constraint failed: users.email'); END");
        try {
            $this->post('/register/profile', $this->profile())->assertSessionHasErrors('email');
        } finally {
            DB::unprepared('DROP TRIGGER synthetic_identity_race');
        }
        $this->assertSame(0, User::where('role', 'student')->count());
        $this->assertNull(StudentInvitation::first()->used_at);
        $this->assertNull(DB::table('signup_challenges')->first()->consumed_at);
        $this->assertSame(0, DB::table('student_code_counters')->where('category', 'G')->value('last_number'));
    }

    public function test_unfinished_signup_resumes_permitted_step_and_change_email_invalidates_old_code(): void
    {
        $code = $this->begin();
        $this->get('/register')->assertRedirect('/register/verify');
        $this->post('/register/verify', ['code' => $code]);
        $this->get('/register')->assertRedirect('/register/profile');
        $this->get('/register?restart=1')->assertOk()->assertSee('name="email"', false);
        $oldChallenge = session('signup_challenge');
        $this->post('/register', ['invitation' => $this->invitation(), 'email' => 'changed@example.test'])->assertRedirect('/register/verify');
        $this->assertNotNull(DB::table('signup_challenges')->where('id', $oldChallenge)->value('consumed_at'));
        $this->post('/register/verify', ['code' => $code])->assertSessionHasErrors('code');
    }

    public function test_restarting_signup_cannot_bypass_shared_email_send_cooldown(): void
    {
        $token = $this->invitation();
        $code = $this->begin($token);
        $challenge = session('signup_challenge');
        $this->post('/register', ['invitation' => $token, 'email' => 'SYNTHETIC@example.test'])->assertSessionHasErrors('email');
        $this->assertSame($challenge, session('signup_challenge'));
        Notification::assertSentOnDemandTimes(SignupCode::class, 1);
        $this->post('/register/verify', ['code' => $code])->assertRedirect('/register/profile');
    }

    public function test_account_completion_start_and_resend_share_send_cooldown(): void
    {
        Notification::fake();
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->post('/account/complete', ['email' => 'complete@example.test'])->assertRedirect('/account/complete/verify');
        $challenge = session('signup_challenge');
        $this->post('/account/complete', ['email' => 'complete@example.test'])->assertSessionHasErrors('email');
        $this->assertSame($challenge, session('signup_challenge'));
        $this->post('/account/complete/resend')->assertSessionHasErrors('code');
        Notification::assertSentOnDemandTimes(SignupCode::class, 1);
        $this->travel(61)->seconds();
        $this->post('/account/complete/resend')->assertRedirect('/account/complete/verify');
        $this->post('/account/complete', ['email' => 'complete@example.test'])->assertSessionHasErrors('email');
        Notification::assertSentOnDemandTimes(SignupCode::class, 2);
    }
}
