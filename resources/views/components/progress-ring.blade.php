@props(['percent', 'done', 'total', 'cycle' => null])
<div class="overall-ring">
    <svg viewBox="0 0 220 220" role="img" aria-label="{{ $cycle ? 'Cycle '.$cycle.' completion' : 'Overall completion across both cycles' }}: {{ $percent }} percent, {{ $done }} of {{ $total }} {{ $cycle ? 'stages' : 'activities' }} completed"><circle class="ring-track" cx="110" cy="110" r="101"/><circle class="ring-fill" cx="110" cy="110" r="101" pathLength="100" stroke-dasharray="{{ $percent }} 100"/></svg>
    <div class="overall-ring-copy"><span class="overall-ring-label"><span class="overall-label-full">{{ $cycle ? 'Cycle '.$cycle.' completion' : 'Overall completion' }}</span><span class="overall-label-short">{{ $cycle ? 'Cycle '.$cycle : 'Overall' }}</span></span><strong>{{ $percent }}<small>%</small></strong><span class="overall-ring-count">{{ $done }} of {{ $total }} completed</span>{{ $slot }}</div>
</div>
<p class="overall-progress-count">{{ $done }} of {{ $total }} {{ $cycle ? 'stages' : 'activities' }} completed</p>
