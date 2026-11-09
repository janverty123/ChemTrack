<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'teacher';
    }

    public function create(User $user): bool
    {
        return $user->role === 'teacher';
    }

    public function view(User $user, User $student): bool
    {
        return $user->role === 'teacher' && $student->teacher_id === $user->id && $student->role === 'student';
    }

    public function update(User $user, User $student): bool
    {
        return $this->view($user, $student);
    }

    public function delete(User $user, User $student): bool
    {
        return false;
    }
}
