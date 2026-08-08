<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Datos de contacto (email/teléfono) que un abonado confirmó por su cuenta en la página
 * pública "Actualizar Datos", ya verificados por doble código (email + SMS). SIIC no expone
 * un endpoint de escritura, así que esta tabla es la lista que Atención al Cliente revisa
 * para aplicar el cambio ahí manualmente (ver App\Http\Controllers\ActualizarDatosController
 * y App\Filament\Resources\ClientContactUpdateResource).
 */
class ClientContactUpdate extends Model
{
    protected $fillable = [
        'nro_cliente',
        'cuenta',
        'client_name',
        'email',
        'phone',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];
}
