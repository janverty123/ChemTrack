<?php

namespace App\Filament\Actions;

use App\Services\CycleProgress;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class OpenCycleTwo
{
    public static function make(): Action
    {
        return Action::make('openCycleTwo')->label('Open Cycle 2')->color('gray')->requiresConfirmation()
            ->modalDescription('Students must also complete all four stages of Cycle 1. Existing results will be preserved.')
            ->visible(fn ($record) => ! app(CycleProgress::class)->released($record))
            ->action(function ($record) {
                app(CycleProgress::class)->release(auth()->user(), $record);
                Notification::make()->title('Cycle 2 is open for eligible students.')->success()->send();
            });
    }
}
