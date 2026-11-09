<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('student_code')->label('Student reference')->formatStateUsing(fn (?User $record) => $record?->studentReference())->disabled()->dehydrated(false)->visibleOn('edit'),
                Select::make('category')->label('G/B category')->options(['G' => 'G', 'B' => 'B'])->required(fn (string $operation) => $operation === 'create')->disabled(fn (?User $record) => filled($record?->student_code))->dehydrated(fn (?User $record) => blank($record?->student_code))->helperText('New students receive the next permanent code in this category. For legacy accounts, choose a category only when confirmed by the teacher.'),
                TextInput::make('name')->label('Existing legacy display name')->maxLength(255)->visible(fn (?User $record) => $record && ! $record->student_code)->helperText('Retained for legacy account compatibility. Student lists use a neutral learner reference.'),
                TextInput::make('password')->label('Set or reset password')->password()->revealable()->minLength(12)->maxLength(200)->required(fn (string $operation) => $operation === 'create')->dehydrated(fn (?string $state): bool => filled($state))->helperText('At least 12 characters. Leave blank when editing to keep the existing password. Share a reset privately with the student.'),
            ]);
    }
}
