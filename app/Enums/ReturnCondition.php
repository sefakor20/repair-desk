<?php

declare(strict_types=1);

namespace App\Enums;

enum ReturnCondition: string
{
    case New = 'new';
    case Opened = 'opened';
    case Used = 'used';
    case Damaged = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New/Unopened',
            self::Opened => 'Opened',
            self::Used => 'Used',
            self::Damaged => 'Damaged',
        };
    }
}
