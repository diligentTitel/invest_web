<?php

namespace App\Domain\Payments\Data;

final readonly class BlockchainVerificationResult
{
    public function __construct(
        public bool $verified,
        public string $status,
        public int $confirmations,
        public ?string $observedAmount = null,
        public ?string $destinationAddress = null,
        public ?string $assetCode = null,
        public ?string $failureReason = null,
        public ?string $source = null,
    ) {}
}