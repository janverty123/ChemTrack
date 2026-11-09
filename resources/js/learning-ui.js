// Cycle selection is a normal server request, so eligibility and empty states also work without JavaScript.
document.querySelectorAll('.cycle-switch a').forEach(link => {
    link.addEventListener('click', () => link.setAttribute('aria-busy', 'true'));
});
