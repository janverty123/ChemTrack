<?php

namespace App\Filament\Actions;

use App\Models\Programme;
use App\Models\ProgrammeInvitation;
use App\Services\ProgrammeInvitations;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class InviteStudents
{
    public static function make(): Action
    {
        return Action::make('inviteStudents')->label('Invite students')->icon(Heroicon::OutlinedUserPlus)
            ->modalHeading('Invite students')
            ->modalDescription(fn (Programme $record) => self::description($record))
            ->schema(function (Programme $record) {
                $invitation = self::invitation($record);

                return [
                    TextEntry::make('invitation_code')->label('Invitation code')->state($invitation->code)
                        ->copyable()->copyMessage('Code copied')->weight(FontWeight::Bold)->size(TextSize::Large)
                        ->icon(Heroicon::OutlinedClipboardDocument)->iconPosition(IconPosition::After)->helperText('Click the code or link to copy it.'),
                    TextEntry::make('invitation_link')->label('Invitation link')->state($invitation->url())
                        ->copyable()->copyMessage('Link copied')->icon(Heroicon::OutlinedClipboardDocument)->iconPosition(IconPosition::After),
                ];
            })
            ->modalWidth(Width::Large)
            ->modalSubmitAction(false)->modalCancelActionLabel('Close')
            ->extraModalFooterActions(fn (Programme $record) => [
                Action::make('newInvitationCode')->label('New code')->icon(Heroicon::OutlinedArrowPath)->color('gray')->requiresConfirmation()
                    ->modalDescription('The current code and link stop working. Students who already joined stay in this programme.')
                    ->action(function () use ($record) {
                        app(ProgrammeInvitations::class)->regenerate(auth()->user(), $record);
                        Notification::make()->title('A new invitation code is ready.')->success()->send();
                    })->cancelParentActions(),
                Action::make('toggleInvitation')
                    ->label(fn () => self::invitation($record)->enabled ? 'Turn off joining' : 'Turn on joining')
                    ->color(fn () => self::invitation($record)->enabled ? 'gray' : 'primary')
                    ->icon(fn () => self::invitation($record)->enabled ? Heroicon::OutlinedNoSymbol : Heroicon::OutlinedCheckCircle)
                    ->action(function () use ($record) {
                        $enabled = ! self::invitation($record)->enabled;
                        app(ProgrammeInvitations::class)->setEnabled(auth()->user(), $record, $enabled);
                        Notification::make()->title($enabled ? 'Students can join with the invitation code.' : 'Joining is turned off. Enrolled students keep their access.')->success()->send();
                    })->cancelParentActions(),
            ]);
    }

    private static function invitation(Programme $programme): ProgrammeInvitation
    {
        return app(ProgrammeInvitations::class)->forProgramme(auth()->user(), $programme);
    }

    private static function description(Programme $programme): string
    {
        if (! self::invitation($programme)->enabled) {
            return 'Joining is turned off, so this code does not work right now. Turn it on to let students join.';
        }
        if (! $programme->published) {
            return 'Students can use this code once you publish the programme.';
        }

        return 'Share the code or link with your class. Students sign in or create an account, then join this programme.';
    }
}
