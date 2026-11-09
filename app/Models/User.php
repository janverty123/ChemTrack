<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;

#[Fillable(['name', 'email', 'password', 'role', 'teacher_id', 'student_code', 'category', 'private_identity', 'identity_hash', 'account_completed_at', 'registration_required', 'preferences', 'avatar_path', 'school', 'designation'])]
#[Hidden(['password', 'remember_token', 'private_identity', 'identity_hash', 'pending_email', 'pending_email_token_hash', 'pending_email_expires_at'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use InteractsWithAppAuthentication, InteractsWithAppAuthenticationRecovery;

    public function studentReference(): string
    {
        return $this->student_code ?: 'Learner '.$this->id;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return auth()->id() === $this->id && $this->avatar_path ? route('settings.avatar') : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'teacher';
    }

    public function programmes(): BelongsToMany
    {
        return $this->belongsToMany(Programme::class)->withTimestamps();
    }

    protected static function booted(): void
    {
        static::updating(function (self $user) {
            if ($user->getOriginal('student_code') && $user->isDirty('student_code')) {
                throw ValidationException::withMessages(['student_code' => 'The assigned student code is permanent.']);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'private_identity' => 'encrypted:array',
            'account_completed_at' => 'datetime',
            'registration_required' => 'boolean',
            'preferences' => 'array',
            'pending_email' => 'encrypted',
            'pending_email_expires_at' => 'datetime',
        ];
    }
}
