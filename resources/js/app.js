import './learning-ui.js';
import './workspace-navigation.js';
import './reader.js';
import './landing.js';
import './auth-ui.js';

const form = document.querySelector('[data-assessment]');
if (form) {
    const status = document.querySelector('#save-status');
    let revision = 0, savedRevision = 0, inFlight = null, timer, submitting = false;
    const answers = () => Array.from({ length: Number(form.dataset.questionCount) }, (_, i) => {
        const field = form.querySelector(`input[name="answers[${i}]"]:checked`);
        return field ? Number(field.value) : null;
    });
    async function save() {
        clearTimeout(timer);
        if (inFlight) {
            await inFlight;
            if (savedRevision < revision && !submitting) return save();
            return;
        }
        if (submitting) return;
        const current = revision;
        status.textContent = 'Saving answers…';
        inFlight = (async () => {
            try {
                const response = await fetch(form.dataset.saveUrl, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ answers: answers() }), signal: AbortSignal.timeout(15000),
                });
                if (!response.ok) throw new Error('Save was not confirmed');
                const result = await response.json();
                savedRevision = current;
                status.textContent = savedRevision === revision ? `Saved at ${new Date(result.saved_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}. You can safely return later.` : 'More changes are waiting to save.';
            } catch {
                status.textContent = 'Save not confirmed. Keep this page open, check your connection, then select Save answers to retry.';
            }
        })();
        await inFlight;
        inFlight = null;
    }
    form.addEventListener('change', () => { revision++; status.textContent = 'Unsaved changes.'; clearTimeout(timer); timer = setTimeout(save, 700); });
    form.querySelector('[data-save-button]').addEventListener('click', (event) => { event.preventDefault(); save(); });
    form.addEventListener('submit', async (event) => {
        event.preventDefault(); submitting = true; clearTimeout(timer);
        if (inFlight) await inFlight;
        HTMLFormElement.prototype.submit.call(form);
    });
    window.addEventListener('beforeunload', (event) => { if (!submitting && revision !== savedRevision) { event.preventDefault(); event.returnValue = ''; } });
    window.addEventListener('offline', () => { status.textContent = 'Connection lost. Keep this page open. Unsaved changes need a connection to be saved.'; });
    window.addEventListener('online', () => { if (revision !== savedRevision) save(); });
}
