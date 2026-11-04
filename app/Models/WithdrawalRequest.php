<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id', 'financial_account_id', 'network_id', 'reference', 'idempotency_key',
        'amount', 'currency', 'destination_encrypted', 'status', 'reviewed_by',
        'reviewed_at', 'rejection_reason', 'payment_reference', 'ledger_transaction_id', 'completed_at',
    ];

    protected $hidden = ['destination_encrypted'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:18',
            'destination_encrypted' => 'encrypted',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(BlockchainNetwork::class, 'network_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function ledgerTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'ledger_transaction_id');
    }
}