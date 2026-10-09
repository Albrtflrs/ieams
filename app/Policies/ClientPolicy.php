<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager', 'staff', 'viewer']);
    }

    public function view(User $user, Client $client): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    public function update(User $user, Client $client): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }
}