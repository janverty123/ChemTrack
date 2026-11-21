<x-workspace title="Join a programme">
<section class="surface">
    <a class="back-link" href="/learn">Back to my learning path</a>
    @if($invitation)
        <span class="pill">Invitation</span>
        <h1>Join {{ $invitation->programme->title }}?</h1>
        <p class="muted">Your teacher will see your student code and your progress in this programme. Your private details stay private.</p>
        <form method="post" action="/join" class="stack">@csrf
            <input type="hidden" name="code" value="{{ $code }}">
            <div><button class="button">Join programme</button></div>
        </form>
    @else
        <h1>Join a programme</h1>
        <p class="muted">Enter the invitation code your teacher shared with your class.</p>
        @if($problem)<p class="error-notice" role="alert">{{ $problem }}</p>@endif
        <form method="post" action="/join" class="stack">@csrf
            <label>Invitation code<input name="code" value="{{ old('code', $code) }}" required maxlength="40" autocomplete="off" autocapitalize="characters" spellcheck="false" @error('code') aria-invalid="true" aria-describedby="join-code-error" @enderror></label>
            @error('code')<p class="field-error" id="join-code-error">{{ $message }}</p>@enderror
            <div><button class="button">Continue</button></div>
        </form>
    @endif
</section>
</x-workspace>
