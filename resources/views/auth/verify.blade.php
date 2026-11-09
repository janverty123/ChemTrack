<x-auth-layout title="Verify your email" heading="Check your email." intro="One quick check before we set up your account." mode="register" step="2" :back-href="$base.'?restart=1'" back-label="Change email">
<p class="muted">Enter the six-digit code sent to {{ $destination }}. The code expires after 10 minutes. Check your spam folder if needed.</p>
<form method="post" action="{{ $base }}/verify" class="stack auth-form">@csrf
<x-auth-field name="code" label="Verification code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" autocomplete="one-time-code" required />
<button class="button">Verify email</button></form>
<form method="post" action="{{ $base }}/resend" class="auth-resend">@csrf<button id="resend-code" class="secondary-button" @disabled($resendAfter > 0)>Send another code</button></form>
<p class="help-text" id="resend-countdown">Resend available {{ $resendAfter > 0 ? 'in '.$resendAfter.' seconds' : 'now' }}.</p>
<script>
(() => {
    const button = document.getElementById('resend-code');
    const label = document.getElementById('resend-countdown');
    const readyAt = Date.now() + @json((int) $resendAfter) * 1000;
    const update = () => {
        const remaining = Math.max(0, Math.ceil((readyAt - Date.now()) / 1000));
        button.disabled = remaining > 0;
        label.textContent = remaining ? `Resend available in ${remaining} seconds.` : 'You can request another code now.';
        if (!remaining) clearInterval(timer);
    };
    const timer = setInterval(update, 1000);
    update();
})();
</script>
</x-auth-layout>
