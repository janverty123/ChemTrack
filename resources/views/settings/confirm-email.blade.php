<x-workspace title="Confirm email change">
<header class="page-header"><div><h1>Confirm your new email</h1><p class="muted">Your existing address is still active. Confirm to use the verified new address for sign-in and recovery.</p></div></header>
<section class="settings-section"><form method="post" action="{{ request()->fullUrl() }}" class="settings-body settings-form">@csrf<div class="settings-actions"><button class="button" type="submit">Confirm email change</button><a class="secondary-button" href="/settings">Cancel</a></div></form></section>
</x-workspace>
