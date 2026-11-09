<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\ProgressReport;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Url;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.view-user';

    #[Url]
    public int $cycle = 1;

    public function selectCycle(int $cycle): void
    {
        abort_unless(in_array($cycle, [1, 2], true), 422);
        $this->cycle = $cycle;
    }

    public function getHeading(): string
    {
        return $this->getRecord()->studentReference();
    }

    public function getSubheading(): string
    {
        return 'Student progress';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // This overview does not render an account form or send legacy identity fields.
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('manageAccount')->label('Manage account')->url(UserResource::getUrl('edit', ['record' => $this->getRecord()]))];
    }

    protected function getViewData(): array
    {
        abort_unless(in_array($this->cycle, [1, 2], true), 422);
        $student = $this->getRecord();
        $teacher = auth()->user();
        $programmes = $student->programmes()->where('teacher_id', $teacher->id)->get();
        $reports = $programmes->map(function ($programme) use ($teacher, $student) {
            $report = app(ProgressReport::class)->forProgramme($teacher, $programme, $student);
            $report['summary'] = $report['cycles'][$student->id][$this->cycle];

            return $report;
        });

        return compact('student', 'reports');
    }
}
