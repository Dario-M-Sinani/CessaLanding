<?php

namespace App\Services\Payments\DataTransferObjects;

use App\Services\Payments\PaymentStatus;
use DateTimeInterface;

final readonly class QrPaymentStatusResult
{
    public function __construct(
        public string $alias,
        public PaymentStatus $status,
        public ?DateTimeInterface $processedAt = null,
        public ?string $providerOrderNumber = null,
        public ?float $amount = null,
        public ?string $providerQrId = null,
        public ?string $currency = null,
        public ?string $payerAccount = null,
        public ?string $payerName = null,
        public ?string $payerDocument = null,
    ) {
    }
}
