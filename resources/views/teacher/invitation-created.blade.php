<x-workspace title="Invitation created">
<section class="surface">
    <span class="pill">Shown once</span>
    <h1>Copy this invitation now.</h1>
    <p class="muted">Share it privately with one student. It can create one account and expires after seven days.</p>
    <label class="invitation-link">Student registration link<textarea id="invitation-url" rows="3" readonly aria-describedby="invitation-help">{{ $url }}</textarea></label>
    <p id="invitation-help" class="help-text">If you leave this page, the link cannot be displayed again. Create another invitation if needed.</p>
    <div class="invitation-actions">
        <button type="button" class="button" data-copy-target="invitation-url">Copy link</button>
        <a class="secondary-button" href="/teacher/student-invitations">Create another invitation</a>
        <a href="/teacher/users">Back to students</a>
    </div>
    <p class="help-text" data-copy-status role="status" aria-live="polite"></p>
</section>
</x-workspace>
