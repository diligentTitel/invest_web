<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepositRequest extends Model
{
    protected $fillable = [
        'user_id', 'cryptocurrency_id', 'network_id', 'crypto_wallet_id', 'payment_provider_id',
        'reference', 'idempotency_key', 'method', 'requested_amount', 'expected_crypto_amount',
        'received_amount', 'currency_code', 'status', 'expires_at', 'payment_detected_at',
        'verified_at', 'reviewed_at', 'reviewed_by', 'review_reason',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:18',
            'expected_crypto_amount' => 'decimal:18',
            'received_amount' => 'decimal:18',
            'expires_at' => 'datetime',
            'payment_detected_at' => 'datetime',
            'verified_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(SupportedCryptocurrency::class, 'cryptocurrency_id');
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(BlockchainNetwork::class, 'network_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(CryptoWallet::class, 'crypto_wallet_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}