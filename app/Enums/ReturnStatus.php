<?php

declare(strict_types=1);

namespace App\Enums;

enum ReturnStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Processing => 'Processing Refund',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'blue',
            self::Rejected => 'red',
            self::Processing => 'purple',
            self::Completed => 'green',
            self::Cancelled => 'gray',
        };
    }

    public function canEdit(): bool
    {
        return match ($this) {
            self::Pending, self::Approved => true,
            default => false,
        };
    }

    public function canRefund(): bool
    {
        return match ($this) {
            self::Approved, self::Processing => true,
            default => false,
        };
    }
}
