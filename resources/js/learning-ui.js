// Cycle selection is a normal server request, so eligibility and empty states also work without JavaScript.
document.querySelectorAll('.cycle-switch a').forEach((link) => {
    link.addEventListener('click', () => link.setAttribute('aria-busy', 'true'));
});

// One-time invitation link: copy to clipboard, falling back to selecting the text.
document.querySelectorAll('[data-copy-target]').forEach((button) => {
    const field = document.getElementById(button.dataset.copyTarget);
    const status = document.querySelector('[data-copy-status]');
    if (!field) return;
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(field.value);
            if (status) status.textContent = 'Link copied. Share it privately with one student.';
        } catch {
            field.focus();
            field.select();
            if (status) status.textContent = 'Press Ctrl+C (or long-press and choose Copy) to copy the selected link.';
        }
    });
});
