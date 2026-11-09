<x-workspace :title="'Results: '.$programme->title"><a class="back-link" href="/teacher/programmes">Back to programmes</a>
<header class="page-header"><div><h1>{{ $programme->title }}</h1><p class="muted">Student progress</p></div><a class="button" href="/teacher/programmes/{{ $programme->id }}/export">Export report</a></header>
<div class="table-panel" tabindex="0" role="region" aria-label="Student progress table"><table><caption>Student progress</caption><thead><tr><th scope="col">Student</th><th scope="col">Cycle 1</th><th scope="col">Cycle 2</th><th scope="col">Assessment results</th></tr></thead><tbody>
@forelse($programme->students as $student)
@php
    $studentAttempts = $attempts->where('user_id', $student->id);
@endphp
<tr><td><a href="{{ \App\Filament\Resources\Users\UserResource::getUrl('view', ['record' => $student]) }}"><strong>{{ $student->studentReference() }}</strong></a></td>@foreach([1,2] as $cycle)
@php($summary = $cycles[$student->id][$cycle])
<td><div class="report-completion"><div class="task-ring" role="img" aria-label="Cycle {{ $cycle }}: {{ $summary['percent'] }} percent complete"><svg viewBox="0 0 48 48" aria-hidden="true"><circle class="ring-track" cx="24" cy="24" r="20"/><circle class="ring-fill" cx="24" cy="24" r="20" pathLength="100" stroke-dasharray="{{ $summary['percent'] }} 100"/></svg><span aria-hidden="true">{{ $summary['percent'] }}%</span></div><span>{{ $summary['done'] }} / 4</span></div></td>
@endforeach
<td>@forelse($studentAttempts as $attempt)<div class="attempt-line">{{ $attempt->activity->title }}: <strong>{{ $attempt->submitted_at ? $attempt->score.'%' : 'In progress' }}</strong></div>@empty<span class="muted">No assessments started</span>@endforelse</td></tr>
@empty<tr><td colspan="4">No enrolled students yet. Use “Enroll students” in the programme list.</td></tr>@endforelse
</tbody></table></div>
<section class="programme report-details"><h2>Activity details</h2><div class="table-panel" tabindex="0" role="region" aria-label="Activity details table"><table><caption class="sr-only">Per-student activity evidence</caption><thead><tr><th scope="col">Student code</th><th scope="col">Activity</th><th scope="col">Reading estimate</th><th scope="col">Status</th><th scope="col">Score</th><th scope="col">Completed</th></tr></thead><tbody>
@forelse($rows as $row)<tr><td>{{ $row['student'] }}</td><td>{{ $row['activity'] }}@if($row['cycle'])<span class="table-sub">Cycle {{ $row['cycle'] }}</span>@endif</td><td>{{ $row['reading'] !== null ? $row['reading'].'% viewed' : 'Not recorded' }}</td><td>{{ ucfirst($row['status']) }}</td><td>{{ $row['score'] !== null ? $row['score'].'%' : 'Not recorded' }}</td><td>@if($row['completed_at']){{ $row['completed_at']->timezone('Asia/Manila')->format('M j, g:i a') }}@else Not completed @endif</td></tr>
@empty<tr><td colspan="6">No assigned activity records yet.</td></tr>@endforelse
</tbody></table></div></section></x-workspace>
