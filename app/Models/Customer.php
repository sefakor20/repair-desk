<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'notes',
        'tags',
        'portal_access_token',
        'portal_token_created_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'portal_token_created_at' => 'datetime',
        ];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function loyaltyAccount(): HasOne
    {
        return $this->hasOne(CustomerLoyaltyAccount::class);
    }

    /**
     * Generate a new portal access token for this customer.
     */
    public function generatePortalAccessToken(): string
    {
        $this->portal_access_token = bin2hex(random_bytes(32));
        $this->portal_token_created_at = now();
        $this->save();

        return $this->portal_access_token;
    }

    /**
     * Validate a portal access token for a customer.
     */
    public static function validatePortalToken(string $token, string|int $customerId): ?self
    {
        return self::where('id', $customerId)
            ->where('portal_access_token', $token)
            ->whereNotNull('portal_access_token')
            ->first();
    }

    /**
     * Get the portal URL for this customer.
     */
    public function getPortalUrlAttribute(): string
    {
        if (! $this->portal_access_token) {
            $this->generatePortalAccessToken();
        }

        return route('portal.access', [
            'customer' => $this->id,
            'token' => $this->portal_access_token,
        ]);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
