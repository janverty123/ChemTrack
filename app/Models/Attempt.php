<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attempt extends Model
{
    protected $fillable = ['user_id', 'activity_id', 'answers', 'score', 'started_at', 'saved_at', 'submitted_at'];

    protected function casts(): array
    {
        return ['answers' => 'array', 'score' => 'integer', 'started_at' => 'datetime', 'saved_at' => 'datetime', 'submitted_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
