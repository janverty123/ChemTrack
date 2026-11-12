@props(['title', 'heading', 'intro', 'mode' => 'login', 'step' => null, 'backHref' => '/', 'backLabel' => 'Back to home'])
<x-workspace :title="$title" :public="true" :auth-page="true">
<div class="auth-shell auth-{{ $mode }}">
    <header class="auth-header">
        <a class="auth-back" href="{{ $backHref }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14 6-6 6 6 6M8 12h13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span>{{ $backLabel }}</span></a>
        <x-brand />
    </header>
    <div class="auth-grid">
        <section class="auth-form-panel" aria-labelledby="auth-heading">
            <div class="auth-form-inner">
                @guest
                <nav class="auth-switch" aria-label="Account access"><a href="/login" @if($mode === 'login') aria-current="page" @endif>Sign in</a><a href="/register" @if($mode === 'register') aria-current="page" @endif>Create account</a></nav>
                @endguest
                <h1 id="auth-heading">{{ $heading }}</h1>
                <p class="auth-intro">{{ $intro }}</p>
                @if($step)
                <ol class="auth-steps" aria-label="Account setup progress">
                    @foreach(['Email', 'Verify', 'Details'] as $label)
                    <li @if($loop->iteration === (int) $step) aria-current="step" @endif class="{{ $loop->iteration < (int) $step ? 'is-done' : '' }}"><span aria-hidden="true">{{ $loop->iteration < (int) $step ? '✓' : $loop->iteration }}</span>{{ $label }}</li>
                    @endforeach
                </ol>
                @endif
                {{ $slot }}
            </div>
        </section>
        <aside class="auth-story" aria-label="About ChemTrack">
            <div class="auth-story-body">
                <div class="auth-story-mark" aria-hidden="true"><x-brand-mark /></div>
                <h2>{{ $mode === 'register' ? 'Your learning path starts here.' : 'Chemistry, one clear step at a time.' }}</h2>
                <p>Work through your module at your own pace. Your place, answers and progress are saved as you go.</p>
                <ol class="auth-story-path" aria-label="Each cycle">
                    <li><span>1</span>Pre-test</li>
                    <li><span>2</span>Module</li>
                    <li><span>3</span>Video lesson</li>
                    <li><span>4</span>Post-test</li>
                </ol>
            </div>
            <p class="auth-story-footer">Chemistry remediation &amp; progress tracking</p>
        </aside>
    </div>
</div>
</x-workspace>
