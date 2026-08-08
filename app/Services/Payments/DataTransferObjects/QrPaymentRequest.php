<?php

namespace App\Services\Payments\DataTransferObjects;

use DateTimeInterface;

final readonly class QrPaymentRequest
{
    public function __construct(
        public string $alias,
        public float $amount,
        public string $currency,
        public string $description,
        public DateTimeInterface $expiresAt,
        public string $callbackUrl,
        public bool $singleUse = true,
    ) {
    }
}
