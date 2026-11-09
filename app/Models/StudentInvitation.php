<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentInvitation extends Model
{
    protected $fillable = ['teacher_id', 'programme_id', 'token_hash', 'expires_at', 'used_at', 'used_by'];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime'];
    }
}
