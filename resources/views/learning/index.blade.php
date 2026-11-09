<x-workspace title="My learning path">
    <header class="page-header">
        <div><p class="date-line">{{ now()->timezone('Asia/Manila')->format('l, F j') }}</p><h1>Pick up where you left off.</h1><p class="muted">Your chemistry learning path, one activity at a time.</p></div>
        <span class="pill">{{ auth()->user()->studentReference() }}</span>
    </header>
    @forelse($programmes as $programme)
        @php
            $selectedCycle = $cycle;
            $summary = $cycleSummaries[$programme->id][$selectedCycle];
            $done = $summary['done'];
            $total = 4;
            $percent = $summary['percent'];
            $visibleActivities = $programme->activities->filter(fn ($item) => $item->cycle === null || (int) $item->cycle === $selectedCycle);
        @endphp
        <section class="programme programme-progress" aria-labelledby="programme-{{ $programme->id }}">
            <div class="programme-overview">
                <h2 id="programme-{{ $programme->id }}">{{ $programme->title }}</h2>@if($programme->description)<details class="programme-description"><summary>About this module</summary><p>{{ $programme->description }}</p></details>@endif
                <x-progress-ring :percent="$percent" :done="$done" :total="$total" :cycle="$selectedCycle">
                    <nav class="cycle-switch" aria-label="Select cycle for {{ $programme->title }}">
                        @foreach([1, 2] as $choice)<a href="{{ route('learning.index', ['cycle' => $choice]) }}#programme-{{ $programme->id }}" @if($choice === $selectedCycle) aria-current="true" @endif>Cycle {{ $choice }}</a>@endforeach
                    </nav>
                </x-progress-ring>
                @if($selectedCycle === 2 && !app(\App\Services\CycleProgress::class)->released($programme))<p class="help-text">Waiting for your teacher to open Cycle 2.</p>@endif
            </div>
            <div class="programme-tasks">
                <div class="task-list-heading"><h3>Cycle {{ $selectedCycle }} learning path</h3><span>{{ $done }} of 4 stages complete</span></div>
                <div class="cycle-stages" aria-label="Cycle {{ $selectedCycle }} required stages">@foreach($summary['stages'] as $stage)<span class="pill">{{ $stage['label'] }} · {{ $stage['complete'] ? 'Complete' : ($stage['configured'] ? 'Pending' : 'Not available yet') }}</span>@endforeach</div>
                @forelse($visibleActivities as $activity)
                    @php
                        $state = $states[$activity->id];
                        $isDone = $state['complete'];
                        $locked = $state['locked'];
                        $taskPercent = $state['percent'];
                    @endphp
                    <article class="path-step {{ $isDone ? 'is-complete' : ($locked ? 'is-locked' : 'is-current') }}">
                        <div class="task-ring" role="img" aria-label="{{ $taskPercent }} percent {{ $isDone ? 'complete' : 'progress' }}"><svg viewBox="0 0 48 48" aria-hidden="true"><circle class="ring-track" cx="24" cy="24" r="20"/><circle class="ring-fill" cx="24" cy="24" r="20" pathLength="100" stroke-dasharray="{{ $taskPercent }} 100"/></svg><span aria-hidden="true">{{ $isDone ? '✓' : $taskPercent.'%' }}</span></div>
                        <div class="step-content"><span class="activity-kind">{{ str_replace('_', '-', ucfirst($activity->type)) }}</span><h4>{{ $activity->title }}</h4><p>{{ $state['detail'] }}</p></div>
                        @if($isDone || !$locked)<a class="{{ $isDone ? 'secondary-button' : 'button' }}" href="{{ route('learning.activity', $activity) }}">{{ $isDone ? 'Review' : 'Continue' }}<span class="sr-only"> {{ $activity->title }}</span></a>@else<span class="lock-label">Locked</span>@endif
                    </article>
                @empty
                    <div class="cycle-empty"><p>No activities are assigned to Cycle {{ $selectedCycle }} yet.</p></div>
                @endforelse
            </div>
        </section>
    @empty
        <section class="empty-state"><h2>Your learning path will appear here.</h2><p>Ask your teacher to enroll you in a published programme to get started.</p></section>
    @endforelse
</x-workspace>
