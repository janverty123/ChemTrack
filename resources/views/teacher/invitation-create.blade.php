<x-workspace title="Student invitation">
<section class="surface"><a href="/teacher/users">Back to students</a>
<h1>Create a student invitation</h1>
<p>Each invitation creates one coded student account and enrolls that student in your selected programme. It expires after seven days.</p>
@if($programmes->isEmpty())
<p>Create a programme in the teacher panel before issuing invitations.</p>
@else
<form method="post" action="/teacher/student-invitations" class="stack">@csrf
<label>Programme<select name="programme_id" required>@foreach($programmes as $programme)<option value="{{ $programme->id }}" @selected((string) old('programme_id') === (string) $programme->id)>{{ $programme->title }}</option>@endforeach</select></label>
<button class="button">Create one-use invitation</button>
</form>
@endif
</section>
</x-workspace>
