<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AccountSecurityNotice;
use App\Notifications\ConfirmAccountEmail;
use App\Services\AccountAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $sessions = config('session.driver') === 'database'
            ? DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))->where('user_id', $request->user()->id)->orderByDesc('last_activity')->get(['id', 'user_agent', 'last_activity'])
            : collect();

        return response()->view('settings.index', ['user' => $request->user(), 'sessions' => $sessions])->header('Cache-Control', 'no-store, private');
    }

    public function profile(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+() .-]+$/'],
            'school' => ['nullable', 'string', 'max:150'],
            'designation' => ['nullable', 'string', 'max:100'],
            'display_name' => ['nullable', 'string', 'max:100'],
        ]);
        $user = $request->user();
        $identity = $user->private_identity ?? [];
        $identity['full_name'] = $data['full_name'];
        $identity['phone'] = $data['phone'] ?? null;
        $changes = ['private_identity' => $identity];
        if ($user->role === 'teacher') {
            $changes += ['name' => ($data['display_name'] ?? '') ?: $data['full_name'], 'school' => $data['school'] ?? null, 'designation' => $data['designation'] ?? null];
        }
        $user->forceFill($changes)->save();
        $this->audit($user, 'profile.updated');

        return redirect('/settings')->with('status', 'Profile saved.');
    }

    public function preferences(Request $request)
    {
        $data = $request->validate([
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
            'text_size' => ['required', Rule::in(['standard', 'large'])],
            'reduced_motion' => ['sometimes', 'boolean'],
            'default_cycle' => ['required', Rule::in([1, 2])],
        ]);
        $data['reduced_motion'] = $request->boolean('reduced_motion');
        $data['default_cycle'] = (int) $data['default_cycle'];
        $request->user()->forceFill(['preferences' => $data])->save();

        return redirect('/settings')->with('status', 'Appearance and learning preferences saved.');
    }

    public function password(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'confirmed', Password::min(12), 'max:200']]);
        Auth::logoutOtherDevices($data['current_password']);
        $request->user()->forceFill(['password' => Hash::make($data['password']), 'remember_token' => Str::random(60)])->save();
        $this->deleteOtherSessions($request);
        $request->session()->put('password_hash_web', $request->user()->getAuthPassword());
        $request->session()->regenerate();
        $this->audit($request->user(), 'password.changed');

        return redirect('/settings')->with('status', 'Password changed. Other sessions have been signed out.');
    }

    public function requestEmail(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'email' => ['required', 'email:rfc', 'max:254']]);
        $email = Str::lower($data['email']);
        // A uniform result avoids disclosing whether a mailbox already belongs to an account.
        if (! User::where('email', $email)->exists()) {
            $token = Str::random(64);
            $user = $request->user();
            $expires = now()->addMinutes(30);
            $user->forceFill(['pending_email' => $email, 'pending_email_token_hash' => hash('sha256', $token), 'pending_email_expires_at' => $expires])->save();
            $url = URL::temporarySignedRoute('settings.email.confirm', $expires, ['user' => $user->id, 'token' => $token]);
            Notification::route('mail', $email)->notify(new ConfirmAccountEmail($url));
            $this->audit($user, 'email.change_requested');
        }

        return redirect('/settings')->with('status', 'If the new address is available, a confirmation link has been requested. Your current email stays active until confirmation.');
    }

    public function reviewEmail(Request $request, string $user, string $token)
    {
        $this->validateEmailChallenge($request, $user, $token);

        return response()->view('settings.confirm-email')->header('Cache-Control', 'no-store, private');
    }

    public function confirmEmail(Request $request, string $user, string $token)
    {
        $oldEmail = DB::transaction(function () use ($request, $user, $token) {
            $account = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $this->validateEmailChallenge($request, $user, $token, $account);
            if (User::where('email', $account->pending_email)->whereKeyNot($account->id)->exists()) {
                throw ValidationException::withMessages(['email' => 'The email change could not be completed. Request a new link or choose another address.']);
            }
            $old = $account->email;
            $account->forceFill(['email' => $account->pending_email, 'email_verified_at' => now(), 'pending_email' => null, 'pending_email_token_hash' => null, 'pending_email_expires_at' => null])->save();
            $this->audit($account, 'email.changed');

            return $old;
        });
        Notification::route('mail', $oldEmail)->notify(new AccountSecurityNotice('Your ChemTrack email address was changed. This notice was sent to your previous address.'));

        return redirect('/settings')->with('status', 'Email changed and verified.');
    }

    private function validateEmailChallenge(Request $request, string $user, string $token, ?User $account = null): void
    {
        $account ??= $request->user();
        abort_unless((string) $account->id === $user && $account->pending_email && $account->pending_email_expires_at?->isFuture() && hash_equals($account->pending_email_token_hash ?? '', hash('sha256', $token)), 403);
    }

    public function revokeSessions(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'current_password']]);
        Auth::logoutOtherDevices($data['current_password']);
        $this->deleteOtherSessions($request);
        $request->session()->put('password_hash_web', $request->user()->getAuthPassword());
        $this->audit($request->user(), 'sessions.revoked');

        return redirect('/settings')->with('status', 'Other sessions signed out.');
    }

    private function deleteOtherSessions(Request $request): void
    {
        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))->where('user_id', $request->user()->id)->where('id', '!=', $request->session()->getId())->delete();
        }
    }

    private function audit(User $user, string $action): void
    {
        DB::table('account_security_events')->insert(['user_id' => $user->id, 'action' => $action, 'created_at' => now()]);
    }

    public function avatar(Request $request)
    {
        $path = $request->user()->avatar_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'no-store, private', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function saveAvatar(Request $request, AccountAvatar $avatar)
    {
        $request->validate(['avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096']]);
        $avatar->store($request->user(), $request->file('avatar'));

        return redirect('/settings')->with('status', 'Photo saved.');
    }

    public function deleteAvatar(Request $request)
    {
        $old = $request->user()->avatar_path;
        $request->user()->forceFill(['avatar_path' => null])->save();
        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return redirect('/settings')->with('status', 'Photo removed.');
    }
}
