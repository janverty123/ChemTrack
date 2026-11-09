<x-filament-panels::page>
    <dl class="teacher-summary" aria-label="Your teaching workspace at a glance">
        <div><dt>Your students</dt><dd>{{ $studentCount }}</dd></div>
        <div><dt>Programmes</dt><dd>{{ $programmeCount }}</dd></div>
        <div><dt>Published programmes</dt><dd>{{ $publishedCount }}</dd></div>
    </dl>
    <div class="teacher-workgrid">
        <section class="teacher-panel" aria-labelledby="teacher-programmes-title">
            <header class="teacher-panel-heading"><div><h2 id="teacher-programmes-title">Your programmes</h2></div><a class="teacher-text-link" href="{{ \App\Filament\Resources\Programmes\ProgrammeResource::getUrl() }}">View all programmes <span aria-hidden="true">→</span></a></header>
            <div class="teacher-programme-list">
                @forelse($programmes as $programme)
                    <article class="teacher-programme-row">
                        @php($cycleSetup = $readiness[$programme->id])
                        <div><span class="teacher-state {{ $programme->published ? 'teacher-state-complete' : '' }}">{{ $programme->published ? 'Published' : 'Draft' }}</span><h3><a href="{{ \App\Filament\Resources\Programmes\ProgrammeResource::getUrl('view', ['record' => $programme]) }}">{{ $programme->title }}</a></h3><p>{{ $programme->students_count }} {{ Str::plural('student', $programme->students_count) }} <span aria-hidden="true">·</span> {{ $programme->activities_count }} {{ Str::plural('activity', $programme->activities_count) }}</p></div>
                        <div class="teacher-row-actions"><a class="teacher-small-button" href="{{ \App\Filament\Resources\Programmes\ProgrammeResource::getUrl('view', ['record' => $programme]) }}" aria-label="Open content for {{ $programme->title }}">Content</a><a class="teacher-small-button teacher-small-button-primary" href="/teacher/programmes/{{ $programme->id }}/results" aria-label="View student progress for {{ $programme->title }}">Student progress</a></div>
                        <div class="teacher-cycle-release"><p>{{ $cycleSetup['eligible'] }} ready for Cycle 2 · {{ $cycleSetup['waiting'] }} still completing Cycle 1</p>@if($cycleSetup['released'])<span class="teacher-state teacher-state-complete">Cycle 2 open</span>@else<form method="post" action="/teacher/programmes/{{ $programme->id }}/open-cycle-two">@csrf<button class="teacher-small-button" @disabled(!$cycleSetup['ready'])>Open Cycle 2</button></form>@if(!$cycleSetup['ready'])<p>Configure all four stages in both cycles before opening.</p>@endif @endif</div>
                    </article>
                @empty
                    <div class="teacher-empty"><h3>Build your first learning path.</h3><p>Create a programme, add the module parts and enrol your students when it is ready.</p><a class="teacher-small-button teacher-small-button-primary" href="{{ \App\Filament\Resources\Programmes\ProgrammeResource::getUrl('create') }}">Create a programme</a></div>
                @endforelse
            </div>
        </section>
        <section class="teacher-panel" aria-labelledby="teacher-students-title">
            <header class="teacher-panel-heading"><div><h2 id="teacher-students-title">Students</h2></div><a class="teacher-text-link" href="{{ \App\Filament\Resources\Users\UserResource::getUrl() }}">View all students <span aria-hidden="true">→</span></a></header>
            <div class="teacher-student-list">
                @forelse($students as $student)
                    <a class="teacher-student-row" href="{{ \App\Filament\Resources\Users\UserResource::getUrl('view', ['record' => $student]) }}"><span class="teacher-student-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M5 21v-2a7 7 0 0 1 14 0v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span><strong>{{ $student->studentReference() }}</strong><small>{{ $student->programmes_count }} {{ Str::plural('programme', $student->programmes_count) }}</small></span><span aria-hidden="true">→</span></a>
                @empty
                    <div class="teacher-empty"><h3>Your roster will appear here.</h3><p>Add a coded student account or create an invitation.</p></div>
                @endforelse
            </div>
            <div class="teacher-panel-footer"><a class="teacher-small-button" href="/teacher/student-invitations">Invite students</a></div>
        </section>
    </div>
</x-filament-panels::page>
