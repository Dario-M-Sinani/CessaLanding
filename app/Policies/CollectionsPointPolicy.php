<?php

namespace App\Policies;

use App\Models\CollectionsPoint;
use App\Models\User;

class CollectionsPointPolicy
{
    // Igual que en el legacy (Rcadmin\CollectionsPointsController::isAuthorized):
    // solo ADMIN/SYSTEM administran Puntos de Cobranza, PUBLICATIONS nunca tuvo acceso.
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function view(User $user, CollectionsPoint $collectionsPoint): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function update(User $user, CollectionsPoint $collectionsPoint): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function delete(User $user, CollectionsPoint $collectionsPoint): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }
}
