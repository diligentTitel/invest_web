<?php

namespace App\Domain\Operations;

use LogicException;

class LiveOperationsGuard
{
    public function assertEnabled(string $operation): void
    {
        if (! config('platform.live_operations_enabled')) {
            throw new LogicException('Live '.$operation.' operations are disabled until legal, compliance, and provider readiness is approved.');
        }
    }

    public function isDemoEnvironment(): bool
    {
        return (bool) config('platform.demo_mode');
    }
}