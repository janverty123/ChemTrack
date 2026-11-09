<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Activity extends Model
{
    protected $fillable = ['programme_id', 'title', 'type', 'position', 'content', 'video_url', 'questions', 'cycle', 'document_path', 'assessment_source', 'google_form_url'];

    protected $hidden = ['questions'];

    protected function casts(): array
    {
        return ['questions' => 'array', 'cycle' => 'integer'];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $activity) {
            $activity->programme->ensureEditable();
            validator($activity->getAttributes(), [
                'type' => 'required|in:lesson,pre_test,checkpoint,video,post_test',
                'cycle' => 'nullable|integer|in:1,2',
                'video_url' => 'nullable|url:https|max:2048',
                'assessment_source' => 'sometimes|in:native,google_forms',
                'google_form_url' => ['nullable', 'url:https', 'max:2048', 'regex:~^https://(?:docs\.google\.com/forms/|forms\.gle/)~'],
                'document_path' => ['nullable', 'max:255', 'regex:~^modules/[a-zA-Z0-9_./-]+\.pdf$~'],
            ])->validate();
            if ($activity->document_path && (str_contains($activity->document_path, '..') || $activity->type !== 'lesson')) {
                throw ValidationException::withMessages(['document_path' => 'Choose a private module PDF for a reading part.']);
            }
            if ($activity->assessment_source === 'google_forms' && ! in_array($activity->type, ['pre_test', 'post_test'], true)) {
                throw ValidationException::withMessages(['assessment_source' => 'Google Forms is available for the research pre-test and post-test.']);
            }
            if (! in_array($activity->type, ['lesson', 'video']) && $activity->assessment_source !== 'google_forms') {
                validator(['questions' => $activity->questions], [
                    'questions' => 'required|array|min:1|max:100',
                    'questions.*.prompt' => 'required|string|max:2000',
                    'questions.*.options' => 'required|array|min:2|max:5',
                    'questions.*.options.*' => 'required|string|max:1000',
                    'questions.*.correct' => 'required|integer|min:0',
                ])->validate();
                foreach ($activity->questions as $question) {
                    if (! array_key_exists((int) $question['correct'], $question['options'])) {
                        throw ValidationException::withMessages(['questions' => 'The correct option must exist.']);
                    }
                }
            }
        });
        static::deleting(fn (self $activity) => $activity->programme->ensureEditable());
    }

    public function publicQuestions(): array
    {
        return array_map(fn ($q) => ['prompt' => $q['prompt'], 'options' => $q['options']], $this->questions ?? []);
    }

    public function contentVersion(): string
    {
        $fileHash = $this->document_path && Storage::disk('local')->exists($this->document_path)
            ? hash_file('sha256', Storage::disk('local')->path($this->document_path)) : '';

        return hash('sha256', ($this->content ?? '').'|'.($this->document_path ?? '').'|'.$fileHash);
    }
}
