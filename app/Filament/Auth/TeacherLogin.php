<?php

namespace App\Filament\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;

/**
 * Filament's own sign-in is only used for the authenticator step of teachers with two-factor
 * authentication. Everyone else signs in on the main ChemTrack page.
 */
class TeacherLogin extends Login
{
    public const HANDOFF_KEY = 'teacher_mfa_handoff_until';

    public function mount(): void
    {
        if (! Filament::auth()->check() && ! self::hasHandoff()) {
            $this->redirect('/login');

            return;
        }

        parent::mount();
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if (Filament::auth()->check()) {
            session()->forget(self::HANDOFF_KEY);
        }

        return $response;
    }

    public static function hasHandoff(): bool
    {
        return (int) session(self::HANDOFF_KEY, 0) > now()->getTimestamp();
    }
}
