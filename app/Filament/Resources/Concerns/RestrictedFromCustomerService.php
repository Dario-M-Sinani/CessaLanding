<?php

namespace App\Filament\Resources\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Atención al Cliente solo puede ver/atender Solicitudes -- nada más del panel.
 * Este trait bloquea navegación y acceso directo por URL a los demás recursos.
 */
trait RestrictedFromCustomerService
{
    protected static function staffCanAccess(): bool
    {
        return auth()->user()?->role !== User::ROLE_CUSTOMER_SERVICE;
    }

    public static function canViewAny(): bool
    {
        return static::staffCanAccess();
    }

    public static function canCreate(): bool
    {
        return static::staffCanAccess();
    }

    public static function canEdit(Model $record): bool
    {
        return static::staffCanAccess();
    }

    public static function canDelete(Model $record): bool
    {
        return static::staffCanAccess();
    }

    public static function canView(Model $record): bool
    {
        return static::staffCanAccess();
    }
}
