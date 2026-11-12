@props(['title' => 'ChemTrack', 'public' => false, 'authPage' => false])
@php
    $user = auth()->user();
    $isTeacher = $user?->role === 'teacher';
    $dashboardUrl = $isTeacher ? '/teacher' : '/learn';
    $navigation = $public ? [] : array_values(array_filter([
        ['href' => $dashboardUrl, 'label' => $isTeacher ? 'Dashboard' : 'My learning path', 'icon' => 'path', 'active' => request()->is('learn*') || request()->is('teacher')],
        $isTeacher ? ['href' => '/teacher/programmes', 'label' => 'Programmes', 'icon' => 'book', 'active' => request()->is('teacher/programmes*')] : null,
        $isTeacher ? ['href' => '/teacher/users', 'label' => 'Students', 'icon' => 'people', 'active' => request()->is('teacher/users*', 'teacher/student-invitations*')] : null,
        ['href' => '/settings', 'label' => 'Settings', 'icon' => 'gear', 'active' => request()->is('settings*')],
    ]));
    $icons = [
        'path' => '<path d="M5 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm14-10a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM7 17h6a3 3 0 0 0 0-6h-2a3 3 0 0 1 0-6h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
        'book' => '<path d="M12 6.5C10 5 7.5 4.5 4 5v13.5c3.5-.5 6 0 8 1.5m0-13.5C14 5 16.5 4.5 20 5v13.5c-3.5-.5-6 0-8 1.5m0-13.5V20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
        'people' => '<path d="M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm-6 9a6 6 0 0 1 12 0m1-15.8a3.5 3.5 0 0 1 0 6.6M21 20a6 6 0 0 0-3.5-5.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
        'gear' => '<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" stroke="currentColor" stroke-width="1.6"/>',
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
<x-head-meta />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex,nofollow">
<title>{{ $title }} · ChemTrack</title>
<x-appearance :public="$public" />
@vite(['resources/css/app.css', 'resources/js/app.js'])
@if($authPage)<style>@view-transition { navigation: auto; } @media (prefers-reduced-motion: reduce) { @view-transition { navigation: none; } }</style>@endif
</head>
<body class="{{ $public ? 'public-page' : 'workspace-page' }}">
<a href="#main" class="skip-link">Skip to content</a>
@unless($public)
<aside id="workspace-sidebar" class="sidebar" data-workspace-sidebar aria-label="Workspace navigation">
    <div class="sidebar-heading">
        <x-brand :href="$dashboardUrl" aria-label="ChemTrack dashboard" />
        <button type="button" class="sidebar-hide" data-sidebar-hide aria-label="Hide navigation" aria-controls="workspace-sidebar" aria-expanded="true" hidden><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="4" stroke="currentColor" stroke-width="1.7"/><path d="M9 4v16m8-11-3 3 3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    </div>
    <p class="workspace-label">{{ $isTeacher ? 'Teacher' : 'Student' }} workspace</p>
    <nav aria-label="Main navigation">
        @foreach($navigation as $item)
        <a href="{{ $item['href'] }}" @class(['nav-active' => $item['active']]) @if($item['active']) aria-current="page" @endif><svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true">{!! $icons[$item['icon']] !!}</svg><span>{{ $item['label'] }}</span></a>
        @endforeach
    </nav>
    <div class="account">
        <a class="sidebar-profile" href="/settings" aria-label="Open your profile settings">
            <span class="sidebar-avatar" aria-hidden="true">@if($user->avatar_path)<img src="{{ route('settings.avatar') }}" width="40" height="40" alt="">@else<svg viewBox="0 0 24 24" width="21" height="21" fill="none"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M5 21v-2a7 7 0 0 1 14 0v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>@endif</span>
            <div><strong>{{ $user->role === 'student' ? $user->studentReference() : $user->name }}</strong><span>{{ ucfirst($user->role) }}</span></div>
        </a>
        <form method="post" action="/logout">@csrf<button type="submit" class="sidebar-signout"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"><path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4M15 8l4 4-4 4M9 12h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Sign out</span></button></form>
    </div>
</aside>
<button class="sidebar-backdrop" type="button" data-sidebar-backdrop aria-label="Close navigation" tabindex="-1" hidden></button>
@endunless
<main id="main" class="{{ $public ? 'login-main' : 'main-content' }}">
@unless($public)
<div class="workspace-toolbar" data-sidebar-toolbar hidden>
    <button type="button" class="sidebar-show" data-sidebar-show aria-label="Show navigation" aria-controls="workspace-sidebar" aria-expanded="false"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg><span>Menu</span></button>
    <x-brand class="toolbar-brand" :href="$dashboardUrl" aria-label="ChemTrack dashboard" />
</div>
@endunless
@if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="error-notice" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
{{ $slot }}
</main>
</body>
</html>
