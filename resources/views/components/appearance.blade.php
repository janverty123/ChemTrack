@props(['public' => false])
@php($appearance = auth()->check() ? (auth()->user()->preferences ?? []) : null)
<script>
(() => {
    const saved = @json($appearance);
    const canCustomize = ! @json($public) && saved !== null;
    const prefs = canCustomize ? saved : {};
    const root = document.documentElement;
    let theme = ['light', 'dark', 'system'].includes(prefs.theme) ? prefs.theme : 'light';
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    const apply = () => {
        const dark = theme === 'dark' || (theme === 'system' && media.matches);
        root.dataset.theme = dark ? 'dark' : 'light';
        root.classList.toggle('dark', dark);
        root.style.colorScheme = dark ? 'dark' : 'light';
    };
    root.dataset.textSize = prefs.text_size === 'large' ? 'large' : 'standard';
    root.dataset.reducedMotion = prefs.reduced_motion ? 'true' : 'false';
    const persist = () => {
        try {
            if (canCustomize) localStorage.setItem('chemtrack.appearance', JSON.stringify({ theme, text_size: root.dataset.textSize, reduced_motion: root.dataset.reducedMotion === 'true' }));
            localStorage.setItem('filament:theme:mode', theme);
        } catch {}
    };
    apply();
    persist();
    media.addEventListener?.('change', apply);
    window.addEventListener('theme-changed', (event) => {
        if (! canCustomize || ! ['light', 'dark', 'system'].includes(event.detail)) return;
        theme = event.detail;
        apply();
        persist();
    });
})();
</script>
