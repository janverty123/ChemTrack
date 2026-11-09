@props(['title', 'heading', 'intro', 'mode' => 'login', 'step' => null, 'backHref' => '/', 'backLabel' => 'Back to home'])
<x-workspace :title="$title" :public="true" :auth-page="true">
<div class="auth-shell auth-{{ $mode }}">
    <header class="auth-header">
        <a class="auth-back" href="{{ $backHref }}"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14 6-6 6 6 6M8 12h13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg><span>{{ $backLabel }}</span></a>
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
                    <li @if($loop->iteration === (int) $step) aria-current="step" @endif class="{{ $loop->iteration < (int) $step ? 'is-done' : '' }}"><span aria-hidden="true">{{ $loop->iteration }}</span>{{ $label }}</li>
                    @endforeach
                </ol>
                @endif
                {{ $slot }}
            </div>
        </section>
        <aside class="auth-story" aria-label="About ChemTrack">
            <div class="auth-story-body">
                <div class="auth-atom" aria-hidden="true"><svg viewBox="0 0 240 180" fill="none"><ellipse cx="120" cy="90" rx="104" ry="35" stroke="currentColor"/><ellipse cx="120" cy="90" rx="104" ry="35" stroke="currentColor" transform="rotate(60 120 90)"/><ellipse cx="120" cy="90" rx="104" ry="35" stroke="currentColor" transform="rotate(120 120 90)"/><circle cx="120" cy="90" r="10" fill="currentColor"/><circle cx="221" cy="82" r="6" fill="currentColor"/></svg></div>
                <h2>{{ $mode === 'register' ? 'Your next step starts here.' : 'A clearer path through chemistry.' }}</h2>
                <p>Learn at your own pace, check your understanding, and see how far you’ve come.</p>
                <div class="auth-story-path" aria-hidden="true"><span>Read</span><i></i><span>Explore</span><i></i><span>Understand</span></div>
            </div>
            <p class="auth-story-footer">Chemistry remediation & progress tracking</p>
        </aside>
    </div>
</div>
</x-workspace>
