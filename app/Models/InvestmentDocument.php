<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentDocument extends Model
{
    protected $fillable = [
        'investment_opportunity_id', 'uploaded_by', 'document_type', 'version',
        'storage_key', 'sha256', 'is_investor_visible', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['is_investor_visible' => 'boolean', 'reviewed_at' => 'datetime'];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class, 'investment_opportunity_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}