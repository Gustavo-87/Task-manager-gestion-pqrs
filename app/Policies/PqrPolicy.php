<?php

namespace App\Policies;

use App\Models\Pqr;
use App\Models\User;

class PqrPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pqr $pqr): bool
    {
        return $user->canViewAllPqrs() || $pqr->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Pqr $pqr): bool
    {
        return $user->canManagePqrs() && ($user->role !== 'apoyo' || ! $pqr->assigned_to_id || $pqr->assigned_to_id === $user->id);
    }

    public function delete(User $user, Pqr $pqr): bool
    {
        return in_array($user->role, ['admin','gestor'], true);
    }
}
