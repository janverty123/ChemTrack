<?php

namespace App\Services;

use App\Models\Programme;
use App\Models\ProgrammeInvitation;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgrammeInvitations
{
    /** Unambiguous characters only, so codes can be read aloud or copied from a board. */
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const LENGTH = 8;

    public function forProgramme(User $teacher, Programme $programme): ProgrammeInvitation
    {
        $this->ensureOwner($teacher, $programme);

        return $programme->invitation()->first() ?? $this->withFreshCode(fn (string $code) => ProgrammeInvitation::create(['programme_id' => $programme->id, 'code' => $code]), fn () => $programme->invitation()->first());
    }

    public function regenerate(User $teacher, Programme $programme): ProgrammeInvitation
    {
        $invitation = $this->forProgramme($teacher, $programme);

        return $this->withFreshCode(function (string $code) use ($invitation) {
            $invitation->update(['code' => $code, 'enabled' => true]);

            return $invitation;
        });
    }

    public function setEnabled(User $teacher, Programme $programme, bool $enabled): ProgrammeInvitation
    {
        $invitation = $this->forProgramme($teacher, $programme);
        $invitation->update(['enabled' => $enabled]);

        return $invitation;
    }

    public function normalize(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
    }

    /**
     * Find the programme a code currently admits, or fail with a message safe to show students.
     */
    public function available(string $code): ProgrammeInvitation
    {
        $invitation = ProgrammeInvitation::where('code', $this->normalize($code))->with('programme.teacher')->first();
        $this->check($invitation);

        return $invitation;
    }

    /**
     * Enrol a student through a programme code. Joining is idempotent; the first joined programme links the student to its teacher.
     *
     * @return array{programme: Programme, joined: bool}
     */
    public function join(User $student, string $code): array
    {
        abort_unless($student->role === 'student', 403);

        return DB::transaction(function () use ($student, $code) {
            $invitation = ProgrammeInvitation::where('code', $this->normalize($code))->lockForUpdate()->first();
            $this->check($invitation);
            $programme = Programme::whereKey($invitation->programme_id)->firstOrFail();
            $student = User::whereKey($student->id)->lockForUpdate()->firstOrFail();
            if ($student->teacher_id && $student->teacher_id !== $programme->teacher_id) {
                throw ValidationException::withMessages(['code' => 'Your account is already linked to another teacher. Ask your teacher for help joining this programme.']);
            }
            if ($programme->students()->whereKey($student->id)->exists()) {
                return ['programme' => $programme, 'joined' => false];
            }
            if (! $student->teacher_id) {
                $student->forceFill(['teacher_id' => $programme->teacher_id])->save();
            }
            $programme->students()->attach($student);

            return ['programme' => $programme, 'joined' => true];
        }, 5);
    }

    private function check(?ProgrammeInvitation $invitation): void
    {
        $programme = $invitation ? Programme::whereKey($invitation->programme_id)->first() : null;
        if (! $invitation || ! $invitation->enabled || ! $programme || ! $programme->published
            || ! User::whereKey($programme->teacher_id)->where('role', 'teacher')->exists()) {
            throw ValidationException::withMessages(['code' => 'This invitation code is not valid or is no longer open. Check the code or ask your teacher.']);
        }
    }

    private function ensureOwner(User $teacher, Programme $programme): void
    {
        abort_unless($teacher->role === 'teacher' && $programme->teacher_id === $teacher->id, 403);
    }

    /**
     * Retry on the rare unique-code collision, including a concurrent first issue for the same programme.
     *
     * @template T
     *
     * @param  callable(string): T  $write
     * @param  (callable(): ?T)|null  $existing
     * @return T
     */
    private function withFreshCode(callable $write, ?callable $existing = null): mixed
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $write($this->generate());
            } catch (UniqueConstraintViolationException $exception) {
                if ($existing && ($found = $existing())) {
                    return $found;
                }
                if ($attempt >= 5) {
                    throw $exception;
                }
            }
        }
    }

    private function generate(): string
    {
        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return $code;
    }
}
