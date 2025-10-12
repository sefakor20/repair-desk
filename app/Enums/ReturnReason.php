<?php

declare(strict_types=1);

namespace App\Enums;

enum ReturnReason: string
{
    case Defective = 'defective';
    case WrongItem = 'wrong_item';
    case CustomerChanged = 'customer_changed_mind';
    case NotAsDescribed = 'not_as_described';
    case Damaged = 'damaged';
    case Duplicate = 'duplicate_order';
    case BetterPrice = 'found_better_price';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Defective => 'Defective/Faulty',
            self::WrongItem => 'Wrong Item',
            self::CustomerChanged => 'Customer Changed Mind',
            self::NotAsDescribed => 'Not As Described',
            self::Damaged => 'Damaged',
            self::Duplicate => 'Duplicate Order',
            self::BetterPrice => 'Found Better Price',
            self::Other => 'Other',
        };
    }

    public function requiresRestockingFee(): bool
    {
        return match ($this) {
            self::CustomerChanged, self::BetterPrice, self::Other => true,
            default => false,
        };
    }
}
