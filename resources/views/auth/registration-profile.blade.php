<x-auth-layout title="Your private account details" heading="A little about you." intro="Your email is verified. These details stay private; your learning records use your student code." mode="register" step="3" :back-href="$base.'?restart=1'" back-label="Change email">
<form method="post" action="{{ $base }}/profile" class="stack auth-form">@csrf
<x-auth-field name="full_name" label="Full name" required maxlength="150" autocomplete="name" />
<x-auth-field name="lrn" label="Learner Reference Number (LRN)" inputmode="numeric" pattern="[0-9]{12}" minlength="12" maxlength="12" required autocomplete="off" aria-describedby="lrn-help" />
<p class="auth-field-help" id="lrn-help">Enter your 12-digit school LRN. Ask your teacher privately if you need a correction.</p>
@if(!$completion)<div class="auth-select"><label for="signup-category">G/B category</label><select id="signup-category" name="category" required><option value="">Choose your category</option><option value="G" @selected(old('category') === 'G')>G</option><option value="B" @selected(old('category') === 'B')>B</option></select></div>@endif
<x-auth-field name="phone" label="Mobile number (optional)" type="tel" maxlength="30" autocomplete="tel" />
<div class="auth-field-row"><x-auth-field name="grade" label="Grade/year (optional)" maxlength="40" /><x-auth-field name="section" label="Section (optional)" maxlength="60" /></div>
<x-auth-field name="password" label="Password" id="signup-password" type="password" required minlength="12" maxlength="200" autocomplete="new-password" aria-describedby="signup-password-help" />
<x-auth-field name="password_confirmation" label="Confirm password" id="signup-confirmation" type="password" required minlength="12" maxlength="200" autocomplete="new-password" />
<p class="auth-field-help" id="signup-password-help">At least 12 characters. You can paste a passphrase or use a password manager. Your teacher provides participation and privacy guidance separately.</p>
<button class="button">{{ $completion ? 'Complete account' : 'Create account' }}</button>
</form></x-auth-layout>
