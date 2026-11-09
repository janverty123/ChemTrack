<x-workspace :title="$activity->title">
    <a class="back-link" href="{{ route('learning.index', $activity->cycle ? ['cycle' => $activity->cycle] : []) }}">Back to my learning path</a>
    <header class="page-header"><div><span class="pill">{{ str_replace('_', '-', ucfirst($activity->type)) }}{{ $activity->cycle ? ' · Cycle '.$activity->cycle : '' }}</span><h1>{{ $activity->title }}</h1><p class="muted">{{ $activity->programme->title }}</p></div></header>
    @if($activity->assessment_source === 'google_forms')
        <section class="lesson-panel external-assessment" aria-labelledby="external-heading"><h2 id="external-heading">Google Forms assessment</h2><p>Completion verification is not connected yet.</p>
            @if($activity->google_form_url && str_starts_with($activity->google_form_url, 'https://'))<a class="button" href="{{ $activity->google_form_url }}" target="_blank" rel="noopener noreferrer">Open Google Form <span class="sr-only">in a new tab</span></a><p class="help-text">Use your student code, {{ auth()->user()->studentReference() }}, when requested. Return here after submitting.</p>
            @else<p class="pending-notice">Your teacher has not connected a Google Form yet.</p>@endif
            <p class="help-text">This activity stays pending until your response is verified.</p>
        </section>
    @elseif($activity->type === 'video')
        <section class="lesson-panel"><h2>Video lesson</h2>
            @if($activity->video_url)<p>Open the video, then return here to finish this lesson.</p><a class="button" href="{{ $activity->video_url }}" target="_blank" rel="noopener noreferrer">Open video <span class="sr-only">in a new tab</span></a><form method="post" action="/learn/activities/{{ $activity->id }}/complete" class="stack">@csrf<label class="video-confirm"><input type="checkbox" name="video_acknowledged" value="1" required> I have finished this video lesson.</label><button class="button">Finish video lesson</button></form>@else<p>Your teacher has not added the video yet.</p>@endif
        </section>
    @elseif($activity->type === 'lesson')
        @php
            $reading = $reading?->content_version === $version ? $reading : null;
            $readerState = ['version' => $version, 'position' => (float) ($reading?->position ?? 0), 'furthest_position' => (float) ($reading?->furthest_position ?? 0), 'current_page' => $reading?->current_page, 'page_count' => $reading?->page_count, 'viewed_pages' => $reading?->viewed_pages ?? [], 'saved_at' => $reading?->saved_at?->toIso8601String()];
            $initialProgress = $activity->document_path ? ($reading?->page_count ? (int) floor(count($reading->viewed_pages ?? []) / $reading->page_count * 100) : 0) : (int) ($reading?->furthest_position ?? 0);
        @endphp
        <section class="module-reader" data-reader data-kind="{{ $activity->document_path ? 'pdf' : 'native' }}" data-save-url="/learn/activities/{{ $activity->id }}/reading" @if($activity->document_path) data-document-url="/learn/activities/{{ $activity->id }}/document" @endif>
            <script type="application/json" data-reader-state>{!! json_encode($readerState, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
            @if($activity->document_path)
                <div class="reader-toolbar" aria-label="Document controls"><div class="page-control"><label for="reader-page">Page</label><input id="reader-page" data-page-input type="number" min="1" max="{{ $reading?->page_count ?? 500 }}" value="{{ $reading?->current_page ?? 1 }}" inputmode="numeric"><span data-page-total>of {{ $reading?->page_count ?? '…' }}</span><button class="secondary-button" type="button" data-page-go>Go</button></div><div class="zoom-control"><button class="secondary-button" type="button" data-zoom-out aria-label="Zoom out">−</button><span data-zoom-label>100%</span><button class="secondary-button" type="button" data-zoom-in aria-label="Zoom in">+</button></div><a href="/learn/activities/{{ $activity->id }}/document" target="_blank" rel="noopener noreferrer">Open original PDF<span class="sr-only"> in a new tab</span></a></div>
                <p class="reader-loading" data-load-status role="status">Loading the document reader…</p><button class="secondary-button" type="button" data-document-retry hidden>Retry document</button><div class="pdf-pages" data-pdf-pages aria-label="Module document"></div><noscript><p>Enable JavaScript for the page reader, or <a href="/learn/activities/{{ $activity->id }}/document">open the original PDF</a>. Reading observations cannot be saved without JavaScript.</p></noscript>
            @else
                <article class="lesson-panel" data-native-content><div class="lesson-copy">{{ $activity->content }}</div>@if($activity->video_url && str_starts_with($activity->video_url, 'https://'))<a class="secondary-button" href="{{ $activity->video_url }}" target="_blank" rel="noopener noreferrer">Open lesson video <span class="sr-only">in a new tab</span></a>@endif</article>
                <noscript><p>Enable JavaScript to save and resume reading observations. You can still read and complete this lesson.</p></noscript>
            @endif
            <div class="reader-save-bar"><p id="reading-save-status" data-reading-status role="status" aria-live="polite">{{ $reading?->saved_at ? 'Restoring your saved place…' : 'Your place saves as you read.' }}</p><button class="secondary-button" type="button" data-reading-save>Save reading progress</button></div>
            <p class="help-text reader-help">Mark the lesson complete when you finish reading.</p>
            <form method="post" action="/learn/activities/{{ $activity->id }}/complete" class="activity-footer">@csrf<button class="button">Mark lesson complete</button></form>
            <div class="reader-tracker" aria-label="Reading progress"><svg viewBox="0 0 320 84" preserveAspectRatio="none" aria-hidden="true"><rect class="ring-track" x="3" y="3" width="314" height="78" rx="39"/><rect class="ring-fill" x="3" y="3" width="314" height="78" rx="39" pathLength="100" stroke-dasharray="{{ $initialProgress }} 100" data-oval-fill/></svg><div><strong data-reader-stage>{{ $activity->document_path ? 'Module document' : 'Current lesson' }}</strong><span data-reader-progress>{{ $initialProgress }}% viewed · {{ $reading?->saved_at ? 'saved' : 'not saved yet' }}</span></div></div>
        </section>
    @elseif($attempt->submitted_at)
        <section class="result-panel"><span class="result-check" aria-hidden="true">✓</span><h2>Assessment complete.</h2><div class="result-score">{{ $attempt->score }}<span>%</span></div><p>Submitted {{ $attempt->submitted_at->timezone('Asia/Manila')->format('F j, Y, g:i a') }} (Philippine time).</p><p class="muted">Your answers and result are recorded. This submitted attempt can no longer be changed.</p><a class="button" href="/learn">Continue my learning path</a></section>
    @else
        <div class="assessment-intro"><p>{{ count($questions) }} questions. Select one answer for each question.</p><p id="save-status" role="status" aria-live="polite">{{ $attempt->saved_at ? 'Previously saved answers restored.' : 'Choose an answer to begin.' }}</p></div>
        <form method="post" action="/learn/activities/{{ $activity->id }}/submit" data-assessment data-save-url="/learn/activities/{{ $activity->id }}/save" data-question-count="{{ count($questions) }}">@csrf
            @foreach($questions as $i => $question)<fieldset class="question-panel"><legend><span>Question {{ $i + 1 }}</span>{{ $question['prompt'] }}</legend>
                @foreach($question['options'] as $j => $option)<label class="answer-option"><input type="radio" name="answers[{{ $i }}]" value="{{ $j }}" required @checked((string) old('answers.'.$i, $attempt->answers[$i] ?? '') === (string) $j)><span>{{ $option }}</span></label>@endforeach
            </fieldset>@endforeach
            <div class="activity-footer"><button class="secondary-button" type="submit" formaction="/learn/activities/{{ $activity->id }}/save" formnovalidate data-save-button>Save answers</button><button class="button" type="submit">Submit assessment</button></div><p class="help-text">Submission is final. Check that your answers are saved before leaving.</p>
        </form>
    @endif
</x-workspace>
