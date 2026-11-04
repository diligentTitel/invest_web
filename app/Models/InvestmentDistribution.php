<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentDistribution extends Model
{
    protected $fillable = [
        'investment_id', 'amount', 'currency', 'status', 'source_reference',
        'ledger_transaction_id', 'distribution_date',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:18', 'distribution_date' => 'datetime'];
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function ledgerTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'ledger_transaction_id');
    }
}