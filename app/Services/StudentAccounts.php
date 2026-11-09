<?php

namespace App\Services;

use App\Models\Programme;
use App\Models\SignupChallenge;
use App\Models\StudentInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentAccounts
{
    public function invite(User $teacher, int $programmeId): string
    {
        $this->ensureTeacher($teacher);

        return DB::transaction(function () use ($teacher, $programmeId) {
            $programme = Programme::whereKey($programmeId)->lockForUpdate()->firstOrFail();
            abort_unless($programme->teacher_id === $teacher->id, 403);
            $token = Str::random(64);
            StudentInvitation::create([
                'teacher_id' => $teacher->id, 'programme_id' => $programme->id,
                'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDays(7),
            ]);

            return $token;
        }, 5);
    }

    public function register(string $token, string $category, string $password): User
    {
        $this->validateAccount($category, $password);

        return DB::transaction(function () use ($token, $category, $password) {
            $invitation = StudentInvitation::where('token_hash', hash('sha256', $token))->lockForUpdate()->first();
            if (! $invitation || $invitation->used_at || $invitation->expires_at->isPast()) {
                throw ValidationException::withMessages(['invitation' => 'This invitation is unavailable or expired. Ask your teacher for a new invitation.']);
            }
            $teacher = User::whereKey($invitation->teacher_id)->where('role', 'teacher')->first();
            $programme = Programme::whereKey($invitation->programme_id)->lockForUpdate()->first();
            if (! $teacher || ! $programme || $programme->teacher_id !== $teacher->id) {
                throw ValidationException::withMessages(['invitation' => 'This invitation is unavailable. Ask your teacher for a new invitation.']);
            }
            $student = $this->newStudent($teacher, $category, $password);
            $programme->students()->attach($student);
            $invitation->update(['used_at' => now(), 'used_by' => $student->id]);

            return $student;
        }, 5);
    }

    public function create(User $teacher, string $category, string $password): User
    {
        $this->ensureTeacher($teacher);
        $this->validateAccount($category, $password);

        return DB::transaction(function () use ($teacher, $category, $password) {
            $student = $this->newStudent($teacher, $category, $password);
            $student->forceFill(['registration_required' => true])->save();

            return $student;
        }, 5);
    }

    public function availableInvitation(string $token): StudentInvitation
    {
        $invitation = StudentInvitation::where('token_hash', hash('sha256', $token))->first();
        $this->checkInvitation($invitation);

        return $invitation;
    }

    public function checkInvitation(?StudentInvitation $invitation): void
    {
        if (! $invitation || $invitation->used_at || $invitation->expires_at->isPast()
            || ! User::whereKey($invitation->teacher_id)->where('role', 'teacher')->exists()
            || ! Programme::whereKey($invitation->programme_id)->where('teacher_id', $invitation->teacher_id)->exists()) {
            throw ValidationException::withMessages(['invitation' => 'This invitation is unavailable or expired. Ask your teacher for a new invitation.']);
        }
    }

    public function finishVerified(SignupChallenge $challenge, array $data): User
    {
        return DB::transaction(function () use ($challenge, $data) {
            $challenge = SignupChallenge::whereKey($challenge->id)->lockForUpdate()->firstOrFail();
            if (! $challenge->verified_at || $challenge->consumed_at || $challenge->expires_at->isPast()) {
                throw ValidationException::withMessages(['code' => 'Verify your email again before continuing.']);
            }
            $hash = hash_hmac('sha256', $data['lrn'], config('app.key'));
            if (User::where('identity_hash', $hash)->when($challenge->user_id, fn ($query) => $query->where('id', '!=', $challenge->user_id))->exists()) {
                throw ValidationException::withMessages(['lrn' => 'These details could not be accepted. Ask your teacher for help.']);
            }
            if (User::where('email', $challenge->email)->when($challenge->user_id, fn ($query) => $query->where('id', '!=', $challenge->user_id))->exists()) {
                throw ValidationException::withMessages(['email' => 'This email cannot be used. Sign in or ask your teacher for help.']);
            }
            if ($challenge->user_id) {
                $student = User::whereKey($challenge->user_id)->where('role', 'student')->lockForUpdate()->firstOrFail();
                if ($student->account_completed_at) {
                    throw ValidationException::withMessages(['email' => 'This account is already complete.']);
                }
            } else {
                $invitation = StudentInvitation::whereKey($challenge->invitation_id)->lockForUpdate()->first();
                $this->checkInvitation($invitation);
                $programme = Programme::whereKey($invitation->programme_id)->lockForUpdate()->firstOrFail();
                $this->checkInvitation($invitation);
                $student = $this->newStudent(User::findOrFail($invitation->teacher_id), $data['category'], $data['password']);
                $programme->students()->attach($student);
                $invitation->update(['used_at' => now(), 'used_by' => $student->id]);
            }
            $student->forceFill([
                'email' => $challenge->email, 'email_verified_at' => $challenge->verified_at,
                'password' => $data['password'], 'private_identity' => array_intersect_key($data, array_flip(['full_name', 'lrn', 'phone', 'grade', 'section'])),
                'identity_hash' => $hash, 'account_completed_at' => now(), 'registration_required' => true,
            ])->save();
            $challenge->update(['consumed_at' => now()]);

            return $student;
        }, 5);
    }

    public function assignCode(User $teacher, User $student, string $category): User
    {
        $this->ensureTeacher($teacher);
        Validator::make(['category' => $category], ['category' => 'required|in:G,B'])->validate();

        return DB::transaction(function () use ($teacher, $student, $category) {
            $student = User::whereKey($student->id)->lockForUpdate()->firstOrFail();
            abort_unless($student->role === 'student' && $student->teacher_id === $teacher->id, 403);
            if ($student->student_code) {
                throw ValidationException::withMessages(['category' => 'This learner already has a permanent student code.']);
            }
            $student->update(['student_code' => $this->allocate($category), 'category' => $category]);

            return $student;
        }, 5);
    }

    private function ensureTeacher(User $teacher): void
    {
        abort_unless($teacher->role === 'teacher', 403);
    }

    private function validateAccount(string $category, string $password): void
    {
        Validator::make(compact('category', 'password'), ['category' => 'required|in:G,B', 'password' => 'required|string|min:12|max:200'])->validate();
    }

    private function newStudent(User $teacher, string $category, string $password): User
    {
        $code = $this->allocate($category);

        return User::create(['name' => $code, 'email' => null, 'student_code' => $code, 'category' => $category, 'password' => $password, 'role' => 'student', 'teacher_id' => $teacher->id]);
    }

    private function allocate(string $category): string
    {
        // Both category rows exist from migration, so concurrent first registrations lock a real row.
        $counter = DB::table('student_code_counters')->where('category', $category)->lockForUpdate()->first();
        $number = $counter->last_number + 1;
        DB::table('student_code_counters')->where('category', $category)->update(['last_number' => $number]);

        return 'ST'.$number.'-'.$category;
    }
}
