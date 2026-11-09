<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Programme;
use App\Models\StudentInvitation;
use App\Models\User;
use App\Services\StudentAccounts;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CodedRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function programme(User $teacher): Programme
    {
        return Programme::create(['teacher_id' => $teacher->id, 'title' => 'Synthetic programme']);
    }

    public function test_internal_legacy_registration_allocates_independent_global_sequences_and_public_signup_starts_with_email(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $programme = $this->programme($teacher);
        $accounts = app(StudentAccounts::class);
        foreach (['G' => 'ST1-G', 'B' => 'ST1-B'] as $category => $code) {
            $token = $accounts->invite($teacher, $programme->id);
            $accounts->register($token, $category, 'synthetic-password');
            $student = User::where('student_code', $code)->firstOrFail();
            $this->assertSame($code, $student->name);
            $this->assertNull($student->email);
            $this->assertSame('student', $student->role);
            $this->assertSame($teacher->id, $student->teacher_id);
            $this->assertTrue($programme->students()->whereKey($student->id)->exists());
        }
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        $student = $accounts->create($otherTeacher, 'G', 'synthetic-password');
        $this->assertSame('ST2-G', $student->studentReference());
        $this->get('/register')->assertOk()->assertDontSee('name="password"', false)->assertSee('name="email"', false);
        $this->post('/register', ['invitation' => $accounts->invite($teacher, $programme->id), 'category' => 'G', 'password' => 'synthetic-password', 'password_confirmation' => 'synthetic-password'])->assertSessionHasErrors('email');
        $this->assertSame(3, User::where('role', 'student')->count());
    }

    public function test_consumed_expired_or_unknown_invitations_cannot_create_accounts(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $accounts = app(StudentAccounts::class);
        $token = $accounts->invite($teacher, $this->programme($teacher)->id);
        $accounts->register($token, 'B', 'synthetic-password');
        $before = User::count();
        foreach ([$token, 'unknown-token'] as $invalid) {
            $this->post('/register', ['invitation' => $invalid, 'email' => 'synthetic@example.test'])->assertSessionHasErrors('invitation');
        }
        $expired = $accounts->invite($teacher, $this->programme($teacher)->id);
        StudentInvitation::where('token_hash', hash('sha256', $expired))->update(['expires_at' => now()->subMinute()]);
        $this->post('/register', ['invitation' => $expired, 'email' => 'synthetic@example.test'])->assertSessionHasErrors('invitation');
        $this->assertSame($before, User::count());
        $this->assertSame(0, DB::table('student_code_counters')->where('category', 'G')->value('last_number'));
    }

    public function test_code_password_login_and_legacy_email_requests_remain_supported(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = app(StudentAccounts::class)->create($teacher, 'G', 'synthetic-password');
        $this->post('/login', ['identifier' => 'st1-g', 'password' => 'wrong'])->assertSessionHasErrors('identifier');
        $this->assertGuest();
        $this->post('/login', ['identifier' => 'st1-g', 'password' => 'synthetic-password'])->assertRedirect('/account/complete');
        $this->assertAuthenticatedAs($student);
        $this->post('/logout');
        $legacy = User::factory()->create(['role' => 'student']);
        $this->post('/login', ['email' => $legacy->email, 'password' => 'password'])->assertRedirect('/learn');
        $this->assertAuthenticatedAs($legacy);
        $this->assertSame('Learner '.$legacy->id, $legacy->studentReference());
    }

    public function test_invitation_issuance_is_teacher_owned_hashed_and_shown_once(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $other = User::factory()->create(['role' => 'teacher']);
        $programme = $this->programme($teacher);
        $this->actingAs($other)->post('/teacher/student-invitations', ['programme_id' => $programme->id])->assertForbidden();
        $this->actingAs($teacher)->post('/teacher/student-invitations', ['programme_id' => $programme->id])->assertRedirect('/teacher/student-invitations/created');
        $url = session('student_invitation_url');
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertDatabaseHas('student_invitations', ['token_hash' => hash('sha256', $query['invitation']), 'teacher_id' => $teacher->id, 'programme_id' => $programme->id]);
        $this->assertStringNotContainsString($query['invitation'], StudentInvitation::firstOrFail()->toJson());
        $this->get('/teacher/student-invitations/created')->assertOk()->assertSee(e($url), false)->assertHeader('Cache-Control', 'no-store, private');
        $this->get('/teacher/student-invitations/created')->assertRedirect('/teacher/student-invitations');
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->post('/teacher/student-invitations', ['programme_id' => $programme->id])->assertForbidden();
    }

    public function test_teacher_creates_coded_student_and_resets_only_own_student_password(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $other = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(CreateUser::class)->fillForm(['category' => 'B', 'password' => 'synthetic-password'])->call('create')->assertHasNoFormErrors();
        $student = User::where('student_code', 'ST1-B')->firstOrFail();
        $this->assertSame($teacher->id, $student->teacher_id);
        Livewire::test(EditUser::class, ['record' => $student->id])->fillForm(['password' => 'replacement-password'])->call('save')->assertHasNoFormErrors();
        $this->assertTrue(Hash::check('replacement-password', $student->fresh()->password));
        $this->actingAs($other)->get('/teacher/users/'.$student->id.'/edit')->assertNotFound();
        $this->actingAs($teacher);
        Livewire::test(ListUsers::class)->assertSee('ST1-B')->assertDontSee($other->email);
    }

    public function test_assigning_legacy_code_preserves_existing_identity_and_rejects_foreign_ownership(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $other = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id]);
        $original = $student->only(['name', 'email', 'password']);
        $accounts = app(StudentAccounts::class);
        try {
            $accounts->assignCode($other, $student, 'G');
            $this->fail('Foreign teacher was accepted.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $accounts->assignCode($teacher, $student, 'G');
        $this->assertSame('ST1-G', $student->fresh()->studentReference());
        $this->assertSame($original, $student->fresh()->only(['name', 'email', 'password']));
        $this->expectException(ValidationException::class);
        $accounts->assignCode($teacher, $student->fresh(), 'B');
    }

    public function test_invalid_registration_does_not_consume_invitation_and_programme_ownership_is_rechecked(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $other = User::factory()->create(['role' => 'teacher']);
        $programme = $this->programme($teacher);
        $token = app(StudentAccounts::class)->invite($teacher, $programme->id);
        $this->post('/register', ['invitation' => $token, 'category' => 'teacher', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('email');
        $this->assertNull(StudentInvitation::firstOrFail()->used_at);
        $programme->update(['teacher_id' => $other->id]);
        $this->post('/register', ['invitation' => $token, 'email' => 'synthetic@example.test'])->assertSessionHasErrors('invitation');
        $this->assertSame(0, User::where('role', 'student')->count());
        $this->assertNull(StudentInvitation::firstOrFail()->used_at);
    }

    public function test_legacy_roster_hides_identity_and_teacher_can_assign_category_in_edit_form(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $teacher->id]);
        $original = $student->only(['name', 'email', 'password']);
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(ListUsers::class)->assertSee('Learner '.$student->id)->assertDontSee($student->name)->assertDontSee($student->email);
        Livewire::test(EditUser::class, ['record' => $student->id])->fillForm(['category' => 'G'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('ST1-G', $student->fresh()->studentReference());
        $this->assertSame($original, $student->fresh()->only(['name', 'email', 'password']));
    }

    public function test_failed_enrollment_rolls_back_account_counter_and_invitation_consumption(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $accounts = app(StudentAccounts::class);
        $token = $accounts->invite($teacher, $this->programme($teacher)->id);
        DB::unprepared("CREATE TRIGGER refuse_synthetic_enrollment BEFORE INSERT ON programme_user BEGIN SELECT RAISE(ABORT, 'synthetic enrollment failure'); END");
        try {
            $accounts->register($token, 'G', 'synthetic-password');
            $this->fail('Enrollment should have failed.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('synthetic enrollment failure', $exception->getMessage());
        } finally {
            DB::unprepared('DROP TRIGGER refuse_synthetic_enrollment');
        }
        $this->assertSame(0, User::where('role', 'student')->count());
        $this->assertSame(0, DB::table('student_code_counters')->where('category', 'G')->value('last_number'));
        $this->assertNull(StudentInvitation::firstOrFail()->used_at);
        $this->assertSame('ST1-G', $accounts->register($token, 'G', 'synthetic-password')->studentReference());
    }
}
