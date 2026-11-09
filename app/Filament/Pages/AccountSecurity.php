<?php

namespace App\Filament\Pages;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Pages\Page;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class AccountSecurity extends Page
{
    protected static ?string $title = 'Account security';

    protected static ?string $navigationLabel = 'Account security';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.account-security';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'teacher';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make(AppAuthentication::make()->recoverable()->codeWindow(1)->getManagementSchemaComponents())->statePath('app'),
        ])->statePath('data');
    }
}
