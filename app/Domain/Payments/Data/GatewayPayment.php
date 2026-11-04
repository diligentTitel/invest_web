<?php

namespace App\Domain\Payments\Data;

final readonly class GatewayPayment
{
    public function __construct(
        public string $providerPaymentId,
        public string $paymentUrl,
        public string $status,
        public ?string $expiresAt,
        public array $providerMetadata = [],
    ) {}
}