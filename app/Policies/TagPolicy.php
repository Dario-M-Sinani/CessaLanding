<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    // Igual que en el legacy (Rcadmin\TagsController::isAuthorized): solo
    // ADMIN/SYSTEM administran Tags, PUBLICATIONS nunca tuvo acceso.
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }
}
