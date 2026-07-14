<?php

namespace App\Policies;

use App\Models\Pqr;
use App\Models\User;

class PqrPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['admin', 'residente'], true);
    }

    public function view(User $user, Pqr $pqr): bool
    {
        return $user->rol === 'admin' || $pqr->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->rol, ['admin', 'residente'], true);
    }

    public function update(User $user, Pqr $pqr): bool
    {
        return $user->rol === 'admin';
    }

    public function delete(User $user, Pqr $pqr): bool
    {
        return $user->rol === 'admin';
    }

    public function respond(User $user, Pqr $pqr): bool
    {
        return $user->rol === 'admin' && blank($pqr->respuesta);
    }

    public function updateResponse(User $user, Pqr $pqr): bool
    {
        return $user->rol === 'admin' && filled($pqr->respuesta);
    }
}
