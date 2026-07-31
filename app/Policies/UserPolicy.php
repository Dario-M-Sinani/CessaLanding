<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // Igual que en el legacy (Rcadmin\UsersController::isAuthorized): la gestión de
    // usuarios y roles del propio panel es exclusiva del rol SYSTEM.
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_SYSTEM);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole(User::ROLE_SYSTEM);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_SYSTEM);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole(User::ROLE_SYSTEM);
    }

    public function delete(User $user, User $model): bool
    {
        // Nadie puede borrar su propio usuario desde el panel (evita quedarse sin acceso).
        return $user->hasRole(User::ROLE_SYSTEM) && $user->id !== $model->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_SYSTEM);
    }
}
