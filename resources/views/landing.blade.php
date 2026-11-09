@php
    $dashboardUrl = auth()->check() ? (auth()->user()->role === 'teacher' ? '/teacher' : '/learn') : '/login';
    $actionLabel = auth()->check() ? 'Open dashboard' : 'Sign in';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="ChemTrack brings chemistry reading, checkpoints and saved learning progress together in one place.">
    <meta name="theme-color" content="#F7FAF8">
    <title>ChemTrack — Chemistry at your pace</title>
    <x-appearance :public="true" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page" data-landing>
    <a class="landing-skip" href="#main">Skip to content</a>
    <svg class="landing-symbols" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
            <symbol id="landing-water" viewBox="0 0 480 340">
                <path d="M139 234 240 125 341 234" fill="none" stroke="currentColor" stroke-width="22" stroke-linecap="round"/>
                <circle cx="240" cy="125" r="76" fill="#115E59"/>
                <circle cx="125" cy="248" r="53" fill="#F7FAF8"/>
                <circle cx="355" cy="248" r="53" fill="#F7FAF8"/>
                <text x="240" y="150" fill="#FFFFFF" text-anchor="middle" font-size="70" font-family="Manrope, sans-serif" font-weight="600">O</text>
                <text x="125" y="266" fill="#173C35" text-anchor="middle" font-size="50" font-family="Manrope, sans-serif" font-weight="600">H</text>
                <text x="355" y="266" fill="#173C35" text-anchor="middle" font-size="50" font-family="Manrope, sans-serif" font-weight="600">H</text>
            </symbol>
            <symbol id="landing-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        </defs>
    </svg>

    <header class="landing-header landing-frame">
        <x-brand class="landing-brand" />
    </header>

    <button class="landing-menu-button" type="button" data-landing-menu aria-haspopup="dialog" aria-controls="landing-menu" aria-expanded="false" hidden><span class="landing-menu-word"><span>Menu</span><span aria-hidden="true">Explore</span></span><svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M2 6h14M2 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
    <dialog id="landing-menu" class="landing-menu" aria-labelledby="landing-menu-title">
        <div class="landing-menu-top"><h2 id="landing-menu-title">ChemTrack</h2><button type="button" data-landing-close aria-label="Close menu"><span>Close</span><svg viewBox="0 0 20 20" width="18" height="18" aria-hidden="true"><path d="m4 4 12 12M16 4 4 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div>
        <nav aria-label="Page sections"><a href="#landing-title"><span>Back to the beginning</span></a><a href="#module"><span>Inside the module</span></a><a href="#checkpoints"><span>Checkpoints</span></a><a href="#progress"><span>Your progress</span></a><a class="landing-menu-action" href="{{ $dashboardUrl }}"><span>{{ $actionLabel }}</span><span aria-hidden="true">↗</span></a></nav>
    </dialog>

    <main id="main" class="landing-main">
        <section class="landing-hero" aria-labelledby="landing-title">
            <div class="landing-frame">
            <div class="landing-module-sheet">
                <div class="landing-hero-copy">
                    <h1 id="landing-title">Chemistry,<br>at your pace.</h1>
                    <p>ChemTrack keeps your module, checkpoints and saved progress together as you work through chemistry at home.</p>
                    <a class="landing-action" href="{{ $dashboardUrl }}">{{ $actionLabel }}<svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true"><path d="M3 10h13m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8"/></svg></a><a class="landing-text-link" href="#module">Take a look inside <span aria-hidden="true">↓</span></a>
                </div>
                <figure class="landing-hero-product" data-ui aria-label="Chemistry module preview">
                    <div class="landing-product-bar"><span class="landing-product-dot" aria-hidden="true"></span><span>Your learning space</span><span>Cycle 1</span></div>
                    <div class="landing-hero-model"><div><h2>Inside matter</h2><p>One module. One next step.</p></div><svg class="landing-molecule" viewBox="0 0 480 340" role="img" aria-label="Water molecule: one oxygen atom and two hydrogen atoms"><use href="#landing-water"/></svg></div>
                    <div class="landing-hero-path"><div class="landing-mini-task"><svg width="25" height="25" aria-hidden="true"><use href="#landing-check"/></svg><span>Molecules and bonds<small>Reading complete</small></span><span class="landing-mini-status">Saved</span></div><div class="landing-mini-task landing-mini-current"><span class="landing-small-ring" aria-hidden="true"></span><span>Check your understanding<small>Your next checkpoint</small></span><span aria-hidden="true">→</span></div></div>
                    <figcaption class="landing-sr-only">Illustrative material and activity order. Your teacher supplies the actual module and cycle assignments.</figcaption>
                </figure>
            </div>
            <p class="landing-hero-foot">A learning space for students. A record of progress for teachers.</p>
            </div>
        </section>

        <section id="module" class="landing-reading landing-frame" aria-labelledby="module-title">
            <div class="landing-section-intro"><h2 id="module-title" data-landing-reveal>Stay with the module</h2><p data-landing-reveal>Read each part, pick up where you left off, and move forward when you’re ready.</p></div>
            <div class="landing-module-story" data-reading-scene>
                <div class="landing-story-sticky">
                    <div class="landing-reading-notes"><p class="landing-story-instruction">Scroll to follow a sample lesson <span aria-hidden="true">↓</span></p><ol class="landing-story-steps"><li data-story-step="reading"><h3>Read at your pace</h3><p>The page moves. Your place stays saved.</p></li><li data-story-step="complete"><h3>Finish this part</h3><p>Mark the reading complete when you are ready.</p></li><li data-story-step="checkpoint"><h3>Try the checkpoint</h3><p>Your next activity opens after confirmed completion.</p></li></ol></div>
                    <figure class="landing-reader" data-ui>
                        <div class="landing-reader-toolbar"><span>Chemistry module</span><span data-story-label>Reading preview</span></div>
                        <div class="landing-reader-viewport" data-story-viewport>
                            <div class="landing-reader-page" data-story-document><h3>Molecules and bonds</h3><p>Look at a glass of water. Every drop contains countless molecules, each made from the same combination of atoms.</p><svg viewBox="0 0 480 340" role="img" aria-label="Water molecule illustration"><use href="#landing-water"/></svg><h4>A small structure, repeated</h4><p>Two hydrogen atoms bond to one oxygen atom to make a water molecule. A sample of water contains many of these molecules moving among one another.</p><p>The formula H<sub>2</sub>O describes one molecule. The small 2 belongs to hydrogen: it tells us that two hydrogen atoms are present. Oxygen has no written subscript because there is one oxygen atom.</p><div class="landing-formula"><span>2 hydrogen atoms</span><strong>H<sub>2</sub>O</strong><span>1 oxygen atom</span></div><h4>Count atoms, not letters</h4><p>A chemical formula gives both the elements and their proportions. The letters identify the elements; the subscripts tell you how many atoms of each are present.</p><p>One water molecule therefore contains three atoms in total. Its two elements are hydrogen and oxygen. The number of elements and the number of atoms describe different things.</p><h4>Bring the idea together</h4><p>Before the next activity, use the formula to identify each element and count its atoms. In H<sub>2</sub>O, that is two hydrogen atoms and one oxygen atom.</p><div class="landing-reading-finish"><span data-story-completion>End of the sample reading</span><span class="landing-illustrated-action" data-story-action>Mark reading complete <svg width="18" height="18" aria-hidden="true"><use href="#landing-check"/></svg></span></div></div>
                            <div class="landing-reader-next" data-story-next><span class="landing-next-check"><svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true"><use href="#landing-check"/></svg></span><h3>Ready for a checkpoint?</h3><p>What does the 2 in H<sub>2</sub>O tell you?</p><div class="landing-preview-option">The number of hydrogen atoms</div><div class="landing-preview-option">The number of oxygen atoms</div><a href="#checkpoints" class="landing-text-link">Try the sample below <span aria-hidden="true">↓</span></a></div>
                        </div>
                        <div class="landing-reader-bottom"><div class="landing-reader-oval"><svg viewBox="0 0 260 64" aria-hidden="true"><rect x="3" y="3" width="254" height="58" rx="29" pathLength="100"/><rect class="landing-oval-fill" x="3" y="3" width="254" height="58" rx="29" pathLength="100"/></svg><span data-story-tracker>Reading preview</span></div><span class="landing-reader-save" data-story-save>Lesson preview</span></div>
                        <figcaption class="landing-sr-only">Interactive lesson preview. No learning records are changed.</figcaption>
                    </figure>
                </div>
            </div>
        </section>

        <section id="checkpoints" class="landing-checkpoints" aria-labelledby="checkpoint-title">
            <div class="landing-frame landing-checkpoint-layout">
                <div class="landing-checkpoint-copy"><h2 id="checkpoint-title" data-landing-reveal>A checkpoint<br>you can finish</h2><p data-landing-reveal>Save an answer while you work. Submit when you are ready. ChemTrack keeps the submitted answers and records the result.</p></div>
                <figure class="landing-answer-sheet" data-ui data-checkpoint-demo>
                    <div class="landing-demo-heading"><p class="landing-demo-note">Interactive preview</p><button type="button" class="landing-replay" data-demo-play hidden>Play example <span aria-hidden="true">↻</span></button></div>
                    <fieldset data-demo-options><legend>What does H<sub>2</sub>O contain?</legend><label class="landing-demo-answer"><input type="radio" name="demo-answer" value="0"><span>Two hydrogen atoms and one oxygen atom</span></label><label class="landing-demo-answer"><input type="radio" name="demo-answer" value="1"><span>One hydrogen atom and two oxygen atoms</span></label></fieldset>
                    <div class="landing-demo-controls"><span data-demo-status role="status">Choose an answer</span><button type="button" class="landing-action" data-demo-submit disabled>Submit answer</button></div><noscript><p class="landing-demo-note">Enable JavaScript to try this interactive example.</p></noscript>
                    <div class="landing-answer-receipt" data-demo-receipt hidden><svg width="28" height="28" aria-hidden="true"><use href="#landing-check"/></svg><div><strong>Submission confirmed</strong><p>The next activity is ready.</p></div></div><figcaption class="landing-sr-only">Try the preview. Answers are not saved to your account.</figcaption>
                </figure>
            </div>
        </section>

        <section id="progress" class="landing-progress landing-frame" aria-labelledby="progress-title">
            <div class="landing-section-intro"><h2 id="progress-title" data-landing-reveal>See the whole picture</h2><p data-landing-reveal>See your overall progress and the next step in each cycle.</p></div>
            <div class="landing-progress-preview" data-ui data-landing-reveal>
                <div class="landing-overall"><div class="landing-overall-ring" role="img" aria-label="Synthetic example: 50 percent overall, 2 of 4 required tasks complete across both cycles"><svg viewBox="0 0 280 280" aria-hidden="true"><circle cx="140" cy="140" r="124"/><circle class="landing-overall-fill" cx="140" cy="140" r="124" pathLength="100"/></svg><div><strong>50<span>%</span></strong><span>Overall completion</span><small>2 of 4 required tasks</small></div></div><p>Across Cycle 1 and Cycle 2</p></div>
                <div class="landing-cycle-preview"><p class="landing-demo-note">Progress preview</p><fieldset class="landing-cycle-switch"><legend>Preview a cycle</legend><div><input id="landing-cycle-one" type="radio" name="landing-cycle" value="1" checked aria-controls="landing-cycle-one-tasks"><label for="landing-cycle-one">Cycle 1</label><input id="landing-cycle-two" type="radio" name="landing-cycle" value="2" aria-controls="landing-cycle-two-tasks"><label for="landing-cycle-two">Cycle 2</label></div></fieldset>
                    <div id="landing-cycle-one-tasks" class="landing-cycle-tasks" data-cycle="1"><h3>Cycle 1 tasks</h3><p>2 of 2 complete</p><div class="landing-task"><span class="landing-task-ring landing-task-complete" aria-hidden="true"><svg viewBox="0 0 24 24"><use href="#landing-check"/></svg></span><div><strong>Module reading · first part</strong><span>Complete</span></div></div><div class="landing-task"><span class="landing-task-ring landing-task-complete" aria-hidden="true"><svg viewBox="0 0 24 24"><use href="#landing-check"/></svg></span><div><strong>First checkpoint</strong><span>Submission confirmed</span></div></div></div>
                    <div id="landing-cycle-two-tasks" class="landing-cycle-tasks" data-cycle="2"><h3>Cycle 2 tasks</h3><p>0 of 2 complete</p><div class="landing-task"><svg class="landing-task-progress" viewBox="0 0 40 40" role="img" aria-label="40 percent viewed"><circle cx="20" cy="20" r="16"/><circle class="landing-task-progress-fill" cx="20" cy="20" r="16" pathLength="100"/></svg><div><strong>Module reading · next part</strong><span>40% viewed · in progress</span></div></div><div class="landing-task"><span class="landing-task-ring" aria-hidden="true"></span><div><strong>Next checkpoint</strong><span>Locked until the preceding part is complete</span></div></div></div>
                </div>
            </div>
            
        </section>

        <section class="landing-start" aria-labelledby="start-title"><div class="landing-frame landing-start-layout"><p data-landing-reveal>Your teacher sets the material.<br>You bring the next step.</p><div class="landing-start-sheet" data-landing-reveal><h2 id="start-title">Return to your learning</h2><p>Open ChemTrack to see your own assigned work and confirmed progress.</p><a class="landing-action" href="{{ $dashboardUrl }}">{{ $actionLabel }}</a></div></div></section>
    </main>
    <footer class="landing-footer landing-frame"><div><x-brand class="landing-footer-brand" /><p>Chemistry at your pace.</p></div><nav aria-label="Footer navigation"><a href="#landing-title">Back to the beginning</a><a href="#module">Inside the module</a><a href="#checkpoints">Checkpoints</a><a href="#progress">Your progress</a><a href="{{ $dashboardUrl }}">{{ $actionLabel }}</a></nav></footer>
</body>
</html>
