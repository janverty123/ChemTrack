<?php

namespace App\Filament\Actions;

use App\Models\Programme;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class EnrollStudents
{
    public static function make(): Action
    {
        return Action::make('enroll')->label('Enroll students')->schema([
            Select::make('students')->multiple()->searchable()
                ->options(fn () => User::where('teacher_id', auth()->id())->where('role', 'student')->get()->mapWithKeys(fn (User $student) => [$student->id => $student->studentReference()]))
                ->required(),
        ])->action(function (Programme $record, array $data) {
            abort_unless(auth()->user()->role === 'teacher' && $record->teacher_id === auth()->id(), 403);
            $ids = User::where('teacher_id', auth()->id())->where('role', 'student')->whereIn('id', $data['students'])->pluck('id');
            abort_unless($ids->count() === count(array_unique($data['students'])), 403);
            $record->students()->syncWithoutDetaching($ids);
        });
    }
}
