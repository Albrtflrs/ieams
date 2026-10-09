<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MiscellaneousTransaction;

class MiscellaneousPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    public function view(User $user, MiscellaneousTransaction $misc): bool
    {
        return $this->viewAny($user)
            && (!$user->hasRole(['staff', 'viewer']) || $misc->created_by === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    public function update(User $user, MiscellaneousTransaction $misc): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    public function delete(User $user, MiscellaneousTransaction $misc): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }
}