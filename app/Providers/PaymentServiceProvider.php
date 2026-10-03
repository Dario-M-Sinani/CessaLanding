<?php

namespace App\Providers;

use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\PaymentProviderRegistry;
use App\Services\Payments\Providers\BnbQrProvider;
use App\Services\Payments\Providers\SipQrProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Arma los proveedores de QR de pago disponibles (SIP/BISA y BNB) y el registro que los
 * resuelve por key. Para sumar otro banco:
 * 1) crear la clase que implemente QrPaymentProviderInterface (ver SipQrProvider/BnbQrProvider),
 * 2) registrar su singleton acá y sumarlo al PaymentProviderRegistry,
 * 3) darle una etiqueta en PaymentProviderRegistry si debe poder elegirlo el cliente.
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

        $this->app->singleton('qr.provider.bnb', function () {
            return new BnbQrProvider(
                baseUrl: rtrim(config('services.bnb.base_url') ?: 'http://test.bnb.com.bo', '/')
                    .(filled(config('services.bnb.uri_subfolder')) ? '/'.trim(config('services.bnb.uri_subfolder'), '/') : ''),
                accountId: (string) config('services.bnb.account_id'),
                authorizationId: (string) config('services.bnb.authorization_id'),
                currency: config('services.bnb.currency') ?: 'BOB',
                singleUse: (bool) config('services.bnb.single_use'),
                destinationAccountId: (int) config('services.bnb.destination_account_id'),
            );
        });

        $this->app->singleton(PaymentProviderRegistry::class, function ($app) {
            return new PaymentProviderRegistry([
                $app->make('qr.provider.sip_bisa'),
                $app->make('qr.provider.bnb'),
            ]);
        });

        // Compat: el contrato genérico (sin key) resuelve al banco por defecto (SIP/BISA), para
        // el código que todavía no distingue banco. Los consumidores que sí deben respetar el
        // banco de cada recibo usan PaymentProviderRegistry::forRecibo().
        $this->app->bind(QrPaymentProviderInterface::class, function ($app) {
            return $app->make('qr.provider.sip_bisa');
        });
    }
}
