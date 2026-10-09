<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ExpenseTransaction;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    public function view(User $user, ExpenseTransaction $expense): bool
    {
        return $this->viewAny($user)
            && (!$user->hasRole(['staff', 'viewer']) || $expense->created_by === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function update(User $user, ExpenseTransaction $expense): bool
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }
        return false;
    }

    public function delete(User $user, ExpenseTransaction $expense): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    // ─── Trash / Restore / Force Delete ──────────────────────────────
    public function viewTrash(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function restore(User $user, ExpenseTransaction $expense): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function forceDelete(User $user, ExpenseTransaction $expense): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }
}