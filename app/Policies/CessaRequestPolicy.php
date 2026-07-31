<?php

namespace App\Policies;

use App\Models\CessaRequest;
use App\Models\User;

class CessaRequestPolicy
{
    // Igual que en el legacy (Rcadmin\RequestsController::isAuthorized): las solicitudes
    // traen fotos de C.I., factura y datos personales, así que solo el rol de atención al
    // cliente puede verlas o gestionarlas -- ni siquiera ADMIN/SYSTEM tienen acceso acá.
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_CUSTOMER_SERVICE);
    }

    public function view(User $user, CessaRequest $cessaRequest): bool
    {
        return $user->hasRole(User::ROLE_CUSTOMER_SERVICE);
    }

    public function create(User $user): bool
    {
        // Igual que el legacy: las solicitudes solo se crean desde el portal público,
        // nunca manualmente desde el panel.
        return false;
    }

    public function update(User $user, CessaRequest $cessaRequest): bool
    {
        return $user->hasRole(User::ROLE_CUSTOMER_SERVICE);
    }

    public function delete(User $user, CessaRequest $cessaRequest): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
