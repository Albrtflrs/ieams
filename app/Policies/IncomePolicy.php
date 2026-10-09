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
        return $user->hasRole(['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    // ─── View single ──────────────────────────────────────────
    public function view(?User $user, IncomeTransaction $income): bool
    {
        if (!$this->viewAny($user)) return false;
        return !$user->hasRole(['staff', 'viewer']) || $income->created_by === $user->id;
    }

    // ─── Create ────────────────────────────────────────────────
    public function create(?User $user): bool
    {
        if (!$user) return false;
        return $user->hasRole(['super_admin', 'admin']);
    }

    // ─── Update ────────────────────────────────────────────────
    public function update(?User $user, IncomeTransaction $income): bool
    {
        if (!$user) return false;
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }
        return false;
    }

    // ─── Delete (soft) ─────────────────────────────────────────
    public function delete(?User $user, IncomeTransaction $income): bool
    {
        if (!$user) return false;
        return $user->hasRole(['super_admin', 'admin']);
    }

    // ─── NEW: View trash ──────────────────────────────────────
    public function viewTrash(?User $user): bool
    {
        if (!$user) return false;
        return $user->hasRole(['super_admin', 'admin']);
    }

    // ─── NEW: Restore ──────────────────────────────────────────
    public function restore(?User $user, IncomeTransaction $income): bool
    {
        if (!$user) return false;
        return $user->hasRole(['super_admin', 'admin']);
    }

    // ─── NEW: Force delete (permanent) ────────────────────────
    public function forceDelete(?User $user, IncomeTransaction $income): bool
    {
        if (!$user) return false;
        return $user->hasRole(['super_admin', 'admin']);
    }
}