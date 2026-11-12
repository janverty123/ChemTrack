@php
    $dashboardUrl = auth()->check() ? (auth()->user()->role === 'teacher' ? '/teacher' : '/learn') : '/login';
    $actionLabel = auth()->check() ? 'Open dashboard' : 'Sign in';
    $check = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" aria-hidden="true"><path d="m5 12 4.5 4.5L19 7" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <x-head-meta description="ChemTrack is a self-paced chemistry remediation space: one module, two cycles, saved progress for students and clear reports for teachers." />
    <title>ChemTrack — Chemistry, one clear step at a time</title>
    <meta property="og:title" content="ChemTrack — Chemistry, one clear step at a time">
    <meta property="og:description" content="Self-paced chemistry remediation with saved progress for students and clear reports for teachers.">
    <meta property="og:type" content="website">
    <x-appearance :public="true" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page" data-landing>
<a class="landing-skip" href="#main">Skip to content</a>

<header class="landing-header">
    <div class="landing-frame landing-header-inner">
        <x-brand class="landing-brand" />
        <nav class="landing-nav" aria-label="Page sections">
            <a href="#how">How it works</a>
            <a href="#checkpoints">Checkpoints</a>
            <a href="#teachers">For teachers</a>
        </nav>
        <a class="button landing-header-action" href="{{ $dashboardUrl }}">{{ $actionLabel }}</a>
    </div>
</header>

