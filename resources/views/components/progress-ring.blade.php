@props(['percent', 'done', 'total', 'cycle'])
<div class="overall-ring">
    <svg viewBox="0 0 220 220" role="img" aria-label="Cycle {{ $cycle }} completion: {{ $percent }} percent, {{ $done }} of {{ $total }} stages completed"><circle class="ring-track" cx="110" cy="110" r="101"/><circle class="ring-fill" cx="110" cy="110" r="101" pathLength="100" stroke-dasharray="{{ $percent }} 100"/></svg>
    <div class="overall-ring-copy"><span class="overall-ring-label"><span class="overall-label-full">Cycle {{ $cycle }} completion</span><span class="overall-label-short">Cycle {{ $cycle }}</span></span><strong>{{ $percent }}<small>%</small></strong><span class="overall-ring-count">{{ $done }} of {{ $total }} stages</span>{{ $slot }}</div>
</div>
<p class="overall-progress-count">{{ $done }} of {{ $total }} stages completed</p>
