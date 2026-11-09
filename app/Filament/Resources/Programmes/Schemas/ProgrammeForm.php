<?php

namespace App\Filament\Resources\Programmes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProgrammeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255),
                Toggle::make('published')->label('Available to enrolled students')->default(false),
                Textarea::make('description')->maxLength(3000)->columnSpanFull(),
                Repeater::make('activities')->relationship(modifyRecordsUsing: fn ($records) => $records->each->makeVisible('questions'))->orderColumn('position')
                    ->label('Learning activities')->columnSpanFull()->minItems(1)->maxItems(40)->collapsible()
                    ->itemLabel(fn (array $state): string => $state['title'] ?? 'New activity')
                    ->schema([
                        TextInput::make('title')->required()->maxLength(255),
                        Select::make('cycle')->label('Cycle')->options([1 => 'Cycle 1', 2 => 'Cycle 2'])->nullable()->required(fn ($record) => ! $record || $record->programme?->cycle_workflow),
                        Select::make('type')->options(['lesson' => 'Lesson', 'pre_test' => 'Pre-test', 'checkpoint' => 'Module assessment', 'video' => 'Video lesson', 'post_test' => 'Post-test'])->required()->live()->default('lesson'),
                        Select::make('assessment_source')->label('Assessment format')->options(['native' => 'ChemTrack assessment', 'google_forms' => 'Google Forms'])->default('native')->required()->live()->visible(fn (Get $get) => in_array($get('type'), ['pre_test', 'post_test'])),
                        TextInput::make('google_form_url')->label('Original Google Form link')->url()->startsWith('https://')->maxLength(2048)->visible(fn (Get $get) => in_array($get('type'), ['pre_test', 'post_test']) && $get('assessment_source') === 'google_forms')->helperText('Completion verification is not connected.'),
                        Textarea::make('content')->label('Lesson text')->rows(7)->maxLength(30000)->visible(fn (Get $get) => $get('type') === 'lesson'),
                        FileUpload::make('document_path')->label('Optional module PDF')->disk('local')->directory(fn () => 'modules/teacher-'.auth()->id())->visibility('private')->preventFilePathTampering(allowFilePathUsing: fn (string $file): bool => auth()->user()?->role === 'teacher' && str_starts_with($file, 'modules/teacher-'.auth()->id().'/') && ! str_contains($file, '..'))->acceptedFileTypes(['application/pdf'])->maxSize(10240)->downloadable(false)->openable(false)->visible(fn (Get $get) => $get('type') === 'lesson')->helperText('PDF up to 10 MB. Original formatting is preserved.'),
                        TextInput::make('video_url')->label('Video link')->url()->startsWith('https://')->maxLength(2048)->required(fn (Get $get) => $get('type') === 'video')->visible(fn (Get $get) => in_array($get('type'), ['lesson', 'video'])),
                        Repeater::make('questions')->minItems(1)->maxItems(100)->collapsible()
                            ->visible(fn (Get $get) => ! in_array($get('type'), ['lesson', 'video']) && $get('assessment_source') !== 'google_forms')
                            ->schema([
                                Textarea::make('prompt')->label('Question')->required()->maxLength(2000),
                                Repeater::make('options')->simple(TextInput::make('option')->required()->maxLength(1000))->minItems(2)->maxItems(5)->defaultItems(3),
                                Select::make('correct')->label('Correct option')->options([0 => 'Option 1', 1 => 'Option 2', 2 => 'Option 3', 3 => 'Option 4', 4 => 'Option 5'])->required()->helperText('Select an option that exists above.'),
                            ]),
                    ]),
            ]);
    }
}
