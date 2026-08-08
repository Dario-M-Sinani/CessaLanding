<?php

namespace App\Policies;

use App\Models\ClientContactUpdate;
use App\Models\User;

// Contactos (email/celular) que un abonado confirmó por su cuenta -- datos personales, mismo
// criterio que CessaRequestPolicy: solo Atención al Cliente los ve. Es solo lectura: el
// registro nace ya verificado desde el portal público, nadie lo crea/edita a mano acá; se
// exporta a CSV para migrarlo después a otro sistema (ver ClientContactUpdateResource).
class ClientContactUpdatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_CUSTOMER_SERVICE);
    }

    public function view(User $user, ClientContactUpdate $clientContactUpdate): bool
    {
        return $user->hasRole(User::ROLE_CUSTOMER_SERVICE);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ClientContactUpdate $clientContactUpdate): bool
    {
        return false;
    }

    public function delete(User $user, ClientContactUpdate $clientContactUpdate): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
