<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin']);
    }

    public function view(User $user, User $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, User $model): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }
        if ($user->role === 'admin' && $model->role !== 'super_admin') {
            return true;
        }
        return false;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->role === 'super_admin' && $user->id !== $model->id) {
            return true;
        }
        return false;
    }
}