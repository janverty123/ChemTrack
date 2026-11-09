<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignupChallenge extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $hidden = ['email', 'session_hash', 'code_hash'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime', 'expires_at' => 'datetime', 'verified_at' => 'datetime', 'consumed_at' => 'datetime'];
    }
}
