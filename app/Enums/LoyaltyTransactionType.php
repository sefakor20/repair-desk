<?php

declare(strict_types=1);

namespace App\Enums;

enum LoyaltyTransactionType: string
{
    case Earned = 'earned';
    case Redeemed = 'redeemed';
    case Expired = 'expired';
    case Adjusted = 'adjusted';
    case Bonus = 'bonus';
    case Refunded = 'refunded';
    case TransferSent = 'transfer_sent';
    case TransferReceived = 'transfer_received';

    public function label(): string
    {
        return match ($this) {
            self::Earned => 'Points Earned',
            self::Redeemed => 'Points Redeemed',
            self::Expired => 'Points Expired',
            self::Adjusted => 'Manual Adjustment',
            self::Bonus => 'Bonus Points',
            self::Refunded => 'Points Refunded',
            self::TransferSent => 'Transfer Sent',
            self::TransferReceived => 'Transfer Received',
        };
    }

    public function isPositive(): bool
    {
        return in_array($this, [self::Earned, self::Bonus, self::Refunded, self::TransferReceived]);
    }
}
