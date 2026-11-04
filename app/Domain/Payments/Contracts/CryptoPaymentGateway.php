<?php

namespace App\Domain\Payments\Contracts;

use App\Domain\Payments\Data\GatewayPayment;
use App\Domain\Payments\Data\NormalizedPaymentEvent;
use App\Models\DepositRequest;
use Illuminate\Http\Request;

interface CryptoPaymentGateway
{
    public function createPayment(DepositRequest $deposit): GatewayPayment;

    public function verifyWebhook(Request $request): bool;

    public function normalizeWebhook(array $payload): NormalizedPaymentEvent;
}