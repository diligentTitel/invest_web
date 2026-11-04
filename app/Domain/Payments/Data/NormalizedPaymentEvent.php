<?php

namespace App\Domain\Payments\Data;

final readonly class NormalizedPaymentEvent
{
    public function __construct(
        public string $eventId,
        public string $providerPaymentId,
        public string $status,
        public string $currencyCode,
        public string $amount,
        public ?string $transactionHash,
        public ?string $networkCode,
        public ?string $destinationAddress,
        public array $metadata = [],
    ) {}
}