<?php

namespace App\Models;

use App\Services\Payments\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Comprobante interno de un cobro por QR -- registro propio de CESSA (monto, estado, quién
 * pagó), SIN valor fiscal ante el SIN. No confundir con una factura electrónica real.
 */
class Recibo extends Model
{
    protected $fillable = [
        'provider',
        'alias',
        'nro_cliente',
        'amount',
        'currency',
        'glosa',
        'descripcion_pago',
        'debt_items',
        'cobranzas_uuid',
        'comprobante_path',
        'facturado_at',
        'facturacion_intentos',
        'facturacion_error',
        'status',
        'expires_at',
        'qr_image_path',
        'provider_qr_id',
        'provider_transaction_id',
        'destination_bank',
        'destination_account',
        'provider_order_number',
        'payer_account',
        'payer_name',
        'payer_document',
        'paid_at',
        'callback_payload',
        'created_by_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => PaymentStatus::class,
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'callback_payload' => 'array',
        'debt_items' => 'array',
        'facturado_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
