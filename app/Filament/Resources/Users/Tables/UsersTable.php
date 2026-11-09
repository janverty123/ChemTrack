<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_code')->label('Student reference')->state(fn (User $record): string => $record->studentReference())->searchable()->sortable(),
                TextColumn::make('category')->label('G/B category')->placeholder('Not assigned'),
                TextColumn::make('programmes_count')->label('Programmes')->sortable(),
                TextColumn::make('created_at')->date()->label('Added'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label('Progress'),
                EditAction::make()->label('Manage account'),
            ])
            ->recordUrl(fn (User $record) => UserResource::getUrl('view', ['record' => $record]))
            ->toolbarActions([
            ]);
    }
}
