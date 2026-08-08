<?php

namespace App\Policies;

use App\Models\Recibo;
use App\Models\User;

// Cobros por QR (dinero real) -- exclusivo de ADMIN/SYSTEM, mismo criterio que Bancos/Reportes.
// Nadie puede borrar un Recibo (se inhabilita, no se elimina -- hay que conservar el historial).
class ReciboPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function view(User $user, Recibo $recibo): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function update(User $user, Recibo $recibo): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
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
