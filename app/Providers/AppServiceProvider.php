<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponse::class, fn () => new class implements LogoutResponse
        {
            public function toResponse($request): RedirectResponse
            {
                return redirect('/login');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Five attempts per account per minute stops password guessing, while the per-IP ceiling
        // still lets a whole class sign in from one school network at the same time.
        RateLimiter::for('login', function (Request $request): array {
            $identifier = Str::lower(trim((string) $request->input('identifier', $request->input('email', ''))));

            return [
                Limit::perMinute(5)->by('login:'.hash('sha256', $identifier).'|'.$request->ip()),
                Limit::perMinute(120)->by('login-ip:'.$request->ip()),
            ];
        });
    }
}
