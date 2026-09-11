<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Sin esto, Filament (Authenticate middleware) solo deja entrar al panel cuando
    // APP_ENV=local -- fuera de local (test/producción) tira 403 para cualquier
    // usuario autenticado, sin importar el rol. El control fino de qué ve cada rol
    // ya lo hacen las Policies de cada Resource (ver §3.5/§3.34 más abajo en este
    // documento), así que acá alcanza con exigir un rol válido.
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, [
            self::ROLE_SYSTEM,
            self::ROLE_ADMIN,
            self::ROLE_PUBLICATIONS,
            self::ROLE_CUSTOMER_SERVICE,
        ], true);
    }

    // Mismos roles que el panel legacy (Rcadmin).
    public const ROLE_SYSTEM = 'SYSTEM';
    public const ROLE_ADMIN = 'ADMIN';
    public const ROLE_PUBLICATIONS = 'PUBLICATIONS';
    public const ROLE_CUSTOMER_SERVICE = 'CUSTOMER_SERVICE';

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
