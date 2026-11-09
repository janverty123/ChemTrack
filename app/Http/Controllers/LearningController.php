<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attempt;
use App\Models\ReadingProgress;
use App\Services\CycleProgress;
use App\Services\Learning;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    public function index(Request $request, Learning $learning)
    {
        abort_unless($request->user()->role === 'student', 403);

        $cycle = (int) ($request->validate(['cycle' => 'sometimes|integer|in:1,2'])['cycle'] ?? session('learning.cycle', $request->user()->preferences['default_cycle'] ?? 1));
        $request->session()->put('learning.cycle', $cycle);
        $programmes = $request->user()->programmes()->where('published', true)->with('activities')->get();
        $completed = $learning->completedIds($request->user());
        $ids = $programmes->flatMap(fn ($p) => $p->activities->pluck('id'));
        $reading = ReadingProgress::where('user_id', $request->user()->id)->whereIn('activity_id', $ids)->get()->keyBy('activity_id');
        $attempts = Attempt::where('user_id', $request->user()->id)->whereIn('activity_id', $ids)->get()->keyBy('activity_id');
        $states = [];
        $cycleSummaries = [];
        $cycleProgress = app(CycleProgress::class);
        foreach ($programmes as $programme) {
            $cycleSummaries[$programme->id] = [1 => $cycleProgress->summary($programme, 1, $completed), 2 => $cycleProgress->summary($programme, 2, $completed)];
            foreach ($programme->activities as $activity) {
                $activity->setRelation('programme', $programme);
                $complete = in_array($activity->id, $completed);
                $reason = $cycleProgress->lockReason($request->user(), $activity, $completed);
                $locked = $reason !== null;
                $observation = $reading->get($activity->id);
                $observed = $observation && $observation->content_version === $activity->contentVersion() ? $observation->progressPercent() : 0;
                $attempt = $attempts->get($activity->id);
                $answered = count(array_filter($attempt?->answers ?? [], fn ($answer) => $answer !== null));
                $percent = $complete ? 100 : ($activity->type === 'lesson' ? $observed : min(99, (int) floor($answered / max(1, count($activity->questions ?? [])) * 100)));
                $detail = $complete ? 'Completed and recorded' : ($locked ? $reason : ($activity->assessment_source === 'google_forms' ? 'Teacher connection required to verify this test' : ($activity->type === 'lesson' ? ($observed ? $observed.'% viewed; completion still pending' : 'Ready to read') : ($activity->type === 'video' ? 'Watch and finish the video lesson' : ($answered ? $answered.' answers saved' : 'Ready when you are')))));
                $states[$activity->id] = compact('complete', 'locked', 'percent', 'detail');
            }
        }

        return view('learning.index', compact('programmes', 'completed', 'reading', 'attempts', 'states', 'cycle', 'cycleSummaries'));
    }

    public function show(Request $request, Activity $activity, Learning $learning)
    {
        $learning->authorize($request->user(), $activity);

        $attempt = in_array($activity->type, ['lesson', 'video']) || $activity->assessment_source === 'google_forms' ? null : $learning->attempt($request->user(), $activity);
        $reading = ReadingProgress::where('user_id', $request->user()->id)->where('activity_id', $activity->id)->first();
        $version = $activity->contentVersion();

        return view('learning.activity', ['activity' => $activity, 'questions' => $activity->publicQuestions(), 'attempt' => $attempt, 'reading' => $reading, 'version' => $version]);
    }

    public function complete(Request $request, Activity $activity, Learning $learning)
    {
        $learning->complete($request->user(), $activity, $request->boolean('video_acknowledged'));

        return redirect('/learn')->with('status', 'Lesson completed. Your next activity is ready.');
    }

    private function answers(Request $request): array
    {
        return $request->validate(['answers' => 'present|array|max:100', 'answers.*' => 'nullable|integer|min:0|max:4'])['answers'];
    }

    public function save(Request $request, Activity $activity, Learning $learning)
    {
        $learning->authorize($request->user(), $activity);
        $attempt = $learning->save($request->user(), $activity, $this->answers($request), false);
        if ($request->expectsJson()) {
            return response()->json(['saved_at' => $attempt->saved_at->toIso8601String()]);
        }

        return back()->with('status', 'Answers saved. You can return to this assessment later.');
    }

    public function submit(Request $request, Activity $activity, Learning $learning)
    {
        $learning->authorize($request->user(), $activity);
        $learning->save($request->user(), $activity, $this->answers($request), true);

        return redirect()->route('learning.activity', $activity)->with('status', 'Assessment submitted. Your result has been recorded.');
    }
}
