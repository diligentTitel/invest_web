<?php

namespace App\Models;

use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulatedPosition extends Model
{
    protected $fillable = [
        'user_id', 'investment_asset_id', 'quantity', 'average_cost', 'cost_basis',
        'realized_pnl', 'manual_price', 'manual_price_updated_at',
    ];

    protected function casts(): array
    {
        return ['manual_price_updated_at' => 'datetime', 'manual_metrics' => 'array'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(InvestmentAsset::class, 'investment_asset_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasQuantity(): bool
    {
        return BigDecimal::of($this->quantity)->isGreaterThan(BigDecimal::zero());
    }
}