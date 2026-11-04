<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlockchainNetwork extends Model
{
    protected $fillable = ['code', 'name', 'chain_identifier', 'address_format', 'required_confirmations', 'is_enabled'];

    protected function casts(): array
    {
        return ['required_confirmations' => 'integer', 'is_enabled' => 'boolean'];
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(CryptoWallet::class, 'network_id');
    }

    public function depositRequests(): HasMany
    {
        return $this->hasMany(DepositRequest::class, 'network_id');
    }
}