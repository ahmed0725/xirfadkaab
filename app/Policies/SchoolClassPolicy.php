<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;

class SchoolClassPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'user', 'teacher'], true);
    }

    public function view(User $user, SchoolClass $schoolClass): bool
    {
        if (in_array($user->role, ['admin', 'user'], true)) {
            return true;
        }

        return $user->role === 'teacher'
            && $user->teachingClasses()->whereKey($schoolClass->id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'user'], true);
    }

    public function update(User $user, SchoolClass $schoolClass): bool
    {
        return in_array($user->role, ['admin', 'user'], true);
    }

    public function delete(User $user, SchoolClass $schoolClass): bool
    {
        return $user->role === 'admin';
    }
}
