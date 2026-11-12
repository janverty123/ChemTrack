<x-filament-panels::page>
    <div class="teacher-student-summary"><p>{{ $reports->count() }} assigned {{ Str::plural('programme', $reports->count()) }} <span aria-hidden="true">·</span> Joined {{ $student->created_at->timezone('Asia/Manila')->format('M j, Y') }}</p><a class="teacher-small-button" href="{{ \App\Filament\Resources\Programmes\ProgrammeResource::getUrl() }}">Manage enrolment</a></div>
    @forelse($reports as $report)
        @php
            $programme = $report['programme'];
            $visibleRows = collect($report['rows'])->filter(fn ($row) => (int) $row['cycle'] === $this->cycle);
        @endphp
        <section class="teacher-panel teacher-progress-layout" aria-labelledby="student-programme-{{ $programme->id }}">
            <div class="teacher-progress-overview">
                <h2 id="student-programme-{{ $programme->id }}">{{ $programme->title }}</h2>
                <x-progress-ring :percent="$report['summary']['percent']" :done="$report['summary']['done']" :total="4" :cycle="$this->cycle">
                    <nav class="cycle-switch" aria-label="Select cycle for {{ $programme->title }}">
                        @foreach([1, 2] as $cycle)<button type="button" wire:click="selectCycle({{ $cycle }})" wire:loading.attr="disabled" wire:target="selectCycle" @if($cycle === $this->cycle) aria-current="true" @endif>Cycle {{ $cycle }}</button>@endforeach
                    </nav>
                </x-progress-ring>
                <a class="teacher-text-link" href="/teacher/programmes/{{ $programme->id }}/results">Programme report <span aria-hidden="true">→</span></a>
            </div>
            <div class="teacher-activity-list">
                <div class="cycle-stages" aria-label="Required stages">@foreach($report['summary']['stages'] as $stage)<span class="teacher-state">{{ $stage['label'] }} · {{ $stage['complete'] ? 'Complete' : ($stage['configured'] ? 'To do' : 'Not set up') }}</span>@endforeach</div>
                <header class="teacher-panel-heading"><h3>Cycle {{ $this->cycle }} activities</h3><span class="teacher-state">{{ $visibleRows->count() }} {{ Str::plural('activity', $visibleRows->count()) }}</span></header>
                @forelse($visibleRows as $row)
                    <article class="teacher-activity-row">
                        <div class="task-ring" role="img" aria-label="{{ $row['progress'] }} percent {{ $row['status'] === 'complete' ? 'complete' : ($row['type'] === 'lesson' ? 'viewed' : 'answered') }}"><svg viewBox="0 0 48 48" aria-hidden="true"><circle class="ring-track" cx="24" cy="24" r="20"/><circle class="ring-fill" cx="24" cy="24" r="20" pathLength="100" stroke-dasharray="{{ $row['progress'] }} 100"/></svg><span aria-hidden="true">@if($row['status'] === 'complete')<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>@else{{ $row['progress'] }}%@endif</span></div>
                        <div class="teacher-activity-content"><h4>{{ $row['activity'] }}</h4><span class="teacher-activity-kind">{{ ucfirst(str_replace('_', '-', $row['type'])) }}@if($row['cycle']) · Cycle {{ $row['cycle'] }}@endif</span><span class="teacher-state {{ $row['status'] === 'complete' ? 'teacher-state-complete' : '' }}">{{ ucfirst($row['status']) }}</span>
                            <dl class="teacher-activity-details"><div><dt>Reading estimate</dt><dd>{{ $row['reading'] !== null ? $row['reading'].'% viewed' : 'Not recorded' }}</dd></div><div><dt>Score</dt><dd>{{ $row['score'] !== null ? $row['score'].'%' : 'Not recorded' }}</dd></div>@if($row['completed_at'])<div><dt>Completed</dt><dd>{{ $row['completed_at']->timezone('Asia/Manila')->format('M j, g:i a') }}</dd></div>@endif</dl>
                        </div>
                    </article>
                @empty
                    <div class="teacher-empty"><p>No activities in this cycle yet.</p></div>
                @endforelse
            </div>
        </section>
    @empty
        <section class="teacher-panel teacher-empty"><h2>No programme assigned yet.</h2><p>Enrol this student in a programme to get started.</p><a class="teacher-small-button teacher-small-button-primary" href="{{ \App\Filament\Resources\Programmes\ProgrammeResource::getUrl() }}">Open programmes</a></section>
    @endforelse
</x-filament-panels::page>
