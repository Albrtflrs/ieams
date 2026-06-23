<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IncomeTransaction;

class IncomePolicy
{
    public function viewAny(?User $user): bool
    {
        if (!$user) return false;
        // ✅ Allow super_admin, admin, manager, staff, viewer
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    public function view(?User $user, IncomeTransaction $income): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        if (!$user) return false;
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff']);
    }

    public function update(?User $user, IncomeTransaction $income): bool
    {
        if (!$user) return false;
        if (in_array($user->role, ['super_admin', 'admin', 'manager'])) {
            return true;
        }
        if ($user->role === 'staff' && $user->id === $income->created_by) {
            return true;
        }
        return false;
    }

    public function delete(?User $user, IncomeTransaction $income): bool
    {
        if (!$user) return false;
        return in_array($user->role, ['super_admin', 'admin', 'manager']);
    }
}