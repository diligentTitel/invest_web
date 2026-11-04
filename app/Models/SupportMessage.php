<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    public $timestamps = false;

    protected $fillable = ['support_ticket_id', 'sender_id', 'body', 'private_attachment_keys'];

    protected function casts(): array
    {
        return [
            'private_attachment_keys' => 'encrypted:array',
            'created_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}