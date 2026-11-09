<x-auth-layout title="Choose a new password" heading="Choose a new password." intro="Use a unique passphrase to protect your account." mode="recovery" back-href="/login" back-label="Back to sign in">
<form method="post" action="/reset-password" class="stack auth-form">@csrf<input type="hidden" name="token" value="{{ $token }}">
<x-auth-field name="email" label="Email address" type="email" required maxlength="254" autocomplete="username" :value="old('email', $email)" />
<x-auth-field name="password" label="New password" id="reset-password" type="password" required minlength="12" maxlength="200" autocomplete="new-password" />
<x-auth-field name="password_confirmation" label="Confirm password" id="reset-confirmation" type="password" required minlength="12" maxlength="200" autocomplete="new-password" />
<button class="button">Reset password</button></form><p class="help-text">At least 12 characters. Resetting signs out existing sessions.</p>
</x-auth-layout>
