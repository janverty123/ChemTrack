<?php

namespace App\Filament\Resources\Programmes;

use App\Filament\Resources\Programmes\Pages\CreateProgramme;
use App\Filament\Resources\Programmes\Pages\EditProgramme;
use App\Filament\Resources\Programmes\Pages\ListProgrammes;
use App\Filament\Resources\Programmes\Pages\ViewProgramme;
use App\Filament\Resources\Programmes\Schemas\ProgrammeForm;
use App\Filament\Resources\Programmes\Tables\ProgrammesTable;
use App\Models\Programme;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProgrammeResource extends Resource
{
    protected static ?string $model = Programme::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('teacher_id', auth()->id());
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    public static function form(Schema $schema): Schema
    {
        return ProgrammeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgrammesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProgrammes::route('/'),
            'create' => CreateProgramme::route('/create'),
            'view' => ViewProgramme::route('/{record}'),
            'edit' => EditProgramme::route('/{record}/edit'),
        ];
    }
}
