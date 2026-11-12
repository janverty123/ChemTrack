<?php

namespace App\Http\Controllers;

use App\Filament\Auth\TeacherLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $field = $request->has('identifier') ? 'identifier' : 'email';
        $data = $request->validate([$field => 'required|string|max:254', 'password' => 'required|string|max:200']);
        $identifier = trim($data[$field]);
        $credentials = ['password' => $data['password']];
        $credentials[filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'student_code'] = str_contains($identifier, '@') ? $identifier : strtoupper($identifier);
        if (isset($credentials['email'])) {
            $credentials['email'] = strtolower($credentials['email']);
        }
        $candidate = User::where(array_diff_key($credentials, ['password' => true]))->first();
        if ($candidate?->role === 'teacher' && filled($candidate->app_authentication_secret) && Hash::check($data['password'], $candidate->password)) {
            $request->session()->put(TeacherLogin::HANDOFF_KEY, now()->addMinutes(10)->getTimestamp());

            return redirect('/teacher/login')->with('status', 'Continue secure teacher sign-in with your authenticator.');
        }
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([$field => 'The student code, email or password is incorrect.']);
        }
        $request->session()->regenerate();

        $user = $request->user();

        return redirect($user->role === 'teacher' ? '/teacher' : ($user->registration_required && (! $user->account_completed_at || ! $user->email_verified_at) ? '/account/complete' : '/learn'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
