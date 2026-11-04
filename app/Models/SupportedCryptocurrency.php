<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportedCryptocurrency extends Model
{
    protected $fillable = ['code', 'name', 'precision', 'is_enabled'];

    protected function casts(): array
    {
        return ['precision' => 'integer', 'is_enabled' => 'boolean'];
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(CryptoWallet::class, 'cryptocurrency_id');
    }

    public function depositRequests(): HasMany
    {
        return $this->hasMany(DepositRequest::class, 'cryptocurrency_id');
    }
}