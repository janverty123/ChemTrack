<x-workspace title="Settings">
@php
    $identity = $user->private_identity ?? [];
    $preferences = array_replace(['theme' => 'light', 'text_size' => 'standard', 'reduced_motion' => false, 'default_cycle' => 1], $user->preferences ?? []);
    $reference = $user->role === 'student' ? $user->studentReference() : $user->name;
    $isTeacher = $user->role === 'teacher';
@endphp
<header class="page-header"><div><p class="eyebrow">Your account</p><h1>Settings</h1><p class="muted">Your profile, how ChemTrack looks, and how your account is protected.</p></div><a class="secondary-button" href="{{ $isTeacher ? '/teacher' : '/learn' }}">Back to dashboard</a></header>
<nav class="settings-tabs" aria-label="Settings sections"><a href="#profile">Profile</a><a href="#appearance">Appearance</a><a href="#security">Security</a><a href="#privacy">Privacy</a></nav>
<div class="settings-stack">
<section class="settings-section" id="profile" aria-labelledby="profile-heading">
<div class="settings-heading"><h2 id="profile-heading">Profile</h2><p>{{ $isTeacher ? 'Your display name is shown in your teacher workspace.' : 'Only you can see these details. Your learning path and your teacher’s reports show your student code.' }}</p></div>
<div class="settings-body">
<div class="settings-avatar-row">
    @if($user->avatar_path)<img class="settings-avatar" src="{{ route('settings.avatar') }}" alt="{{ $reference }} avatar" width="72" height="72">@else<span class="settings-avatar" aria-hidden="true">{{ $isTeacher ? mb_strtoupper(mb_substr($reference, 0, 2)) : 'ST' }}</span>@endif
    <div><strong>{{ $reference }}</strong><p class="muted">{{ ucfirst($user->role) }} account</p></div>
</div>
<form method="post" action="/settings/avatar" enctype="multipart/form-data" class="settings-form settings-photo-form">@csrf
<label for="avatar">Profile photo <span class="settings-optional">optional</span></label><input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="avatar-help"><p id="avatar-help" class="settings-help">JPEG, PNG or WebP, up to 2 MB. Your photo is cropped to a square and only shown to you.</p>
@if(extension_loaded('gd'))<button class="secondary-button" type="submit">Save photo</button>@else<p class="settings-help">Photo uploads are not available right now.</p>@endif
</form>
@if($user->avatar_path)<form method="post" action="/settings/avatar">@csrf @method('DELETE')<button type="submit" class="text-button">Remove photo</button></form>@endif
<form method="post" action="/settings/profile" class="settings-form">@csrf @method('PATCH')
<label for="full-name">Full name<input id="full-name" name="full_name" autocomplete="name" maxlength="150" required value="{{ old('full_name', $identity['full_name'] ?? ($isTeacher ? $user->name : '')) }}"></label>
@if($isTeacher)
<label for="display-name">Display name<input id="display-name" name="display_name" maxlength="100" value="{{ old('display_name', $user->name) }}"></label>
<div class="settings-fields"><label for="school">School <span class="settings-optional">optional</span><input id="school" name="school" maxlength="150" value="{{ old('school', $user->school) }}"></label><label for="designation">Designation <span class="settings-optional">optional</span><input id="designation" name="designation" maxlength="100" value="{{ old('designation', $user->designation) }}"></label></div>
@else
<dl class="settings-facts"><div><dt>Student code</dt><dd>{{ $user->studentReference() }}</dd></div><div><dt>LRN</dt><dd>{{ isset($identity['lrn']) ? '••••••••'.substr($identity['lrn'], -4) : 'Not added' }}</dd></div><div><dt>Grade / section</dt><dd>{{ trim(($identity['grade'] ?? '').' / '.($identity['section'] ?? ''), ' /') ?: 'Not added' }}</dd></div></dl>
<p class="settings-help">Need to correct your LRN, grade or section? Ask your teacher.</p>
@endif
<label for="phone">Mobile number <span class="settings-optional">optional</span><input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="30" value="{{ old('phone', $identity['phone'] ?? '') }}"></label>
<x-auth-field name="current_password" id="profile-current-password" label="Current password" type="password" autocomplete="current-password" required />
<div class="settings-actions"><button class="button" type="submit">Save profile</button><a class="secondary-button" href="/settings#profile">Cancel</a></div>
</form></div></section>

