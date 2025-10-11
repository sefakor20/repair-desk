<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSettings extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'shop_name',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'phone',
        'email',
        'website',
        'tax_rate',
        'currency',
        'logo_path',
        'business_hours',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'business_hours' => 'array',
        ];
    }

    /**
     * Get the shop settings instance (singleton pattern).
     */
    public static function getInstance(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'shop_name' => 'Repair Desk',
                'country' => 'USA',
                'currency' => 'USD',
                'tax_rate' => 0,
            ],
        );
    }

    /**
     * Get formatted full address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}
