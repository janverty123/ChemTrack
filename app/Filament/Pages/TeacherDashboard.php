<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Programmes\ProgrammeResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Programme;
use App\Models\User;
use App\Services\CycleProgress;
use App\Services\ProgressReport;
use Filament\Actions\Action;
use Filament\Pages\Dashboard;

class TeacherDashboard extends Dashboard
{
    protected static ?string $title = 'Teaching workspace';

    protected static ?string $navigationLabel = 'Overview';

    protected string $view = 'filament.pages.teacher-dashboard';

    public function getSubheading(): string
    {
        return 'Programmes and student progress';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newProgramme')->label('New programme')->url(ProgrammeResource::getUrl('create')),
            Action::make('addStudent')->label('Add student')->color('gray')->url(UserResource::getUrl('create')),
        ];
    }

    protected function getViewData(): array
    {
        $teacher = auth()->user();
        abort_unless($teacher?->role === 'teacher', 403);
        $programmes = Programme::where('teacher_id', $teacher->id);
        $students = User::where('teacher_id', $teacher->id)->where('role', 'student');
        $visibleProgrammes = $programmes->withCount(['activities', 'students' => fn ($query) => $query->where('teacher_id', $teacher->id)->where('role', 'student')])->latest('id')->limit(6)->get();
        $readiness = [];
        $progress = app(CycleProgress::class);
        foreach ($visibleProgrammes as $programme) {
            $report = app(ProgressReport::class)->forProgramme($teacher, $programme);
            $eligible = collect($report['cycles'])->filter(fn ($cycles) => $cycles[1]['done'] === 4)->count();
            $readiness[$programme->id] = ['ready' => $programme->published && $progress->summary($programme, 1, [])['ready'] && $progress->summary($programme, 2, [])['ready'], 'released' => $progress->released($programme), 'eligible' => $eligible, 'waiting' => $programme->students_count - $eligible];
        }

        return [
            'programmeCount' => (clone $programmes)->count(),
            'publishedCount' => (clone $programmes)->where('published', true)->count(),
            'studentCount' => (clone $students)->count(),
            'programmes' => $visibleProgrammes,
            'readiness' => $readiness,
            'students' => $students->withCount(['programmes' => fn ($query) => $query->where('teacher_id', $teacher->id)])->latest('id')->limit(6)->get(),
        ];
    }
}