<section class="settings-section" id="appearance" aria-labelledby="appearance-heading"><div class="settings-heading"><h2 id="appearance-heading">Appearance</h2><p>Choose how ChemTrack looks. Your choices follow you to any device you sign in on.</p></div><form method="post" action="/settings/preferences" class="settings-body settings-form">@csrf @method('PATCH')
<fieldset><legend>Theme</legend><div class="settings-choices">@foreach(['light' => 'Light', 'dark' => 'Dark', 'system' => 'Match my device'] as $value => $label)<label class="settings-choice"><input type="radio" name="theme" value="{{ $value }}" @checked(old('theme', $preferences['theme']) === $value)>{{ $label }}</label>@endforeach</div></fieldset>
<fieldset><legend>Text size</legend><div class="settings-choices">@foreach(['standard' => 'Standard', 'large' => 'Larger'] as $value => $label)<label class="settings-choice"><input type="radio" name="text_size" value="{{ $value }}" @checked(old('text_size', $preferences['text_size']) === $value)>{{ $label }}</label>@endforeach</div></fieldset>
<label class="settings-choice"><input type="checkbox" name="reduced_motion" value="1" @checked(old('reduced_motion', $preferences['reduced_motion']))>Reduce movement and animation</label>
@unless($isTeacher)
<label for="default-cycle">Open my dashboard on<select id="default-cycle" name="default_cycle"><option value="1" @selected((int) old('default_cycle', $preferences['default_cycle']) === 1)>Cycle 1</option><option value="2" @selected((int) old('default_cycle', $preferences['default_cycle']) === 2)>Cycle 2</option></select></label>
@else
<input type="hidden" name="default_cycle" value="{{ (int) $preferences['default_cycle'] }}">
@endunless
<div class="settings-actions"><button class="button" type="submit">Save appearance</button><a class="secondary-button" href="/settings#appearance">Cancel</a></div></form></section>

<section class="settings-section" id="security" aria-labelledby="security-heading"><div class="settings-heading"><h2 id="security-heading">Security</h2><p>Keep your account safe with a strong password and an email you can access.</p></div><div class="settings-body settings-stack">
<div><h3>Email address</h3><p class="settings-account-email">{{ $user->email }}</p><p class="settings-help">{{ $user->email_verified_at ? 'Verified' : 'Not verified' }}</p>
<form method="post" action="/settings/email" class="settings-form">@csrf<label for="email">New email address<input id="email" name="email" type="email" autocomplete="email" maxlength="254" required value="{{ old('email') }}"></label><x-auth-field name="current_password" id="email-current-password" label="Current password" type="password" autocomplete="current-password" required /><p class="settings-help">We’ll send a confirmation link to the new address. Your current email keeps working until you confirm.</p><button class="secondary-button" type="submit">Change email</button></form></div>
<div class="settings-divider"><h3>Password</h3><form method="post" action="/settings/password" class="settings-form">@csrf @method('PUT')<x-auth-field name="current_password" id="current-password" label="Current password" type="password" autocomplete="current-password" required /><x-auth-field name="password" id="password" label="New password" type="password" autocomplete="new-password" minlength="12" maxlength="200" required aria-describedby="password-help" /><p id="password-help" class="settings-help">At least 12 characters. A short phrase is easy to remember and hard to guess.</p><x-auth-field name="password_confirmation" id="password-confirmation" label="Confirm new password" type="password" autocomplete="new-password" required /><button class="secondary-button" type="submit">Change password</button></form></div>
<div class="settings-divider"><h3>Signed-in devices</h3>
@if(config('session.driver') === 'database')<ul class="settings-sessions">@forelse($sessions as $activeSession)<li><strong>{{ $activeSession->id === session()->getId() ? 'This device' : 'Another device' }}</strong><span>{{ Str::limit($activeSession->user_agent ?: 'Unknown browser', 100) }}</span><small>Active {{ \Illuminate\Support\Carbon::createFromTimestamp($activeSession->last_activity)->diffForHumans() }}</small></li>@empty<li>Only this device is signed in.</li>@endforelse</ul>@endif
<form method="post" action="/settings/sessions/revoke" class="settings-form">@csrf<x-auth-field name="current_password" id="sessions-current-password" label="Current password" type="password" autocomplete="current-password" required /><button class="secondary-button" type="submit">Sign out other devices</button></form></div>
@if($isTeacher)<div class="settings-divider"><h3>Two-factor authentication</h3><p class="settings-help">Add a code from an authenticator app each time you sign in. Keep your recovery codes somewhere safe.</p><a class="secondary-button" href="/teacher/account-security">Set up two-factor authentication</a></div>@endif
</div></section>
<section class="settings-section" id="privacy" aria-labelledby="privacy-heading"><div class="settings-heading"><h2 id="privacy-heading">Privacy</h2><p>How your information is used.</p></div><div class="settings-body">@if($isTeacher)<p>Students appear by their student code everywhere in your workspace, reports and exports. Their names and LRNs stay private to each student.</p>@else<p>Your name, LRN and contact details are only visible to you. Your learning path, scores and your teacher’s reports use your student code instead.</p><p class="settings-help">Questions about your account or the study? Ask your teacher.</p>@endif</div></section>
</div>
</x-workspace>
