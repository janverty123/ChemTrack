<?php

namespace App\Providers\Filament;

use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('teacher')
            ->brandName('ChemTrack · Teacher')
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2.25rem')
            ->databaseTransactions()
            ->font('Inter', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode(fn () => auth()->check())
            ->renderHook('panels::head.start', fn () => view('components.appearance'))
            ->multiFactorAuthentication([AppAuthentication::make()->recoverable()->codeWindow(1)])
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('16.125rem')
            ->navigationItems([NavigationItem::make('Settings')->url('/settings')->icon('heroicon-o-cog-6-tooth')->sort(90)])
            ->userMenuItems(['settings' => Action::make('settings')->label('Profile & settings')->url('/settings')->icon('heroicon-o-user-circle')])
            ->login()
            ->colors([
                'primary' => '#115E59',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