<main id="main">
    <section class="landing-hero" aria-labelledby="landing-title">
        <div class="landing-frame landing-hero-grid">
            <div class="landing-hero-copy">
                <p class="landing-kicker"><span aria-hidden="true"></span>Chemistry remediation for learning at home</p>
                <h1 id="landing-title">Close the gaps in chemistry, <mark>one clear step</mark> at a time.</h1>
                <p class="landing-lead">ChemTrack guides students through their module at their own pace, saves every answer and page as they go, and gives teachers an honest picture of progress.</p>
                <div class="landing-actions">
                    <a class="button landing-cta" href="{{ $dashboardUrl }}">{{ $actionLabel }}<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    <a class="secondary-button" href="#how">See how it works</a>
                </div>
                @guest<p class="landing-invite">Have an invitation from your teacher? <a href="/register">Create your student account</a></p>@endguest
            </div>

            <figure class="landing-product" aria-label="Example learning dashboard">
                <div class="landing-product-card">
                    <div class="landing-product-top"><span>Cycle 1 · Your learning path</span><span class="landing-dot-row" aria-hidden="true"><i></i><i></i><i></i></span></div>
                    <div class="landing-product-body">
                        <div class="landing-ring" role="img" aria-label="Example: Cycle 1 is 50 percent complete, 2 of 4 stages">
                            <svg viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="50"/><circle class="landing-ring-fill" cx="60" cy="60" r="50" pathLength="100"/></svg>
                            <div><strong>50<small>%</small></strong><span>2 of 4 stages</span></div>
                        </div>
                        <ol class="landing-stages">
                            <li class="is-done"><span>{!! $check !!}</span><div><strong>Pre-test</strong><small>Submitted</small></div></li>
                            <li class="is-done"><span>{!! $check !!}</span><div><strong>Module</strong><small>All parts complete</small></div></li>
                            <li class="is-now"><span>3</span><div><strong>Video lesson</strong><small>Up next</small></div><em>Continue</em></li>
                            <li><span>4</span><div><strong>Post-test</strong><small>Opens after the video</small></div></li>
                        </ol>
                    </div>
                </div>
                <div class="landing-toast" aria-hidden="true"><span>{!! $check !!}</span>Answer saved · 10:42</div>
                <figcaption class="landing-sr-only">Illustrative example only. Your teacher sets the actual module and cycle activities.</figcaption>
            </figure>
        </div>
    </section>

    <section id="how" class="landing-section landing-frame" aria-labelledby="how-title">
        <div class="landing-section-head">
            <p class="eyebrow">How it works</p>
            <h2 id="how-title" data-landing-reveal>One module. Two cycles.<br>Four clear stages.</h2>
            <p data-landing-reveal>Every cycle follows the same path, so students always know what comes next. Cycle 2 opens when Cycle 1 is complete and the teacher releases it.</p>
        </div>
        <ol class="landing-steps">
            <li data-landing-reveal><span class="landing-step-no">01</span><h3>Pre-test</h3><p>A quick check of what the student already knows before the module begins.</p></li>
            <li data-landing-reveal><span class="landing-step-no">02</span><h3>Module</h3><p>Read each part in the built-in reader. Your place is saved, and checkpoints unlock the next part.</p></li>
            <li data-landing-reveal><span class="landing-step-no">03</span><h3>Video lesson</h3><p>Watch the lesson, then confirm when you have finished it.</p></li>
            <li data-landing-reveal><span class="landing-step-no">04</span><h3>Post-test</h3><p>Show what you have learned. Submitted answers and scores are kept on record.</p></li>
        </ol>
    </section>

    <section id="checkpoints" class="landing-dark" aria-labelledby="checkpoint-title">
        <div class="landing-frame landing-checkpoint-grid">
            <div class="landing-checkpoint-copy">
                <p class="eyebrow">Checkpoints</p>
                <h2 id="checkpoint-title" data-landing-reveal>Answers save as you go. Submit when you're ready.</h2>
                <p data-landing-reveal>Slow connection? ChemTrack shows exactly what has been saved, retries safely and never submits twice. Submitted answers are final and stay on record.</p>
                <ul class="landing-ticks">
                    <li>{!! $check !!}Clear saving, saved and retry states</li>
                    <li>{!! $check !!}Resume where you stopped on any device</li>
                    <li>{!! $check !!}Scored on the server, not in the browser</li>
                </ul>
            </div>
            <figure class="landing-demo" data-checkpoint-demo>
                <div class="landing-demo-heading"><p class="landing-demo-note">Interactive preview</p><button type="button" class="landing-replay" data-demo-play hidden>Play example <span aria-hidden="true">↻</span></button></div>
                <fieldset data-demo-options>
                    <legend><span>Question 1</span>What does H<sub>2</sub>O contain?</legend>
                    <label class="landing-demo-answer"><input type="radio" name="demo-answer" value="0"><span>Two hydrogen atoms and one oxygen atom</span></label>
                    <label class="landing-demo-answer"><input type="radio" name="demo-answer" value="1"><span>One hydrogen atom and two oxygen atoms</span></label>
                </fieldset>
                <div class="landing-demo-controls"><span class="landing-demo-status" data-demo-status role="status">Choose an answer</span><button type="button" class="button" data-demo-submit disabled>Submit answer</button></div>
                <noscript><p class="landing-demo-note">Enable JavaScript to try this interactive example.</p></noscript>
                <div class="landing-receipt" data-demo-receipt hidden><span>{!! $check !!}</span><div><strong>Submission confirmed</strong><p>The next activity is ready.</p></div></div>
                <figcaption class="landing-sr-only">Try the preview. Answers are not saved to any account.</figcaption>
            </figure>
        </div>
    </section>

    <section id="teachers" class="landing-section landing-frame" aria-labelledby="teachers-title">
        <div class="landing-section-head">
            <p class="eyebrow">Built for both sides</p>
            <h2 id="teachers-title" data-landing-reveal>A learning space for students.<br>A clear record for teachers.</h2>
        </div>
        <div class="landing-audiences">
            <article class="landing-audience" data-landing-reveal>
                <span class="landing-audience-tag">Students</span>
                <h3>Know where you are and what's next</h3>
                <ul class="landing-ticks">
                    <li>{!! $check !!}One progress ring per cycle, plus a ring for every task</li>
                    <li>{!! $check !!}A reader that remembers your page</li>
                    <li>{!! $check !!}Locked steps explain what to finish first</li>
                    <li>{!! $check !!}Light, dark and larger-text options</li>
                </ul>
            </article>
            <article class="landing-audience landing-audience-teacher" data-landing-reveal>
                <span class="landing-audience-tag">Teachers</span>
                <h3>See progress without chasing it</h3>
                <ul class="landing-ticks">
                    <li>{!! $check !!}Programmes, activities and student invitations in one place</li>
                    <li>{!! $check !!}Reading, completion and scores kept separate</li>
                    <li>{!! $check !!}Release Cycle 2 when your class is ready</li>
                    <li>{!! $check !!}Export a spreadsheet-safe CSV report</li>
                </ul>
            </article>
        </div>
        <div class="landing-privacy" data-landing-reveal>
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" aria-hidden="true"><path d="M12 3 5 6v5c0 4.5 3 8.4 7 10 4-1.6 7-5.5 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <p><strong>Private by design.</strong> Students appear as codes such as ST1-G in learning views, reports and exports. Names and LRNs stay encrypted in the student's own account.</p>
        </div>
    </section>

    <section class="landing-frame landing-final" aria-labelledby="start-title">
        <div class="landing-final-card">
            <x-brand-mark />
            <h2 id="start-title">Ready to pick up where you left off?</h2>
            <p>Sign in with your student code or teacher email to see your own assigned work.</p>
            <div class="landing-actions"><a class="button landing-cta" href="{{ $dashboardUrl }}">{{ $actionLabel }}</a>@guest<a class="secondary-button" href="/register">Create student account</a>@endguest</div>
        </div>
    </section>
</main>

<footer class="landing-footer">
    <div class="landing-frame landing-footer-inner">
        <div><x-brand class="landing-footer-brand" /><p>Chemistry, one clear step at a time.</p></div>
        <nav aria-label="Footer navigation"><a href="#how">How it works</a><a href="#checkpoints">Checkpoints</a><a href="#teachers">For teachers</a><a href="{{ $dashboardUrl }}">{{ $actionLabel }}</a></nav>
    </div>
    <div class="landing-frame landing-footnote"><p>© {{ now()->year }} ChemTrack · Self-paced chemistry remediation</p></div>
</footer>
</body>
</html>
