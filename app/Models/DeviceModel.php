<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceCategory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'name',
        'category',
        'specifications',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => DeviceCategory::class,
            'specifications' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(DeviceBrand::class, 'brand_id');
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->brand?->name . ' ' . $this->name,
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBrand($query, int $brandId)
    {
        return $query->where('brand_id', $brandId)->where('is_active', true);
    }

    public function scopeCategory($query, DeviceCategory|string $category)
    {
        $categoryValue = $category instanceof DeviceCategory ? $category->value : $category;

        return $query->where('category', $categoryValue);
    }
}
