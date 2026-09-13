<?php

namespace App\Policies;

use App\Models\Recibo;
use App\Models\User;

class ReciboPolicy
{
    // Cobros QR maneja dinero real (genera QR, ve datos del pagador, exporta a Excel) --
    // exclusivo del rol SYSTEM, igual que UserPolicy. ADMIN ya no debe verlo.
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_SYSTEM);
    }

    public function view(User $user, Recibo $recibo): bool
    {
        return $user->hasRole(User::ROLE_SYSTEM);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_SYSTEM);
    }

    public function update(User $user, Recibo $recibo): bool
    {
        return $user->hasRole(User::ROLE_SYSTEM);
    }

    public function delete(User $user, Recibo $recibo): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
