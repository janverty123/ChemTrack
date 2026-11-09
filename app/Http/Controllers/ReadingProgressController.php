<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Programme;
use App\Models\ReadingProgress;
use App\Services\Learning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReadingProgressController extends Controller
{
    public function store(Request $request, Activity $activity, Learning $learning)
    {
        $learning->authorize($request->user(), $activity);
        $data = $request->validate([
            'version' => 'required|string|size:64', 'position' => 'required|numeric|between:0,100',
            'furthest_position' => 'sometimes|numeric|between:0,100',
            'current_page' => 'nullable|integer|min:1|max:500', 'page_count' => 'nullable|integer|min:1|max:500',
            'viewed_pages' => 'sometimes|array|max:500', 'viewed_pages.*' => 'integer|min:1|max:500',
        ]);
        $reading = DB::transaction(function () use ($request, $activity, $learning, $data) {
            Programme::whereKey($activity->programme_id)->lockForUpdate()->firstOrFail();
            $activity->refresh();
            $learning->authorize($request->user(), $activity);
            abort_unless($activity->type === 'lesson', 422);
            abort_unless(hash_equals($activity->contentVersion(), $data['version']), 409, 'This material changed. Reload before saving your reading location.');
            $reading = ReadingProgress::firstOrNew(['user_id' => $request->user()->id, 'activity_id' => $activity->id]);
            abort_if($reading->exists && $reading->content_version !== $data['version'], 409, 'Saved reading belongs to a different content version.');
            $pages = array_map('intval', $data['viewed_pages'] ?? []);
            $count = isset($data['page_count']) ? (int) $data['page_count'] : $reading->page_count;
            $page = isset($data['current_page']) ? (int) $data['current_page'] : $reading->current_page;
            if ((! $activity->document_path && ($count || $page || $pages)) || (($page || $pages) && ! $count) || ($count && ($page > $count || collect($pages)->contains(fn ($p) => $p > $count)))) {
                throw ValidationException::withMessages(['viewed_pages' => 'Report only pages within this PDF.']);
            }
            abort_if($reading->page_count && $count !== $reading->page_count, 409, 'The PDF page count changed. Reload this material.');
            $viewed = array_values(array_unique(array_merge($reading->viewed_pages ?? [], $pages)));
            sort($viewed, SORT_NUMERIC);
            $reading->fill([
                'content_version' => $data['version'], 'position' => (float) $data['position'],
                'furthest_position' => max((float) $reading->furthest_position, (float) $data['position'], (float) ($data['furthest_position'] ?? 0)),
                'current_page' => $page, 'page_count' => $count, 'viewed_pages' => $viewed, 'saved_at' => now(),
            ])->save();

            return $reading;
        }, 3);

        return response()->json([
            'saved_at' => $reading->saved_at->toIso8601String(), 'position' => $reading->position,
            'furthest_position' => $reading->furthest_position, 'current_page' => $reading->current_page,
            'page_count' => $reading->page_count, 'viewed_pages' => $reading->viewed_pages, 'progress' => $reading->progressPercent(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function document(Request $request, Activity $activity, Learning $learning)
    {
        $learning->authorize($request->user(), $activity);
        abort_unless($activity->type === 'lesson' && $activity->document_path && str_starts_with($activity->document_path, 'modules/') && ! str_contains($activity->document_path, '..'), 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($activity->document_path), 404);

        return response()->file($disk->path($activity->document_path), ['Content-Type' => 'application/pdf', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
