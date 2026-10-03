<?php

namespace App\Services\Payments;

use App\Models\Recibo;
use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\Exceptions\QrPaymentException;

/**
 * Registro de las pasarelas de QR disponibles (SIP/BISA y BNB hoy). Existe porque, con más de
 * un banco, el proveedor ya no se puede resolver de forma global: al inhabilitar o consultar un
 * QR hay que usar el MISMO banco con el que se generó (guardado en Recibo::provider), no siempre
 * SIP. El controller de generación elige por la key que mande el cliente; el resto de los
 * consumidores (comando de expiración, acciones del panel) resuelven por el recibo.
 */
class PaymentProviderRegistry
{
    /** @var array<string, QrPaymentProviderInterface> */
    private array $providers = [];

    // La key técnica ('sip_bisa'/'bnb', la que se guarda en Recibo::provider) mapeada al nombre
    // que ve el cliente al elegir. Solo se ofrecen para elegir las keys que estén acá.
    private const ETIQUETAS = [
        'sip_bisa' => 'Banco BISA',
        'bnb' => 'Banco Nacional de Bolivia (BNB)',
    ];

    /**
     * @param  iterable<QrPaymentProviderInterface>  $providers
     */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->key()] = $provider;
        }
    }

    public function get(string $key): QrPaymentProviderInterface
    {
        return $this->providers[$key]
            ?? throw QrPaymentException::requestFailed($key, 'resolver proveedor', "No hay una pasarela de QR registrada con la key '{$key}'.");
    }

    public function forRecibo(Recibo $recibo): QrPaymentProviderInterface
    {
        return $this->get($recibo->provider);
    }

    public function has(string $key): bool
    {
        return isset($this->providers[$key]);
    }

    /**
     * Nombre visible del banco para una key técnica ('sip_bisa'/'bnb'), o la propia key si no
     * tiene etiqueta.
     */
    public function label(string $key): string
    {
        return self::ETIQUETAS[$key] ?? $key;
    }

    /**
     * Alterna el banco a usar según el último banco usado, para repartir las generaciones entre
     * los dos bancos sin que el usuario elija (1º BISA, 2º BNB, 3º BISA, ...). Con `$ultimo` nulo
     * (primera vez) arranca por BISA. Si en el futuro hay más de dos bancos, esto se puede
     * generalizar a un round-robin sobre `selectableKeys()`.
     */
    public function rotateNext(?string $ultimo): string
    {
        return $ultimo === 'sip_bisa' ? 'bnb' : 'sip_bisa';
    }

    /**
     * Keys elegibles por el cliente (las que además tienen etiqueta).
     *
     * @return list<string>
     */
    public function selectableKeys(): array
    {
        return array_values(array_filter(
            array_keys($this->providers),
            fn (string $key) => isset(self::ETIQUETAS[$key]),
        ));
    }

    /**
     * Opciones {key, label} para el selector de banco del frontend.
     *
     * @return list<array{key: string, label: string}>
     */
    public function options(): array
    {
        return array_map(
            fn (string $key) => ['key' => $key, 'label' => self::ETIQUETAS[$key]],
            $this->selectableKeys(),
        );
    }
}
