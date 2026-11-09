<?php

namespace App\Policies;

use App\Models\Programme;
use App\Models\User;

class ProgrammePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'teacher';
    }

    public function create(User $user): bool
    {
        return $user->role === 'teacher';
    }

    public function view(User $user, Programme $programme): bool
    {
        return $user->role === 'teacher' && $programme->teacher_id === $user->id;
    }

    public function update(User $user, Programme $programme): bool
    {
        return $this->view($user, $programme) && ! $programme->hasStarted();
    }

    public function delete(User $user, Programme $programme): bool
    {
        return false;
    }
}
