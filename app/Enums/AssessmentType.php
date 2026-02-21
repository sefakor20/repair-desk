<?php

declare(strict_types=1);

namespace App\Enums;

enum AssessmentType: string
{
    case CheckIn = 'check_in';
    case CheckOut = 'check_out';

    public function label(): string
    {
        return match ($this) {
            self::CheckIn => 'Check-In Assessment',
            self::CheckOut => 'Check-Out Assessment',
        };
    }
}
