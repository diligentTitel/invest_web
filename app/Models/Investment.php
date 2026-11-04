<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    protected $fillable = [
        'user_id', 'investment_opportunity_id', 'principal_amount', 'currency', 'status',
        'agreement_version', 'agreement_sha256', 'ledger_transaction_id', 'accepted_at',
        'started_at', 'matures_at',
    ];

    protected function casts(): array
    {
        return [
            'principal_amount' => 'decimal:18',
            'accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'matures_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class, 'investment_opportunity_id');
    }

    public function ledgerTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'ledger_transaction_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(InvestmentDistribution::class);
    }
}