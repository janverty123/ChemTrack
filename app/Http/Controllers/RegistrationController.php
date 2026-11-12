<?php

namespace App\Http\Controllers;

use App\Models\SignupChallenge;
use App\Models\User;
use App\Notifications\SignupCode;
use App\Services\StudentAccounts;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    private function base(Request $request): string
    {
        return $request->is('account/*') ? '/account/complete' : '/register';
    }

    private function page(string $view, array $data = [])
    {
        return response()->view($view, $data)->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer');
    }

    public function create(Request $request)
    {
        if ($request->user()) {
            abort_unless($request->user()->role === 'student', 403);
            if ($request->user()->account_completed_at) {
                return redirect('/settings');
            }
        }
        if (! $request->boolean('restart') && ! $request->has('invitation') && ($challenge = $this->challenge($request))) {
            return redirect($this->base($request).($challenge->verified_at && ! $challenge->expires_at->isPast() ? '/profile' : '/verify'));
        }

        return $this->page('auth.register', ['invitation' => $request->query('invitation', ''), 'base' => $this->base($request)]);
    }

    public function store(Request $request, StudentAccounts $accounts)
    {
        $completion = $this->base($request) !== '/register';
        if ($completion) {
            abort_unless($request->user()->role === 'student' && ! $request->user()->account_completed_at, 403);
        }
        $data = $request->validate(['email' => 'required|email:rfc|max:254', 'invitation' => $completion ? 'nullable' : 'required|string|max:200']);
        $email = strtolower(trim($data['email']));
        $invitation = $completion ? null : $accounts->availableInvitation($data['invitation']);
        if (User::where('email', $email)->when($completion, fn ($query) => $query->where('id', '!=', $request->user()->id))->exists()) {
            throw ValidationException::withMessages(['email' => 'This email cannot be used. Sign in or ask your teacher for help.']);
        }
        $this->limitMail($email, $request);
        $this->challenge($request)?->update(['consumed_at' => now()]);
        $binding = Str::random(64);
        $code = (string) random_int(100000, 999999);
        $challenge = SignupChallenge::create([
            'id' => (string) Str::uuid(), 'invitation_id' => $invitation?->id,
            'user_id' => $completion ? $request->user()->id : null,
            'email' => $email, 'session_hash' => hash('sha256', $binding), 'code_hash' => Hash::make($code),
            'sent_at' => now(), 'expires_at' => now()->addMinutes(10),
        ]);
        $request->session()->put(['signup_challenge' => $challenge->id, 'signup_binding' => $binding]);
        try {
            Notification::route('mail', $email)->notify(new SignupCode($code));
        } catch (\Throwable $exception) {
            $challenge->delete();
            $request->session()->forget(['signup_challenge', 'signup_binding']);
            report($exception);
            throw ValidationException::withMessages(['email' => 'We could not send your code. Please try again later or ask your teacher.']);
        }

        return redirect($this->base($request).'/verify');
    }

    private function limitMail(string $email, Request $request): void
    {
        $key = 'signup-mail:'.hash('sha256', $email);
        $ipKey = 'signup-mail-ip:'.hash('sha256', $request->ip() ?? 'unknown');
        // The per-IP ceiling allows a whole class signing up on one school network; the per-email limit stops abuse.
        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 40)) {
            throw ValidationException::withMessages(['email' => 'Please wait before requesting another verification email.']);
        }
        // Shared atomic reservation covers start/restart/resend and concurrent browser sessions.
        if (! Cache::add('signup-mail-cooldown:'.hash('sha256', $email), true, 60)) {
            throw ValidationException::withMessages(['email' => 'Wait at least 60 seconds before requesting another code.']);
        }
        RateLimiter::hit($key, 600);
        RateLimiter::hit($ipKey, 60);
    }

    private function challenge(Request $request, bool $lock = false): ?SignupChallenge
    {
        $binding = $request->session()->get('signup_binding');
        if (! $binding) {
            return null;
        }
        $query = SignupChallenge::whereKey($request->session()->get('signup_challenge'));
        $challenge = ($lock ? $query->lockForUpdate() : $query)->first();
        if (! $challenge || ! hash_equals($challenge->session_hash, hash('sha256', $binding)) || $challenge->consumed_at) {
            return null;
        }
        $userId = $this->base($request) === '/register' ? null : $request->user()?->id;

        return $challenge->user_id === $userId ? $challenge : null;
    }

    public function verifyPage(Request $request)
    {
        $challenge = $this->challenge($request);
        if (! $challenge) {
            return redirect($this->base($request));
        }
        [$local, $domain] = explode('@', $challenge->email, 2);

        return $this->page('auth.verify', ['base' => $this->base($request), 'destination' => mb_substr($local, 0, 1).'•••@'.$domain, 'resendAfter' => max(0, 60 - (int) $challenge->sent_at->diffInSeconds(now()))]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        $valid = DB::transaction(function () use ($request) {
            $challenge = $this->challenge($request, true);
            if (! $challenge || $challenge->expires_at->isPast() || $challenge->failed_attempts >= 5 || $challenge->verified_at) {
                return false;
            }
            if (! Hash::check($request->string('code')->toString(), $challenge->code_hash)) {
                $challenge->increment('failed_attempts');

                return false;
            }
            $challenge->update(['verified_at' => now()]);

            return true;
        });
        if (! $valid) {
            throw ValidationException::withMessages(['code' => 'The code is incorrect, expired or unavailable. Request a new code if needed.']);
        }

        return redirect($this->base($request).'/profile');
    }

    public function resend(Request $request)
    {
        DB::transaction(function () use ($request) {
            $challenge = $this->challenge($request, true);
            if (! $challenge || $challenge->sent_at->copy()->addSeconds(60)->isFuture()) {
                throw ValidationException::withMessages(['code' => 'Wait at least 60 seconds before requesting another code.']);
            }
            $this->limitMail($challenge->email, $request);
            $code = (string) random_int(100000, 999999);
            while (Hash::check($code, $challenge->code_hash)) {
                $code = (string) random_int(100000, 999999);
            }
            $challenge->update(['code_hash' => Hash::make($code), 'sent_at' => now(), 'expires_at' => now()->addMinutes(10), 'verified_at' => null, 'failed_attempts' => 0]);
            try {
                Notification::route('mail', $challenge->email)->notify(new SignupCode($code));
            } catch (\Throwable $exception) {
                // Rolling back keeps the previously sent code valid.
                report($exception);
                throw ValidationException::withMessages(['code' => 'We could not send a new code. Please try again later or ask your teacher.']);
            }
        });

        return redirect($this->base($request).'/verify');
    }

    public function profile(Request $request)
    {
        $challenge = $this->challenge($request);
        if (! $challenge || ! $challenge->verified_at || $challenge->expires_at->isPast()) {
            return redirect($this->base($request).'/verify');
        }

        return $this->page('auth.registration-profile', ['base' => $this->base($request), 'completion' => (bool) $challenge->user_id]);
    }

    public function finish(Request $request, StudentAccounts $accounts)
    {
        $challenge = $this->challenge($request);
        if (! $challenge || ! $challenge->verified_at || $challenge->expires_at->isPast()) {
            return redirect($this->base($request).'/verify');
        }
        $data = $request->validate([
            'full_name' => 'required|string|max:150', 'lrn' => 'required|digits:12',
            'category' => $challenge->user_id ? 'nullable' : 'required|in:G,B',
            'password' => 'required|string|min:12|max:200|confirmed',
            'phone' => 'nullable|string|max:30', 'grade' => 'nullable|string|max:40', 'section' => 'nullable|string|max:60',
        ]);
        try {
            $student = $accounts->finishVerified($challenge, $data);
        } catch (UniqueConstraintViolationException $exception) {
            if (! str_contains($exception->getMessage(), 'email') && ! str_contains($exception->getMessage(), 'identity_hash')) {
                throw $exception;
            }
            throw ValidationException::withMessages(['email' => 'These account details could not be accepted. Sign in or ask your teacher for help.']);
        }
        Auth::login($student);
        $request->session()->forget(['signup_challenge', 'signup_binding']);
        $request->session()->regenerate();

        return redirect('/learn')->with('status', 'Your student code is '.$student->studentReference().'. Keep it for future sign-ins.');
    }
}
