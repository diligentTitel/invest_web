<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentAsset extends Model
{
    protected $fillable = [
        'slug', 'name', 'symbol', 'category', 'asset_type', 'currency',
        'data_provider', 'provider_asset_id', 'description', 'details', 'is_active',
    ];

    protected function casts(): array
    {
        return ['details' => 'array', 'is_active' => 'boolean'];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(SimulatedPosition::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SimulatedTransaction::class);
    }
}