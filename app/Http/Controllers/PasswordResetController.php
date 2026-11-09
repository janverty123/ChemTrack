<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function create()
    {
        return response()->view('auth.forgot-password')->header('Cache-Control', 'no-store, private');
    }

    public function email(Request $request)
    {
        $data = $request->validate(['email' => 'required|email:rfc|max:254']);
        Password::sendResetLink(['email' => strtolower(trim($data['email']))]);

        return back()->with('status', 'If an account can receive email, a password reset link has been sent.');
    }

    public function edit(Request $request, string $token)
    {
        return response()->view('auth.reset-password', ['token' => $token, 'email' => $request->query('email', '')])->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['token' => 'required|string|max:255', 'email' => 'required|email:rfc|max:254', 'password' => 'required|string|min:12|max:200|confirmed']);
        $status = Password::reset($data, function (User $user, string $password) {
            DB::transaction(function () use ($user, $password) {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                DB::table('sessions')->where('user_id', $user->id)->delete();
            });
            event(new PasswordReset($user));
        });
        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => 'This password reset link is invalid or expired. Request a new link.']);
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Your password has been reset. Sign in with your new password.');
    }
}
