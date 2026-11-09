export function initWorkspaceNavigation(doc = document, win = window) {
    const root = doc.querySelector('.workspace-page');
    if (!root) return;
    const sidebar = doc.querySelector('[data-workspace-sidebar]');
    const main = doc.querySelector('#main');
    const hide = doc.querySelector('[data-sidebar-hide]');
    const show = doc.querySelector('[data-sidebar-show]');
    const toolbar = doc.querySelector('[data-sidebar-toolbar]');
    const backdrop = doc.querySelector('[data-sidebar-backdrop]');
    const media = win.matchMedia('(max-width: 900px)');
    const key = 'chemtrack.navigation.collapsed';
    let preference = null, collapsed = false;
    try {
        const stored = win.sessionStorage.getItem(key);
        if (stored === 'true' || stored === 'false') preference = stored === 'true';
    } catch { /* Navigation also works when browser storage is unavailable. */ }

    const setCollapsed = (value, { remember = false, focus = false } = {}) => {
        collapsed = value;
        const modal = !value && media.matches;
        root.classList.toggle('sidebar-collapsed', value);
        root.classList.toggle('sidebar-modal-open', modal);
        sidebar.inert = value;
        sidebar.setAttribute('aria-hidden', String(value));
        hide.setAttribute('aria-expanded', String(!value));
        show.setAttribute('aria-expanded', String(!value));
        toolbar.hidden = !value;
        backdrop.hidden = !modal;
        main.inert = modal;
        if (modal) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
        } else {
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');
        }
        if (remember) {
            preference = value;
            try { win.sessionStorage.setItem(key, String(value)); } catch { /* Optional preference only. */ }
        }
        if (focus || (value && sidebar.contains(doc.activeElement))) (value ? show : hide).focus({ preventScroll: true });
    };
    hide.hidden = false;
    root.classList.add('navigation-ready');
    setCollapsed(media.matches || preference === true);
    hide.addEventListener('click', () => setCollapsed(true, { remember: true, focus: true }));
    show.addEventListener('click', () => setCollapsed(false, { remember: true, focus: true }));
    backdrop.addEventListener('click', () => setCollapsed(true, { remember: true, focus: true }));
    sidebar.querySelector('nav').addEventListener('click', (event) => {
        if (media.matches && event.target.closest('a')) setCollapsed(true);
    });
    doc.addEventListener('keydown', (event) => {
        if (collapsed) return;
        if (event.key === 'Escape' && media.matches) {
            event.preventDefault();
            setCollapsed(true, { remember: true, focus: true });
        } else if (event.key === 'Tab' && media.matches) {
            const controls = [...sidebar.querySelectorAll('a[href], button:not([disabled])')];
            const first = controls[0], last = controls.at(-1);
            if (event.shiftKey && doc.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && doc.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    media.addEventListener('change', () => setCollapsed(media.matches || preference === true, { focus: main.inert }));
}

if (typeof document !== 'undefined') initWorkspaceNavigation();
