<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammeInvitation extends Model
{
    protected $fillable = ['programme_id', 'code', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function url(): string
    {
        return url('/join').'?'.http_build_query(['code' => $this->code]);
    }
}
