<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    // Igual que en el legacy (Rcadmin\NewsController::isAuthorized): solo
    // ADMIN/SYSTEM administran Noticias, PUBLICATIONS nunca tuvo acceso.
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function view(User $user, News $news): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function update(User $user, News $news): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function delete(User $user, News $news): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }
}
