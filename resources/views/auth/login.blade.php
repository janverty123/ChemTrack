<x-auth-layout title="Sign in" heading="Welcome back." intro="Sign in with your student code or teacher email.">
<form method="post" action="/login" class="stack auth-form">@csrf
    <x-auth-field name="identifier" label="Student code or email" :value="old('identifier', old('email'))" required autocomplete="username" maxlength="254" autocapitalize="none" spellcheck="false" />
    <x-auth-field name="password" label="Password" id="login-password" type="password" required autocomplete="current-password" maxlength="200" />
    <div class="auth-form-links"><label class="auth-remember"><input type="checkbox" name="remember" value="1" checked> Keep me signed in</label><a href="/forgot-password">Forgot password?</a></div>
    <button class="button" type="submit">Sign in <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
</form>
<p class="auth-bottom-link">New to ChemTrack? <a href="/register">Create your student account</a></p>
</x-auth-layout>
