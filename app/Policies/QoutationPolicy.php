<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quotation;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    public function view(User $user, Quotation $quotation): bool
    {
        if ($user->role === 'staff' || $user->role === 'viewer') {
            return $user->id === $quotation->created_by;
        }
        return in_array($user->role, ['super_admin', 'admin', 'manager']);
    }

    // ✅ Staff can NOW create quotations
    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff']);
    }

    // ✅ Staff can update their own draft quotations
    public function update(User $user, Quotation $quotation): bool
    {
        if ($user->role === 'staff') {
            return $user->id === $quotation->created_by && $quotation->status === 'draft';
        }
        return in_array($user->role, ['super_admin', 'admin', 'manager']);
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        if ($user->role === 'staff') {
            return $user->id === $quotation->created_by && $quotation->status === 'draft';
        }
        return in_array($user->role, ['super_admin', 'admin']);
    }

    public function convertToIncome(User $user, Quotation $quotation): bool
    {
        if ($quotation->status !== 'accepted' || $quotation->converted_to_income_id) {
            return false;
        }
        return in_array($user->role, ['super_admin', 'admin', 'manager']);
    }
}