<?php

declare(strict_types=1);

namespace App\Enums;

enum CashTransactionType: string
{
    case Sale = 'sale';
    case CashIn = 'cash_in';
    case CashOut = 'cash_out';
    case Opening = 'opening';
    case Closing = 'closing';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Sale',
            self::CashIn => 'Cash In',
            self::CashOut => 'Cash Out',
            self::Opening => 'Opening Balance',
            self::Closing => 'Closing Balance',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Sale => 'green',
            self::CashIn => 'blue',
            self::CashOut => 'red',
            self::Opening => 'purple',
            self::Closing => 'zinc',
        };
    }
}
