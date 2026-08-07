<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Video;

class VideoPolicy
{
    // Igual que en el legacy (Rcadmin\VideosController::isAuthorized): solo
    // ADMIN/SYSTEM administran Videos, PUBLICATIONS nunca tuvo acceso.
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function view(User $user, Video $video): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function update(User $user, Video $video): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function delete(User $user, Video $video): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SYSTEM);
    }
}
