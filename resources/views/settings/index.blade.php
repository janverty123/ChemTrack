<x-workspace title="Settings">
@php
    $identity = $user->private_identity ?? [];
    $preferences = array_replace(['theme' => 'light', 'text_size' => 'standard', 'reduced_motion' => false, 'default_cycle' => 1], $user->preferences ?? []);
    $reference = $user->role === 'student' ? $user->studentReference() : $user->name;
@endphp
<header class="page-header"><div><p class="eyebrow">Your account</p><h1>Settings</h1><p class="muted">Manage your private details, appearance and account security.</p></div><a class="secondary-button" href="{{ $user->role === 'teacher' ? '/teacher' : '/learn' }}">Back to dashboard</a></header>
<nav class="settings-tabs" aria-label="Settings sections"><a href="#profile">Profile</a><a href="#appearance">Appearance</a><a href="#security">Security</a><a href="#privacy">Privacy & help</a></nav>
<div class="settings-stack">
<section class="settings-section" id="profile" aria-labelledby="profile-heading">
<div class="settings-heading"><h2 id="profile-heading">Private profile</h2><p>{{ $user->role === 'teacher' ? 'Your display name appears in your teacher workspace. Your contact details stay in account settings.' : 'Only you can view these details here. Learning and research views use your student code.' }}</p></div>
<div class="settings-body">
<div class="settings-avatar-row">
    @if($user->avatar_path)<img class="settings-avatar" src="{{ route('settings.avatar') }}" alt="{{ $reference }} avatar" width="72" height="72">@else<span class="settings-avatar" aria-hidden="true">{{ $user->role === 'student' ? 'ST' : mb_strtoupper(mb_substr($reference, 0, 2)) }}</span>@endif
    <div><strong>{{ $reference }}</strong><p class="muted">{{ ucfirst($user->role) }} account</p></div>
</div>
<form method="post" action="/settings/avatar" enctype="multipart/form-data" class="settings-form settings-photo-form">@csrf
<label for="avatar">Profile photo <span class="settings-optional">optional</span></label><input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="avatar-help"><p id="avatar-help" class="settings-help">JPEG, PNG or WebP, up to 2 MB. Photos are cropped to a square and kept private.</p>
@if(extension_loaded('gd'))<button class="secondary-button" type="submit">Save photo</button>@else<p class="settings-help">Photo uploads are temporarily unavailable on this server. Your initials or student code will still be shown.</p>@endif
</form>
@if($user->avatar_path)<form method="post" action="/settings/avatar">@csrf @method('DELETE')<button type="submit" class="text-button">Remove photo</button></form>@endif
<form method="post" action="/settings/profile" class="settings-form">@csrf @method('PATCH')
<label for="full-name">Full name<input id="full-name" name="full_name" autocomplete="name" maxlength="150" required value="{{ old('full_name', $identity['full_name'] ?? ($user->role === 'teacher' ? $user->name : '')) }}"></label>
@if($user->role === 'teacher')
<label for="display-name">Display name<input id="display-name" name="display_name" maxlength="100" value="{{ old('display_name', $user->name) }}"></label>
<div class="settings-fields"><label for="school">School <span class="settings-optional">optional</span><input id="school" name="school" maxlength="150" value="{{ old('school', $user->school) }}"></label><label for="designation">Designation <span class="settings-optional">optional</span><input id="designation" name="designation" maxlength="100" value="{{ old('designation', $user->designation) }}"></label></div>
@else
<dl class="settings-facts"><div><dt>Student code</dt><dd>{{ $user->studentReference() }}</dd></div><div><dt>LRN</dt><dd>{{ isset($identity['lrn']) ? '••••••••'.substr($identity['lrn'], -4) : 'Not supplied' }}</dd></div><div><dt>Grade / section</dt><dd>{{ trim(($identity['grade'] ?? '').' / '.($identity['section'] ?? ''), ' /') ?: 'Not supplied' }}</dd></div></dl>
<p class="settings-help">Your code, LRN and school assignment cannot be changed here. Ask your teacher privately to correct these records.</p>
@endif
<label for="phone">Mobile number <span class="settings-optional">optional</span><input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="30" value="{{ old('phone', $identity['phone'] ?? '') }}"></label>
<x-auth-field name="current_password" id="profile-current-password" label="Current password" type="password" autocomplete="current-password" required />
<div class="settings-actions"><button class="button" type="submit">Save profile</button><a class="secondary-button" href="/settings#profile">Cancel</a></div>
</form></div></section>

