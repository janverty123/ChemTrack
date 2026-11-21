<?php

namespace App\Filament\Resources\Programmes\Pages;

use App\Filament\Actions\EnrollStudents;
use App\Filament\Actions\InviteStudents;
use App\Filament\Actions\OpenCycleTwo;
use App\Filament\Resources\Programmes\ProgrammeResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewProgramme extends ViewRecord
{
    protected static string $resource = ProgrammeResource::class;

    public function getSubheading(): string
    {
        return $this->getRecord()->hasStarted()
            ? 'Students have started. Content is read-only to preserve their learning history.'
            : 'Review the content, invite students or open the progress report.';
    }

    protected function getHeaderActions(): array
    {
        // One primary action; the rest share the outlined style and carry icons so the row reads evenly.
        return [
            InviteStudents::make(),
            Action::make('progress')->label('Student progress')->icon(Heroicon::OutlinedChartBar)->color('gray')->url(fn () => '/teacher/programmes/'.$this->getRecord()->id.'/results'),
            EnrollStudents::make()->icon(Heroicon::OutlinedUserGroup)->color('gray'),
            OpenCycleTwo::make()->icon(Heroicon::OutlinedLockOpen),
            EditAction::make()->label('Edit content')->color('gray'),
        ];
    }
}
