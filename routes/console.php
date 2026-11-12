<?php

use App\Models\SignupChallenge;
use Illuminate\Support\Facades\Schedule;

// Signup challenges hold a plain email address only while verification is in progress.
// Remove abandoned and finished ones a day after they expire (requires the scheduler cron; see docs/deployment.md).
Schedule::call(fn () => SignupChallenge::where('expires_at', '<', now()->subDay())->delete())
    ->daily()
    ->name('prune-signup-challenges')
    ->withoutOverlapping();
