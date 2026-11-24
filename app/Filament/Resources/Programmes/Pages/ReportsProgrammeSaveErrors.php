<?php

namespace App\Filament\Resources\Programmes\Pages;

use App\Models\Programme;
use App\Services\CycleProgress;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Shared by the create and edit pages so a programme is never rejected silently.
 *
 * Model rules raise errors with keys such as `questions` that match no form field, so Filament
 * would roll back without showing anything. Every rejection is also shown as a notification.
 */
trait ReportsProgrammeSaveErrors
{
    protected function ensurePublishable(Programme $record): void
    {
        if (! $record->cycle_workflow || ! $record->published) {
            return;
        }
        $progress = app(CycleProgress::class);
        $programme = $record->fresh('activities');
        $missing = [];
        foreach ([1, 2] as $cycle) {
            $stages = collect($progress->summary($programme, $cycle, [])['stages'])->reject(fn (array $stage) => $stage['configured']);
            if ($stages->isNotEmpty()) {
                $missing[] = 'Cycle '.$cycle.': '.$stages->pluck('label')->implode(', ');
            }
        }
        if ($missing !== []) {
            throw ValidationException::withMessages(['data.published' => 'To publish, both cycles need a finished pre-test, module (with a lesson part), video lesson and post-test. Still missing or incomplete: '.implode('; ', $missing).'. Switch this off to save as a draft.']);
        }
    }

    protected function reportSaveError(ValidationException $exception): void
    {
        Notification::make()->danger()->title('The programme was not saved')
            ->body(implode(' ', array_unique(Arr::flatten($exception->errors()))))->persistent()->send();
    }
}
