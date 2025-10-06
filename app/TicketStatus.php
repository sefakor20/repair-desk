<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case WaitingForParts = 'waiting_for_parts';
    case Completed = 'completed';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In Progress',
            self::WaitingForParts => 'Waiting for Parts',
            self::Completed => 'Completed',
            self::Delivered => 'Delivered',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::InProgress => 'yellow',
            self::WaitingForParts => 'orange',
            self::Completed => 'green',
            self::Delivered => 'gray',
        };
    }
}
