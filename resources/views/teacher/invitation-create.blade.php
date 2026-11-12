<x-workspace title="Student invitation">
<section class="surface">
    <a class="back-link" href="/teacher/users">Back to students</a>
    <h1>Invite a student</h1>
    <p class="muted">Each invitation creates one coded student account and enrolls that student in the programme you choose. It works once and expires after seven days.</p>
    @if($programmes->isEmpty())
        <p class="pending-notice">Create a programme in your teacher dashboard before issuing invitations.</p>
        <a class="button" href="/teacher/programmes/create">Create a programme</a>
    @else
        <form method="post" action="/teacher/student-invitations" class="stack">@csrf
            <label>Programme<select name="programme_id" required>@foreach($programmes as $programme)<option value="{{ $programme->id }}" @selected((string) old('programme_id') === (string) $programme->id)>{{ $programme->title }}</option>@endforeach</select></label>
            <div><button class="button">Create one-use invitation</button></div>
        </form>
    @endif
</section>
</x-workspace>
