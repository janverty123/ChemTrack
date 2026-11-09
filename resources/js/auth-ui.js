export function initPasswordToggles(root = document) {
    root.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = button.closest('[data-password-field]').querySelector('input');
        button.hidden = false;
        button.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.dataset.visible = String(show);
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', `${show ? 'Hide' : 'Show'} ${button.dataset.passwordLabel.toLowerCase()}`);
        });
    });
}

if (typeof document !== 'undefined') initPasswordToggles();
