<?php

namespace App\Services\Payments\DataTransferObjects;

use DateTimeInterface;

final readonly class QrPaymentResult
{
    public function __construct(
        public string $qrImageBase64,
        public string $providerQrId,
        public string $providerTransactionId,
        public DateTimeInterface $expiresAt,
        public string $destinationBank,
        public string $destinationAccount,
    ) {
    }
}
