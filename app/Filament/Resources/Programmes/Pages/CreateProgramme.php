<?php

namespace App\Filament\Resources\Programmes\Pages;

use App\Filament\Resources\Programmes\ProgrammeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateProgramme extends CreateRecord
{
    use ReportsProgrammeSaveErrors;

    protected static string $resource = ProgrammeResource::class;

    public function create(bool $another = false): void
    {
        try {
            parent::create($another);
        } catch (ValidationException $exception) {
            // The publish check runs after the programme is created, so a rejection rolls the new
            // record back. Forget it, or the next attempt would try to load a programme that no longer exists.
            $this->record = null;
            $this->reportSaveError($exception);

            throw $exception;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['teacher_id'] = auth()->id();
        $data['cycle_workflow'] = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->ensurePublishable($this->record);
    }
}
