<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompletedAccount
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->role === 'student' && $user->registration_required && (! $user->account_completed_at || ! $user->email_verified_at)) {
            return redirect('/account/complete');
        }

        return $next($request);
    }
}
