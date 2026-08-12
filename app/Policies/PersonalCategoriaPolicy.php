<?php

namespace App\Policies;

use App\Models\PersonalCategoria;
use App\Models\User;

// Mismo criterio que PersonalPolicy: solo ADMIN/SYSTEM administra las categorías del
// directorio de Personal (Autorizado / Externo Cortes y Reconexiones / etc).
class PersonalCategoriaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function view(User $user, PersonalCategoria $personalCategoria): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function update(User $user, PersonalCategoria $personalCategoria): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function delete(User $user, PersonalCategoria $personalCategoria): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }
}
