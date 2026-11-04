<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorProfile extends Model
{
    protected $fillable = [
        'user_id', 'legal_name', 'country_code', 'preferred_currency',
        'phone_encrypted', 'address_encrypted', 'verification_status',
    ];

    protected $hidden = ['phone_encrypted', 'address_encrypted'];

    protected function casts(): array
    {
        return [
            'phone_encrypted' => 'encrypted',
            'address_encrypted' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}