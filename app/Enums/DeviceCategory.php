<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceCategory: string
{
    case Smartphone = 'smartphone';
    case FeaturePhone = 'feature_phone';
    case Laptop = 'laptop';
    case Palmtop = 'palmtop';
    case Desktop = 'desktop';
    case Tablet = 'tablet';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Smartphone => 'Smartphone',
            self::FeaturePhone => 'Feature Phone',
            self::Laptop => 'Laptop',
            self::Palmtop => 'Palmtop',
            self::Desktop => 'Desktop',
            self::Tablet => 'Tablet',
            self::Other => 'Other',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $category) => [$category->value => $category->label()])
            ->all();
    }
}
