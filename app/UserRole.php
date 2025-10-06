<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Technician = 'technician';
    case FrontDesk = 'front_desk';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Manager => 'Manager',
            self::Technician => 'Technician',
            self::FrontDesk => 'Front Desk',
        };
    }
}
