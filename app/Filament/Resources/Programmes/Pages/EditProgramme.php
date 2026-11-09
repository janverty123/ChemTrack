<?php

namespace App\Filament\Resources\Programmes\Pages;

use App\Filament\Actions\OpenCycleTwo;
use App\Filament\Resources\Programmes\ProgrammeResource;
use App\Services\CycleProgress;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditProgramme extends EditRecord
{
    protected static string $resource = ProgrammeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            OpenCycleTwo::make(),
            Action::make('progress')->label('Student progress')->url(fn () => '/teacher/programmes/'.$this->getRecord()->id.'/results'),
            Action::make('enrolment')->label('Manage enrolment')->color('gray')->url(ProgrammeResource::getUrl()),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->cycle_workflow && $this->record->published) {
            $progress = app(CycleProgress::class);
            $programme = $this->record->fresh('activities');
            if (! $progress->summary($programme, 1, [])['ready'] || ! $progress->summary($programme, 2, [])['ready']) {
                throw ValidationException::withMessages(['data.published' => 'Save as a draft until all four stages in both cycles are configured.']);
            }
        }
    }
}
