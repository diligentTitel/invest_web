<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycSubmission extends Model
{
    protected $fillable = [
        'user_id', 'provider', 'provider_reference', 'status', 'submitted_at',
        'reviewed_by', 'reviewed_at', 'review_reason', 'metadata', 'private_document_keys',
    ];

    protected $hidden = ['metadata', 'private_document_keys'];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'metadata' => 'encrypted:array',
            'private_document_keys' => 'encrypted:array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}