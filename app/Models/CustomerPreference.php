<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPreference extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'customer_id',
        'notify_points_earned',
        'notify_reward_available',
        'notify_tier_upgrade',
        'notify_points_expiring',
        'notify_referral_success',
        'marketing_emails',
        'newsletter',
    ];

    protected function casts(): array
    {
        return [
            'notify_points_earned' => 'boolean',
            'notify_reward_available' => 'boolean',
            'notify_tier_upgrade' => 'boolean',
            'notify_points_expiring' => 'boolean',
            'notify_referral_success' => 'boolean',
            'marketing_emails' => 'boolean',
            'newsletter' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
