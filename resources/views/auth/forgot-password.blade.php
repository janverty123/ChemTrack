<x-auth-layout title="Reset your password" heading="Forgot your password?" intro="Enter your account email and we’ll send you a reset link. For a code-only account without email, ask your teacher for help." mode="recovery" back-href="/login" back-label="Back to sign in">
<form method="post" action="/forgot-password" class="stack auth-form">@csrf
<x-auth-field name="email" label="Email address" type="email" required maxlength="254" autocomplete="email" />
<button class="button" type="submit">Send reset link</button></form>
</x-auth-layout>
