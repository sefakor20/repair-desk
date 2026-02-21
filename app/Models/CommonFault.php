<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonFault extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'device_category',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'device_category' => DeviceCategory::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDeviceCategory($query, DeviceCategory|string|null $category)
    {
        if ($category === null) {
            return $query->whereNull('device_category');
        }

        $categoryValue = $category instanceof DeviceCategory ? $category->value : $category;

        return $query->where(function ($q) use ($categoryValue) {
            $q->where('device_category', $categoryValue)
                ->orWhereNull('device_category');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
