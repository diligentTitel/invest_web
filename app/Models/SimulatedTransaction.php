<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulatedTransaction extends Model
{
    protected $fillable = [
        'user_id', 'investment_asset_id', 'side', 'quantity', 'unit_price', 'fees',
        'realized_pnl', 'price_source', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(InvestmentAsset::class, 'investment_asset_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}