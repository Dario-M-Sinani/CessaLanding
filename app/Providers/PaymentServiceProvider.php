<?php

namespace App\Providers;

use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\Providers\SipQrProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Arma los proveedores de QR de pago disponibles. Hoy solo hay uno (SIP/BISA), atado acá al
 * contrato genérico QrPaymentProviderInterface -- para sumar otro banco más adelante:
 * 1) crear la clase que implemente QrPaymentProviderInterface (ver SipQrProvider como ejemplo),
 * 2) registrarla acá con su propia key (ej. 'qr.provider.otro_banco'),
 * 3) usar esa key al armar/consultar un Recibo (columna `provider`) en vez de asumir SIP siempre.
 */
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('qr.provider.sip_bisa', function () {
            return new SipQrProvider(
                baseUrl: rtrim(config('services.sip.base_url'), '/'),
                apikey: config('services.sip.apikey'),
                username: config('services.sip.username'),
                password: config('services.sip.password'),
                apikeyServicio: config('services.sip.apikey_servicio'),
            );
        });

        // Mientras exista un solo banco integrado, el contrato genérico resuelve directo a SIP.
        // El día que haya más de uno, esto pasa a resolverse por un parámetro (ej. desde dónde
        // se generó el cobro) en vez de un binding fijo.
        $this->app->bind(QrPaymentProviderInterface::class, function ($app) {
            return $app->make('qr.provider.sip_bisa');
        });
    }
}
