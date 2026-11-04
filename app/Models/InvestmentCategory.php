<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_visible', 'sort_order'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sort_order' => 'integer'];
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(InvestmentOpportunity::class, 'category_id');
    }
}