<x-auth-layout :title="$base === '/register' ? 'Create student account' : 'Complete your account'" :heading="$base === '/register' ? 'Create your account.' : 'Complete your account.'" intro="Start with your email. Your learning and research records will use your student code." mode="register" step="1" :back-href="$base === '/register' ? '/' : '/settings'" :back-label="$base === '/register' ? 'Back to home' : 'Back to settings'">
<form method="post" action="{{ $base }}" class="stack auth-form">@csrf
    @if($base === '/register')<x-auth-field name="invitation" label="Teacher invitation code" :value="old('invitation', $invitation)" required maxlength="200" autocomplete="off" spellcheck="false" aria-describedby="invitation-help" /><p class="auth-field-help" id="invitation-help">Use the invitation your teacher shared. It connects you to your learning programme.</p>@endif
    <x-auth-field name="email" label="Email address" type="email" required maxlength="254" autocomplete="email" autocapitalize="none" spellcheck="false" />
    <button class="button" type="submit">Send verification code <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
</form>
@if($base === '/register')
<details class="auth-help"><summary>Who can create an account?</summary><p>Students join using a teacher invitation, verify their email, then enter their private details. There is no separate approval step after signup.</p><p>Teacher accounts are set up by the ChemTrack project maintainer. Already have a teacher account? <a href="/login">Sign in with your email</a>.</p></details>
<p class="auth-bottom-link">Already learning with us? <a href="/login">Sign in</a></p>
@endif
</x-auth-layout>
