<?php

namespace App\Filament\Resources\Programmes\Tables;

use App\Filament\Actions\EnrollStudents;
use App\Filament\Actions\InviteStudents;
use App\Filament\Actions\OpenCycleTwo;
use App\Filament\Resources\Programmes\ProgrammeResource;
use App\Models\Programme;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgrammesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                IconColumn::make('published')->boolean()->label('Published'),
                TextColumn::make('students_count')->counts('students')->label('Students'),
                TextColumn::make('activities_count')->counts('activities')->label('Activities'),
                TextColumn::make('history_status')->label('Content')->state(fn (Programme $record) => $record->hasStarted() ? 'Locked: learning started' : 'Editable'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label('Open'),
                // Secondary actions share one menu so the row never overflows the table width.
                ActionGroup::make([
                    Action::make('results')->label('Student progress')->icon(Heroicon::OutlinedChartBar)->url(fn ($record) => '/teacher/programmes/'.$record->id.'/results'),
                    InviteStudents::make(),
                    EnrollStudents::make()->icon(Heroicon::OutlinedUserGroup),
                    OpenCycleTwo::make()->icon(Heroicon::OutlinedLockOpen),
                    EditAction::make(),
                ])->label('More')->tooltip('More actions'),
            ])
            ->recordUrl(fn (Programme $record) => ProgrammeResource::getUrl('view', ['record' => $record]))
            ->toolbarActions([
            ]);
    }
}
