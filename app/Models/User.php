<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_demo' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function investorProfile(): HasOne
    {
        return $this->hasOne(InvestorProfile::class);
    }

    public function simulatedPositions(): HasMany
    {
        return $this->hasMany(SimulatedPosition::class);
    }

    public function simulatedTransactions(): HasMany
    {
        return $this->hasMany(SimulatedTransaction::class);
    }

    public function watchlistAssets(): BelongsToMany
    {
        return $this->belongsToMany(InvestmentAsset::class, 'investment_watchlist')->withTimestamps();
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->status === 'active'
            && $this->role?->permissions()->where('name', $permission)->exists() === true;
    }
}