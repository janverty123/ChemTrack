<?php

namespace Tests\Feature;

use App\Filament\Resources\Programmes\Pages\ViewProgramme;
use App\Models\Programme;
use App\Models\ProgrammeInvitation;
use App\Models\User;
use App\Notifications\SignupCode;
use App\Services\ProgrammeInvitations;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProgrammeInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function programme(?User $teacher = null, bool $published = true): Programme
    {
        $teacher ??= User::factory()->create(['role' => 'teacher']);

        return Programme::create(['teacher_id' => $teacher->id, 'title' => 'Synthetic chemistry module', 'published' => $published]);
    }

    private function code(Programme $programme): string
    {
        return app(ProgrammeInvitations::class)->forProgramme($programme->teacher, $programme)->code;
    }

    public function test_teacher_gets_one_reusable_code_per_owned_programme(): void
    {
        $programme = $this->programme();
        $code = $this->code($programme);
        $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{8}$/', $code);
        $this->assertSame($code, $this->code($programme));
        $this->assertSame(1, ProgrammeInvitation::count());
        $other = User::factory()->create(['role' => 'teacher']);
        $this->expectException(HttpException::class);
        app(ProgrammeInvitations::class)->forProgramme($other, $programme);
    }

    public function test_self_registered_student_joins_with_code_and_is_linked_to_that_teacher(): void
    {
        $programme = $this->programme();
        $code = $this->code($programme);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => null]);
        $this->actingAs($student)->get('/join?code='.strtolower(substr($code, 0, 4)).' '.strtolower(substr($code, 4)))
            ->assertOk()->assertSee('Join Synthetic chemistry module?')->assertHeader('Cache-Control', 'no-store, private');
        $this->post('/join', ['code' => $code])->assertRedirect('/learn')->assertSessionHas('status', 'You joined Synthetic chemistry module.');
        $this->assertTrue($programme->students()->whereKey($student->id)->exists());
        $this->assertSame($programme->teacher_id, $student->fresh()->teacher_id);
        $this->get('/learn')->assertOk()->assertSee('Synthetic chemistry module')->assertSee('Join another programme');
        $this->post('/join', ['code' => $code])->assertRedirect('/learn')->assertSessionHas('status', 'You are already in Synthetic chemistry module.');
        $this->assertSame(1, $programme->students()->count());
        $second = $this->programme($programme->teacher);
        $this->post('/join', ['code' => $this->code($second)])->assertRedirect('/learn');
        $this->assertSame(2, $student->fresh()->programmes()->count());
    }

    public function test_invitation_link_is_kept_through_signup_and_sign_in(): void
    {
        Notification::fake();
        $programme = $this->programme();
        $code = $this->code($programme);
        $this->get('/join?code='.$code)->assertRedirect('/register');
        $this->get('/register')->assertOk()->assertSee('Your teacher\'s invitation is saved.', false);
        $this->post('/register', ['email' => 'joiner@example.test'])->assertRedirect('/register/verify');
        $signupCode = Notification::sent(new AnonymousNotifiable, SignupCode::class)->last()->code;
        $this->post('/register/verify', ['code' => $signupCode])->assertRedirect('/register/profile');
        $this->post('/register/profile', ['full_name' => 'Synthetic Joiner', 'lrn' => '123456789012', 'category' => 'B', 'password' => 'synthetic-password', 'password_confirmation' => 'synthetic-password'])->assertRedirect('/join');
        $this->get('/join')->assertOk()->assertSee('Join Synthetic chemistry module?');
        $this->post('/join', ['code' => $code])->assertRedirect('/learn');
        $this->assertSame('ST1-B', $programme->students()->firstOrFail()->student_code);
        $this->post('/logout');

        $existing = User::factory()->create(['role' => 'student', 'teacher_id' => null]);
        $this->get('/join?code='.$code)->assertRedirect('/register');
        $this->post('/login', ['identifier' => $existing->email, 'password' => 'password'])->assertRedirect('/join');
        $this->get('/join')->assertOk()->assertSee('Join Synthetic chemistry module?');
    }

    public function test_closed_unpublished_replaced_or_unknown_codes_do_not_enrol(): void
    {
        $programme = $this->programme();
        $teacher = $programme->teacher;
        $invitations = app(ProgrammeInvitations::class);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => null]);
        $this->actingAs($student);

        $code = $this->code($programme);
        $invitations->setEnabled($teacher, $programme, false);
        $this->post('/join', ['code' => $code])->assertSessionHasErrors('code');
        $this->get('/join?code='.$code)->assertOk()->assertSee('not valid or is no longer open');
        $invitations->setEnabled($teacher, $programme, true);

        $replacement = $invitations->regenerate($teacher, $programme)->code;
        $this->assertNotSame($code, $replacement);
        $this->post('/join', ['code' => $code])->assertSessionHasErrors('code');
        $this->post('/join', ['code' => 'UNKNOWN2'])->assertSessionHasErrors('code');

        $draft = $this->programme($teacher, false);
        $this->post('/join', ['code' => $this->code($draft)])->assertSessionHasErrors('code');

        $this->assertSame(0, $student->programmes()->count());
        $this->assertNull($student->fresh()->teacher_id);
        $this->post('/join', ['code' => $replacement])->assertRedirect('/learn');
    }

    public function test_student_of_another_teacher_and_teachers_cannot_join(): void
    {
        $programme = $this->programme();
        $code = $this->code($programme);
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'teacher_id' => $otherTeacher->id]);
        $this->actingAs($student)->post('/join', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertSame(0, $programme->students()->count());
        $this->assertSame($otherTeacher->id, $student->fresh()->teacher_id);

        $this->actingAs($programme->teacher)->post('/join', ['code' => $code])->assertForbidden();
        $this->get('/join?code='.$code)->assertRedirect('/teacher');
    }

    public function test_teacher_shares_resets_and_closes_code_from_programme_page(): void
    {
        $programme = $this->programme();
        $this->actingAs($programme->teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $page = Livewire::test(ViewProgramme::class, ['record' => $programme->id])->mountAction('inviteStudents');
        $code = ProgrammeInvitation::firstOrFail()->code;
        $page->assertMountedActionModalSee($code)->assertMountedActionModalSee(url('/join').'?code='.$code);

        Livewire::test(ViewProgramme::class, ['record' => $programme->id])->callAction(['inviteStudents', 'newInvitationCode']);
        $this->assertNotSame($code, ProgrammeInvitation::firstOrFail()->code);

        Livewire::test(ViewProgramme::class, ['record' => $programme->id])->callAction(['inviteStudents', 'toggleInvitation']);
        $this->assertFalse(ProgrammeInvitation::firstOrFail()->enabled);

        $other = User::factory()->create(['role' => 'teacher']);
        $this->expectException(HttpException::class);
        app(ProgrammeInvitations::class)->setEnabled($other, $programme, true);
    }
}
