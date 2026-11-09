<?php

namespace App\Filament\Resources\Programmes\Pages;

use App\Filament\Actions\EnrollStudents;
use App\Filament\Actions\OpenCycleTwo;
use App\Filament\Resources\Programmes\ProgrammeResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProgramme extends ViewRecord
{
    protected static string $resource = ProgrammeResource::class;

    public function getSubheading(): string
    {
        return $this->getRecord()->hasStarted()
            ? 'Students have started. Content is read-only to preserve their learning history.'
            : 'Review the content, enroll students or open the progress report.';
    }

    protected function getHeaderActions(): array
    {
        return [
            OpenCycleTwo::make(),
            Action::make('progress')->label('Student progress')->url(fn () => '/teacher/programmes/'.$this->getRecord()->id.'/results'),
            EnrollStudents::make()->color('gray'),
            EditAction::make()->label('Edit content'),
        ];
    }
}
