<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Programme extends Model
{
    protected $fillable = ['teacher_id', 'title', 'description', 'published', 'cycle_workflow'];

    protected function casts(): array
    {
        return ['published' => 'boolean', 'cycle_workflow' => 'boolean'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderBy('position')->orderBy('id');
    }

    public function hasStarted(): bool
    {
        $ids = $this->activities()->select('id');

        return Attempt::whereIn('activity_id', $ids)->exists() || Completion::whereIn('activity_id', $ids)->exists() || ReadingProgress::whereIn('activity_id', $ids)->exists();
    }

    public function ensureEditable(): void
    {
        static::whereKey($this->id)->lockForUpdate()->first();
        // Current reads must see learner work committed after an earlier REPEATABLE READ snapshot.
        $ids = Activity::where('programme_id', $this->id)->lockForUpdate()->pluck('id');
        $started = Attempt::whereIn('activity_id', $ids)->lockForUpdate()->first(['id'])
            || Completion::whereIn('activity_id', $ids)->lockForUpdate()->first(['id'])
            || ReadingProgress::whereIn('activity_id', $ids)->lockForUpdate()->first(['id']);
        if ($started) {
            throw ValidationException::withMessages(['title' => 'Learners have started this programme. Preserve its history by creating a new programme for revised content.']);
        }
    }

    protected static function booted(): void
    {
        static::updating(fn (self $programme) => $programme->ensureEditable());
        static::deleting(fn (self $programme) => $programme->ensureEditable());
    }
}
