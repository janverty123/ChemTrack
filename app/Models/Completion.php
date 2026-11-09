<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Completion extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'activity_id', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }
}
