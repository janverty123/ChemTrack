<?php

namespace App\Filament\Resources\Programmes\Pages;

use App\Filament\Resources\Programmes\ProgrammeResource;
use App\Services\CycleProgress;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateProgramme extends CreateRecord
{
    protected static string $resource = ProgrammeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['teacher_id'] = auth()->id();
        $data['cycle_workflow'] = true;

        return $data;
    }

    protected function afterCreate(): void
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
