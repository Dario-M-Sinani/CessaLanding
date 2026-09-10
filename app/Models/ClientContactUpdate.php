<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Datos de contacto que un abonado confirmó por su cuenta en la página pública "Actualizar
 * Datos", ya verificado con un código de un solo uso enviado por correo. SIIC no expone un
 * endpoint de escritura, así que esta tabla es la lista que Atención al Cliente revisa para
 * aplicar el cambio ahí manualmente (ver App\Http\Controllers\ActualizarDatosController y
 * App\Filament\Resources\ClientContactUpdateResource). `phone` se pide y se guarda, pero NO
 * se verifica por SMS -- solo el correo lleva código de un solo uso (columna nullable por si
 * en algún momento se reintroduce un flujo que no lo pida).
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
