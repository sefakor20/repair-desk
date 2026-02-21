<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'logo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => DeviceCategory::class,
            'is_active' => 'boolean',
        ];
    }

    public function models(): HasMany
    {
        return $this->hasMany(DeviceModel::class, 'brand_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory($query, DeviceCategory|string $category)
    {
        $categoryValue = $category instanceof DeviceCategory ? $category->value : $category;

        return $query->where('category', $categoryValue);
    }

    public function scopeForDeviceType($query, DeviceCategory|string $deviceType)
    {
        $categoryValue = $deviceType instanceof DeviceCategory ? $deviceType->value : $deviceType;

        return $query->where('category', $categoryValue)->where('is_active', true);
    }
}
