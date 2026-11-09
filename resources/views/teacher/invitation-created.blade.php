<x-workspace title="Invitation created">
<section class="surface"><h1>Copy this invitation now.</h1>
<p>This link is shown once. Share it privately with one student. It expires after seven days and can create one account.</p>
<label>Student registration link<textarea rows="4" readonly aria-describedby="invitation-help">{{ $url }}</textarea></label>
<p id="invitation-help">If you leave this page, the link cannot be displayed again. Create another invitation if needed.</p>
<a class="button" href="/teacher/student-invitations">Create another invitation</a> <a href="/teacher/users">Back to students</a>
</section>
</x-workspace>
