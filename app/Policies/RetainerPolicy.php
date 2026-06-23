<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Retainer;

class RetainerPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    public function view(User $user, Retainer $retainer): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff']);
    }

    public function update(User $user, Retainer $retainer): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Retainer $retainer): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager']);
    }
}