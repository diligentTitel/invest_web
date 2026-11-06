<?php

namespace App\Domain\Investments;

use App\Models\InvestmentAsset;
use App\Models\SimulatedPosition;
use App\Models\SimulatedTransaction;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SimulatedPortfolioService
{
    public function recordTrade(User $user, InvestmentAsset $asset, array $data): SimulatedTransaction
    {
        return DB::transaction(function () use ($user, $asset, $data): SimulatedTransaction {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $position = SimulatedPosition::query()
                ->where('user_id', $user->id)
                ->where('investment_asset_id', $asset->id)
                ->lockForUpdate()
                ->first();

            if (! $position && $data['side'] === 'sell') {
                throw ValidationException::withMessages(['quantity' => 'You cannot sell more than the quantity held.']);
            }

            if (! $position) {
                $position = SimulatedPosition::query()->create([
                    'user_id' => $user->id,
                    'investment_asset_id' => $asset->id,
                    'quantity' => '0',
                    'average_cost' => '0',
                    'cost_basis' => '0',
                    'realized_pnl' => '0',
                ]);
                $position = SimulatedPosition::query()->whereKey($position->id)->lockForUpdate()->firstOrFail();
            }

            $quantity = BigDecimal::of($data['quantity']);
            $unitPrice = BigDecimal::of($data['unit_price']);
            $fees = BigDecimal::of($data['fees'] ?? '0');
            $currentQuantity = BigDecimal::of($position->quantity);
            $currentCost = BigDecimal::of($position->cost_basis);
            $currentAverageCost = BigDecimal::of($position->average_cost);
            $realizedPnl = BigDecimal::zero();

            if ($data['side'] === 'buy') {
                $newQuantity = $currentQuantity->plus($quantity);
                $newCost = $currentCost->plus($quantity->multipliedBy($unitPrice))->plus($fees);
                $newAverageCost = $newCost->dividedBy($newQuantity, 18, RoundingMode::HALF_UP);
            } else {
                if ($quantity->isGreaterThan($currentQuantity)) {
                    throw ValidationException::withMessages(['quantity' => 'You cannot sell more than the quantity held.']);
                }

                $basis = $currentAverageCost->multipliedBy($quantity);
                $proceeds = $quantity->multipliedBy($unitPrice)->minus($fees);
                $realizedPnl = $proceeds->minus($basis);
                $newQuantity = $currentQuantity->minus($quantity);
                $newCost = $newQuantity->isZero() ? BigDecimal::zero() : $currentCost->minus($basis);
                $newAverageCost = $newQuantity->isZero()
                    ? BigDecimal::zero()
                    : $newCost->dividedBy($newQuantity, 18, RoundingMode::HALF_UP);
                $position->realized_pnl = BigDecimal::of($position->realized_pnl)->plus($realizedPnl)->toScale(18)->__toString();
            }

            $position->quantity = $newQuantity->toScale(18, RoundingMode::HALF_UP)->__toString();
            $position->cost_basis = $newCost->toScale(18, RoundingMode::HALF_UP)->__toString();
            $position->average_cost = $newAverageCost->toScale(18, RoundingMode::HALF_UP)->__toString();
            $position->save();

            return SimulatedTransaction::query()->create([
                'user_id' => $user->id,
                'investment_asset_id' => $asset->id,
                'side' => $data['side'],
                'quantity' => $quantity->toScale(18, RoundingMode::HALF_UP)->__toString(),
                'unit_price' => $unitPrice->toScale(18, RoundingMode::HALF_UP)->__toString(),
                'fees' => $fees->toScale(18, RoundingMode::HALF_UP)->__toString(),
                'realized_pnl' => $realizedPnl->toScale(18, RoundingMode::HALF_UP)->__toString(),
                'price_source' => 'manual',
                'occurred_at' => $data['occurred_at'] ?? now(),
            ]);
        });
    }
}