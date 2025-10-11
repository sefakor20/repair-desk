<?php

declare(strict_types=1);

namespace App\Enums;

enum PosSaleStatus: string
{
    case Completed = 'completed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Completed => 'green',
            self::Refunded => 'red',
        };
    }
}
