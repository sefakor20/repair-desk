<?php

declare(strict_types=1);

namespace App\Enums;

enum LoyaltyRewardType: string
{
    case Discount = 'discount';
    case FreeProduct = 'free_product';
    case FreeService = 'free_service';
    case Voucher = 'voucher';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Discount => 'Discount',
            self::FreeProduct => 'Free Product',
            self::FreeService => 'Free Service',
            self::Voucher => 'Voucher',
            self::Custom => 'Custom Reward',
        };
    }
}
