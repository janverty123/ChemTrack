<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AccountSecurityNotice;
use App\Notifications\ConfirmAccountEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_avatar_uses_only_the_signed_in_owners_private_photo(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'avatar_path' => 'avatars/private-photo.jpg']);
        $other = User::factory()->create(['role' => 'teacher', 'avatar_path' => 'avatars/other-private-photo.jpg']);
        $this->actingAs($teacher);
        $this->assertSame(route('settings.avatar'), $teacher->getFilamentAvatarUrl());
        $this->assertNull($other->getFilamentAvatarUrl());
    }

    public function test_settings_require_login_and_only_show_the_owners_private_identity(): void
    {
        $this->get('/settings')->assertRedirect('/login');
        $student = User::factory()->create(['role' => 'student', 'name' => 'ST1-G', 'student_code' => 'ST1-G']);
        $student->forceFill(['private_identity' => ['full_name' => 'Synthetic Owner', 'lrn' => '123456789012']])->save();
        $other = User::factory()->create(['name' => 'SYNTHETIC FOREIGN']);
        $this->actingAs($student)->get('/settings?user='.$other->id)->assertOk()->assertSee('Synthetic Owner')->assertDontSee('SYNTHETIC FOREIGN')->assertDontSee('123456789012')->assertSee('9012');
    }

    public function test_student_profile_edits_keep_code_lrn_role_and_teacher_immutable(): void
    {
        $student = User::factory()->create(['role' => 'student', 'name' => 'ST1-G', 'student_code' => 'ST1-G']);
        $student->forceFill(['private_identity' => ['full_name' => 'Before', 'lrn' => '123456789012']])->save();
        $this->actingAs($student)->patch('/settings/profile', ['full_name' => 'After', 'phone' => '+639001112222', 'current_password' => 'password', 'student_code' => 'ST2-B', 'role' => 'teacher', 'lrn' => '999999999999', 'teacher_id' => 999])->assertRedirect('/settings');
        $student->refresh();
        $this->assertSame('After', $student->private_identity['full_name']);
        $this->assertSame('123456789012', $student->private_identity['lrn']);
        $this->assertSame('ST1-G', $student->name);
        $this->assertSame('ST1-G', $student->student_code);
        $this->assertSame('student', $student->role);
        $this->assertNull($student->teacher_id);
    }

    public function test_preferences_validate_and_persist_without_modifying_identity(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/settings/preferences', ['theme' => 'dark', 'text_size' => 'large', 'reduced_motion' => '1', 'default_cycle' => '2'])->assertRedirect('/settings');
        $this->assertSame(['theme' => 'dark', 'text_size' => 'large', 'reduced_motion' => true, 'default_cycle' => 2], $user->fresh()->preferences);
        $this->patch('/settings/preferences', ['theme' => 'unknown', 'text_size' => 'giant', 'default_cycle' => 9])->assertSessionHasErrors(['theme', 'text_size', 'default_cycle']);
    }

    public function test_sensitive_profile_and_password_changes_require_current_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/settings/profile', ['full_name' => 'Synthetic', 'current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->put('/settings/password', ['current_password' => 'wrong', 'password' => 'a-new-long-passphrase', 'password_confirmation' => 'a-new-long-passphrase'])->assertSessionHasErrors('current_password');
        $this->put('/settings/password', ['current_password' => 'password', 'password' => 'a-new-long-passphrase', 'password_confirmation' => 'a-new-long-passphrase'])->assertRedirect('/settings');
        $this->assertTrue(Hash::check('a-new-long-passphrase', $user->fresh()->password));
    }

    public function test_email_change_keeps_old_address_until_owner_confirms_and_is_single_use(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'before@example.test']);
        $this->actingAs($user)->post('/settings/email', ['email' => 'after@example.test', 'current_password' => 'password'])->assertRedirect('/settings');
        $this->assertSame('before@example.test', $user->fresh()->email);
        $url = null;
        Notification::assertSentOnDemand(ConfirmAccountEmail::class, function ($notification) use (&$url) {
            $url = $notification->url;

            return true;
        });
        $this->get($url)->assertOk();
        $this->assertSame('before@example.test', $user->fresh()->email);
        $parts = parse_url($url);
        $this->post($parts['path'].'?'.$parts['query'])->assertRedirect('/settings');
        $this->assertSame('after@example.test', $user->fresh()->email);
        Notification::assertSentOnDemand(AccountSecurityNotice::class);
        $this->post($parts['path'].'?'.$parts['query'])->assertForbidden();
    }

    public function test_email_confirmation_denies_foreign_and_expired_challenges(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->actingAs($user)->post('/settings/email', ['email' => 'after@example.test', 'current_password' => 'password']);
        $url = null;
        Notification::assertSentOnDemand(ConfirmAccountEmail::class, function ($notification) use (&$url) {
            $url = $notification->url;

            return true;
        });
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($user);
        $this->travel(31)->minutes();
        $this->get($url)->assertForbidden();
    }

    public function test_database_sessions_can_only_revoke_others_owned_by_the_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        config(['session.driver' => 'database']);
        foreach (['mine-other' => $user->id, 'foreign' => $other->id] as $id => $owner) {
            DB::table('sessions')->insert(['id' => $id, 'user_id' => $owner, 'payload' => '', 'last_activity' => time(), 'user_agent' => 'Synthetic Browser']);
        }
        $this->actingAs($user)->post('/settings/sessions/revoke', ['current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->assertDatabaseHas('sessions', ['id' => 'mine-other']);
        $this->post('/settings/sessions/revoke', ['current_password' => 'password'])->assertRedirect('/settings');
        $this->assertDatabaseMissing('sessions', ['id' => 'mine-other']);
        $this->assertDatabaseHas('sessions', ['id' => 'foreign']);
        $this->get('/settings')->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_avatar_delivery_is_private_and_rejects_executable_uploads(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user)->post('/settings/avatar', ['avatar' => UploadedFile::fake()->createWithContent('avatar.svg', '<svg><script>alert(1)</script></svg>')])->assertSessionHasErrors('avatar');
        $user->forceFill(['avatar_path' => 'avatars/synthetic.png'])->save();
        Storage::disk('local')->put('avatars/synthetic.png', 'synthetic');
        $this->get('/settings/avatar')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->actingAs(User::factory()->create())->get('/settings/avatar')->assertNotFound();
    }

    public function test_teacher_profile_can_change_public_display_and_optional_school_details(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher)->patch('/settings/profile', ['full_name' => 'Synthetic Teacher', 'display_name' => 'Teacher Example', 'school' => 'Synthetic School', 'designation' => 'Chemistry teacher', 'current_password' => 'password'])->assertRedirect('/settings');
        $teacher->refresh();
        $this->assertSame('Teacher Example', $teacher->name);
        $this->assertSame('Synthetic Teacher', $teacher->private_identity['full_name']);
        $this->assertSame('Synthetic School', $teacher->school);
    }

    public function test_new_email_request_invalidates_the_old_link_without_revealing_existing_accounts(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.test']);
        $response = $this->actingAs($user)->post('/settings/email', ['email' => 'taken@example.test', 'current_password' => 'password'])->assertRedirect('/settings');
        $message = session('status');
        Notification::assertNothingSent();
        $this->post('/settings/email', ['email' => 'first@example.test', 'current_password' => 'password'])->assertSessionHas('status', $message);
        $url = null;
        Notification::assertSentOnDemand(ConfirmAccountEmail::class, function ($notification) use (&$url) {
            $url = $notification->url;

            return true;
        });
        $this->post('/settings/email', ['email' => 'second@example.test', 'current_password' => 'password']);
        $this->get($url)->assertForbidden();
    }

    public function test_teacher_security_page_denies_students_and_exposes_native_setup_to_teacher(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'student']))->get('/teacher/account-security')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'teacher']))->get('/teacher/account-security')->assertOk()->assertSee('Authenticator app')->assertSee('Set up');
    }

    public function test_valid_avatar_is_reencoded_and_deleted_privately(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD is not enabled in this PHP process.');
        }
        Storage::fake('local');
        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('Synthetic Private Name.png', 48, 32);
        $this->actingAs($user)->post('/settings/avatar', ['avatar' => $image])->assertRedirect('/settings');
        $path = $user->fresh()->avatar_path;
        $this->assertStringNotContainsString('Synthetic', $path);
        $bytes = Storage::disk('local')->get($path);
        $details = getimagesizefromstring($bytes);
        $this->assertSame([256, 256, IMAGETYPE_JPEG], array_slice($details, 0, 3));
        $this->delete('/settings/avatar')->assertRedirect('/settings');
        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_sensitive_profile_changes_record_an_audit_event_without_raw_identity(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/settings/profile', ['full_name' => 'SYNTHETIC PRIVATE AUDIT', 'current_password' => 'password'])->assertRedirect('/settings');
        $event = DB::table('account_security_events')->where('user_id', $user->id)->first();
        $this->assertSame('profile.updated', $event->action);
        $this->assertStringNotContainsString('SYNTHETIC PRIVATE AUDIT', json_encode($event));
    }

    public function test_password_changes_reject_over_login_limit_and_maximum_length_can_sign_in(): void
    {
        $user = User::factory()->create();
        $tooLong = str_repeat('x', 201);
        $this->actingAs($user)->put('/settings/password', ['current_password' => 'password', 'password' => $tooLong, 'password_confirmation' => $tooLong])->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $password = str_repeat('x', 200);
        $this->put('/settings/password', ['current_password' => 'password', 'password' => $password, 'password_confirmation' => $password])->assertRedirect('/settings');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => $password])->assertRedirect('/learn');
        $this->assertAuthenticatedAs($user);
    }
}
