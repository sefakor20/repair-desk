<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasOne};
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;
    use HasUlids;
    use Notifiable;

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
        'referral_code',
        'referred_by',
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

    public function preferences(): HasOne
    {
        return $this->hasOne(CustomerPreference::class);
    }

    public function referralsMade(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralsReceived(): HasMany
    {
        return $this->hasMany(Referral::class, 'referred_id');
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_by');
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'customer_achievements')
            ->withPivot(['earned_at', 'is_displayed'])
            ->withTimestamps();
    }

    public function sentTransfers(): HasMany
    {
        return $this->hasMany(PointTransfer::class, 'sender_id');
    }

    public function receivedTransfers(): HasMany
    {
        return $this->hasMany(PointTransfer::class, 'recipient_id');
    }

    /**
     * Generate a unique referral code for this customer.
     */
    public function generateReferralCode(): string
    {
        if ($this->referral_code) {
            return $this->referral_code;
        }

        $this->referral_code = Referral::generateCode($this);
        $this->save();

        return $this->referral_code;
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
        return mb_trim("{$this->first_name} {$this->last_name}");
    }
}
