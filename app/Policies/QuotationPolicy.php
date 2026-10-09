<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quotation;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function convertToIncome(User $user, Quotation $quotation): bool
    {
        if ($quotation->status !== 'accepted' || $quotation->converted_to_income_id) {
            return false;
        }
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    // ─── Trash / Restore / Force Delete ──────────────────────────────
    public function viewTrash(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function restore(User $user, Quotation $quotation): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function forceDelete(User $user, Quotation $quotation): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }
}