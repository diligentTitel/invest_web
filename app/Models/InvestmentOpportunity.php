<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentOpportunity extends Model
{
    protected $fillable = [
        'category_id', 'title', 'slug', 'asset_reference', 'structure_type',
        'description', 'currency', 'minimum_investment', 'maximum_investment',
        'duration_months', 'risk_classification', 'documented_return_terms',
        'distribution_schedule', 'eligible_jurisdictions', 'legal_review_status',
        'availability_status', 'is_demo', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'minimum_investment' => 'decimal:18',
            'maximum_investment' => 'decimal:18',
            'documented_return_terms' => 'array',
            'eligible_jurisdictions' => 'array',
            'is_demo' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InvestmentCategory::class, 'category_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InvestmentDocument::class);
    }

    public function isEligibleForPublication(): bool
    {
        return $this->legal_review_status === 'approved'
            && $this->documents()->where('is_investor_visible', true)->exists();
    }
}