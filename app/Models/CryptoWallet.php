<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoWallet extends Model
{
    protected $fillable = [
        'cryptocurrency_id', 'network_id', 'receiving_address_encrypted', 'address_fingerprint',
        'status', 'minimum_deposit', 'maximum_deposit', 'required_confirmations',
        'effective_from', 'effective_until',
    ];

    protected $hidden = ['receiving_address_encrypted', 'address_fingerprint'];

    protected function casts(): array
    {
        return [
            'receiving_address_encrypted' => 'encrypted',
            'minimum_deposit' => 'decimal:18',
            'maximum_deposit' => 'decimal:18',
            'required_confirmations' => 'integer',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CryptoWallet $wallet): void {
            $address = $wallet->receiving_address_encrypted;
            if (is_string($address) && trim($address) !== '') {
                $wallet->address_fingerprint = hash('sha256', trim($address));
            }
        });
    }

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(SupportedCryptocurrency::class, 'cryptocurrency_id');
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(BlockchainNetwork::class, 'network_id');
    }

    public function depositRequests(): HasMany
    {
        return $this->hasMany(DepositRequest::class);
    }
}