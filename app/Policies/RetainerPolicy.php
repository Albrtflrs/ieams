<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Retainer;

class RetainerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    public function view(User $user, Retainer $retainer): bool
    {
        return $this->viewAny($user)
            && (!$user->hasRole(['staff', 'viewer']) || $retainer->created_by === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    public function update(User $user, Retainer $retainer): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Retainer $retainer): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    // ─── Trash / Restore / Force Delete ──────────────────────────────
    public function viewTrash(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function restore(User $user, Retainer $retainer): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function forceDelete(User $user, Retainer $retainer): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }
}