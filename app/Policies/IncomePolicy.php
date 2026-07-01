<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IncomeTransaction;

class IncomePolicy
{
    // ─── View any (index) ────────────────────────────────────
    public function viewAny(?User $user): bool
    {
        if (!$user) return false;
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    // ─── View single ──────────────────────────────────────────
    public function view(?User $user, IncomeTransaction $income): bool
    {
        return $this->viewAny($user);
    }

    // ─── Create ────────────────────────────────────────────────
    public function create(?User $user): bool
    {
        if (!$user) return false;
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff']);
    }

    // ─── Update ────────────────────────────────────────────────
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

    // ─── Delete (soft) ─────────────────────────────────────────
    public function delete(?User $user, IncomeTransaction $income): bool
    {
        if (!$user) return false;
        return in_array($user->role, ['super_admin', 'admin', 'manager']);
    }

    // ─── NEW: View trash ──────────────────────────────────────
    public function viewTrash(?User $user): bool
    {
        if (!$user) return false;
        return in_array($user->role, ['super_admin', 'admin']);
    }

    // ─── NEW: Restore ──────────────────────────────────────────
    public function restore(?User $user, IncomeTransaction $income): bool
    {
        if (!$user) return false;
        return in_array($user->role, ['super_admin', 'admin']);
    }

    // ─── NEW: Force delete (permanent) ────────────────────────
    public function forceDelete(?User $user, IncomeTransaction $income): bool
    {
        if (!$user) return false;
        return in_array($user->role, ['super_admin', 'admin']);
    }
}