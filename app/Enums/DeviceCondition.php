<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceCondition: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case Fair = 'fair';
    case Poor = 'poor';
    case Damaged = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::Excellent => 'Excellent - Like new, no visible wear',
            self::Good => 'Good - Minor wear, fully functional',
            self::Fair => 'Fair - Noticeable wear, functional',
            self::Poor => 'Poor - Heavy wear, may have issues',
            self::Damaged => 'Damaged - Significant damage',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Excellent => 'green',
            self::Good => 'blue',
            self::Fair => 'yellow',
            self::Poor => 'orange',
            self::Damaged => 'red',
        };
    }
}
