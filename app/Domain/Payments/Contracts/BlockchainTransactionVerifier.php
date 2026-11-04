<?php

namespace App\Domain\Payments\Contracts;

use App\Domain\Payments\Data\BlockchainVerificationResult;

interface BlockchainTransactionVerifier
{
    public function verify(
        string $networkCode,
        string $transactionHash,
        string $destinationAddress,
        string $assetCode,
        string $expectedAmount,
        int $requiredConfirmations,
    ): BlockchainVerificationResult;
}