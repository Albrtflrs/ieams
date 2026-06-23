<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MiscellaneousTransaction;

class MiscellaneousPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    public function view(User $user, MiscellaneousTransaction $misc): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff']);
    }

    public function update(User $user, MiscellaneousTransaction $misc): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager']);
    }

    public function delete(User $user, MiscellaneousTransaction $misc): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager']);
    }
}