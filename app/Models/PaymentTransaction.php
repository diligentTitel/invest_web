<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'deposit_request_id', 'payment_provider_id', 'network_id', 'provider_payment_id',
        'transaction_hash', 'event_id', 'payload_sha256', 'currency_code', 'observed_amount',
        'destination_address_encrypted', 'confirmations', 'verification_status',
        'verification_source', 'observed_at', 'confirmed_at',
    ];

    protected $hidden = ['destination_address_encrypted', 'payload_sha256'];

    protected function casts(): array
    {
        return [
            'observed_amount' => 'decimal:18',
            'destination_address_encrypted' => 'encrypted',
            'confirmations' => 'integer',
            'observed_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function depositRequest(): BelongsTo
    {
        return $this->belongsTo(DepositRequest::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider_id');
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(BlockchainNetwork::class, 'network_id');
    }
}