<section class="settings-section" id="appearance" aria-labelledby="appearance-heading"><div class="settings-heading"><h2 id="appearance-heading">Appearance & learning</h2><p>Saved to your account and used on your other devices after signing in.</p></div><form method="post" action="/settings/preferences" class="settings-body settings-form">@csrf @method('PATCH')
<fieldset><legend>Theme</legend><div class="settings-choices">@foreach(['light' => 'Light', 'dark' => 'Dark', 'system' => 'Use device setting'] as $value => $label)<label class="settings-choice"><input type="radio" name="theme" value="{{ $value }}" @checked(old('theme', $preferences['theme']) === $value)>{{ $label }}</label>@endforeach</div></fieldset>
<fieldset><legend>Text size</legend><div class="settings-choices">@foreach(['standard' => 'Standard', 'large' => 'Larger'] as $value => $label)<label class="settings-choice"><input type="radio" name="text_size" value="{{ $value }}" @checked(old('text_size', $preferences['text_size']) === $value)>{{ $label }}</label>@endforeach</div></fieldset>
<label class="settings-choice"><input type="checkbox" name="reduced_motion" value="1" @checked(old('reduced_motion', $preferences['reduced_motion']))>Reduce movement and animation</label>
<p class="settings-help">Your device’s reduced-motion setting is always respected.</p>
<label for="default-cycle">Default dashboard cycle<select id="default-cycle" name="default_cycle"><option value="1" @selected((int) old('default_cycle', $preferences['default_cycle']) === 1)>Cycle 1</option><option value="2" @selected((int) old('default_cycle', $preferences['default_cycle']) === 2)>Cycle 2</option></select></label><p class="settings-help">You can preview either cycle. Choosing a default does not unlock activities.</p>
<div class="settings-actions"><button class="button" type="submit">Save preferences</button><a class="secondary-button" href="/settings#appearance">Cancel</a></div></form></section>

<section class="settings-section" id="security" aria-labelledby="security-heading"><div class="settings-heading"><h2 id="security-heading">Account security</h2><p>Use a unique passphrase and keep access to your verified email.</p></div><div class="settings-body settings-stack">
<div><h3>Email address</h3><p class="settings-account-email">{{ $user->email }}</p><p class="settings-help">{{ $user->email_verified_at ? 'Verified email' : 'Email verification or account completion may still be required.' }}</p>
<form method="post" action="/settings/email" class="settings-form">@csrf<label for="email">New email address<input id="email" name="email" type="email" autocomplete="email" maxlength="254" required value="{{ old('email') }}"></label><x-auth-field name="current_password" id="email-current-password" label="Current password" type="password" autocomplete="current-password" required /><p class="settings-help">Confirm the link sent to the new address before it replaces your current email. A security notice goes to your previous address.</p><button class="secondary-button" type="submit">Request email change</button></form></div>
<div class="settings-divider"><h3>Change password</h3><form method="post" action="/settings/password" class="settings-form">@csrf @method('PUT')<x-auth-field name="current_password" id="current-password" label="Current password" type="password" autocomplete="current-password" required /><x-auth-field name="password" id="password" label="New password" type="password" autocomplete="new-password" minlength="12" maxlength="200" required aria-describedby="password-help" /><p id="password-help" class="settings-help">At least 12 characters. You can paste a passphrase or use a password manager.</p><x-auth-field name="password_confirmation" id="password-confirmation" label="Confirm new password" type="password" autocomplete="new-password" required /><button class="secondary-button" type="submit">Change password</button></form></div>
<div class="settings-divider"><h3>Active sessions</h3><p class="settings-help">Browser descriptions are supplied by each device. They do not establish a precise location.</p>
@if(config('session.driver') === 'database')<ul class="settings-sessions">@forelse($sessions as $activeSession)<li><strong>{{ $activeSession->id === session()->getId() ? 'This session' : 'Other session' }}</strong><span>{{ Str::limit($activeSession->user_agent ?: 'Unknown browser', 100) }}</span><small>Last active {{ \Illuminate\Support\Carbon::createFromTimestamp($activeSession->last_activity)->diffForHumans() }}</small></li>@empty<li>No other stored sessions.</li>@endforelse</ul>@else<p class="settings-help">This environment does not store a device session list. Your current sign-in remains active.</p>@endif
<form method="post" action="/settings/sessions/revoke" class="settings-form">@csrf<x-auth-field name="current_password" id="sessions-current-password" label="Current password" type="password" autocomplete="current-password" required /><button class="secondary-button" type="submit">Sign out other sessions</button></form></div>
@if($user->role === 'teacher')<div class="settings-divider"><h3>Two-factor authentication</h3><p class="settings-help">Add an authenticator app and keep your recovery codes somewhere safe.</p><a class="secondary-button" href="/teacher/account-security">Manage two-factor authentication</a></div>@endif
</div></section>
<section class="settings-section" id="privacy" aria-labelledby="privacy-heading"><div class="settings-heading"><h2 id="privacy-heading">Privacy & help</h2><p>Your private identity details are separate from coded learning and research records.</p></div><div class="settings-body"><p>Contact your teacher privately for account corrections, school assignment changes, participation information or the study’s approved privacy policy.</p><p class="settings-help">Learning requires an internet connection. There are no scheduled reminder preferences available yet.</p></div></section>
</div>
</x-workspace>
