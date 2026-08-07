<?php

namespace App\Policies;

use App\Models\Bank;
use App\Models\User;

class BankPolicy
{
    // Igual que en el legacy (Rcadmin\BanksController::isAuthorized): solo
    // ADMIN/SYSTEM administran Bancos, PUBLICATIONS nunca tuvo acceso.
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function view(User $user, Bank $bank): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function update(User $user, Bank $bank): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function delete(User $user, Bank $bank): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }
}
