<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ExpenseTransaction;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    public function view(User $user, ExpenseTransaction $expense): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff']);
    }

    public function update(User $user, ExpenseTransaction $expense): bool
    {
        if (in_array($user->role, ['super_admin', 'admin', 'manager'])) {
            return true;
        }
        if ($user->role === 'staff' && $user->id === $expense->created_by) {
            return true;
        }
        return false;
    }

    public function delete(User $user, ExpenseTransaction $expense): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager']);
    }
}