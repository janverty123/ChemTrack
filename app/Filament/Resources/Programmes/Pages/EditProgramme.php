<?php

namespace App\Filament\Resources\Programmes\Pages;

use App\Filament\Actions\OpenCycleTwo;
use App\Filament\Resources\Programmes\ProgrammeResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditProgramme extends EditRecord
{
    use ReportsProgrammeSaveErrors;

    protected static string $resource = ProgrammeResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        try {
            parent::save($shouldRedirect, $shouldSendSavedNotification);
        } catch (ValidationException $exception) {
            $this->reportSaveError($exception);

            throw $exception;
        }
    }

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
        $this->ensurePublishable($this->record);
    }
}
