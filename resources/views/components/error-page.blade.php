@props(['code', 'title', 'message'])
@php
    try {
        $role = auth()->user()?->role;
    } catch (\Throwable) {
        $role = null;
    }
    $home = match ($role) { 'teacher' => '/teacher', 'student' => '/learn', default => '/' };
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<title>{{ $title }} · ChemTrack</title>
<style>
:root { --bg:#f6f6f1; --surface:#fff; --line:#e2e2d8; --text:#14171f; --muted:#5b6070; --primary:#3346d3; --accent:#c8f051; color-scheme: light dark; }
@media (prefers-color-scheme: dark) { :root { --bg:#0e111a; --surface:#161a26; --line:#2a3042; --text:#eceef4; --muted:#a3a9bb; --primary:#8f9dff; } }
* { box-sizing: border-box; }
body { margin:0; min-height:100dvh; display:grid; place-items:center; padding:24px; background:var(--bg); color:var(--text); font:16px/1.6 Inter, system-ui, -apple-system, 'Segoe UI', sans-serif; }
main { width:100%; max-width:520px; padding:40px clamp(24px,6vw,48px); background:var(--surface); border:1px solid var(--line); border-radius:28px; text-align:center; }
svg { width:56px; height:56px; }
.code { display:inline-block; margin:20px 0 8px; padding:4px 12px; border-radius:999px; background:var(--accent); color:#1d2600; font-weight:700; font-size:.8rem; letter-spacing:.06em; }
h1 { margin:0 0 10px; font-size:clamp(1.6rem,1.2rem + 1.6vw,2.2rem); line-height:1.15; letter-spacing:-.02em; }
p { margin:0 0 28px; color:var(--muted); }
.actions { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; }
a { display:inline-flex; align-items:center; min-height:46px; padding:10px 20px; border-radius:14px; font-weight:600; text-decoration:none; }
.primary { background:var(--primary); color:#fff; }
@media (prefers-color-scheme: dark) { .primary { color:#0e111a; } }
.secondary { border:1px solid var(--line); color:var(--text); }
a:focus-visible { outline:3px solid var(--primary); outline-offset:3px; }
</style>
</head>
<body>
<main>
    <svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M16 3.8 26.3 9.9V22.1L16 28.2 5.7 22.1V9.9Z" stroke="currentColor" stroke-opacity=".18" stroke-width="3.6" stroke-linejoin="round"/><path d="M16 3.8 26.3 9.9V22.1L16 28.2 5.7 22.1" stroke="#3346d3" stroke-width="3.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="16" cy="16" r="4.4" stroke="currentColor" stroke-width="2.6"/><circle cx="5.7" cy="22.1" r="4.3" fill="#c8f051" stroke="#3346d3" stroke-width="2.4"/></svg>
    <div><span class="code">Error {{ $code }}</span></div>
    <h1>{{ $title }}</h1>
    <p>{{ $message }}</p>
    <div class="actions">
        <a class="primary" href="{{ $home }}">{{ $home === '/' ? 'Go to ChemTrack home' : 'Back to my dashboard' }}</a>
        {{ $slot }}
    </div>
</main>
</body>
</html>
