<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Staff roles available within a branch.
 * These are specific to branch operations and differ from system-wide UserRole.
 */
enum StaffRole: string
{
    case BranchManager = 'branch_manager';
    case Technician = 'technician';
    case Receptionist = 'receptionist';
    case Inventory = 'inventory';
    case Cashier = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::BranchManager => 'Branch Manager',
            self::Technician => 'Technician',
            self::Receptionist => 'Receptionist',
            self::Inventory => 'Inventory Staff',
            self::Cashier => 'Cashier',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BranchManager => 'Manages branch operations and staff',
            self::Technician => 'Repairs and services devices',
            self::Receptionist => 'Customer service and appointments',
            self::Inventory => 'Manages branch inventory',
            self::Cashier => 'Handles cash and payments',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::BranchManager => [
                'manage_staff',
                'manage_tickets',
                'manage_inventory',
                'manage_customers',
                'view_reports',
                'manage_settings',
                'process_payments',
                'manage_cash_drawer',
            ],
            self::Technician => [
                'view_assigned_tickets',
                'update_ticket_status',
                'use_inventory',
                'create_sales',
            ],
            self::Receptionist => [
                'create_tickets',
                'schedule_appointments',
                'manage_customers',
                'view_inventory',
            ],
            self::Inventory => [
                'view_inventory',
                'use_inventory',
                'create_inventory_adjustments',
            ],
            self::Cashier => [
                'create_sales',
                'process_payments',
                'view_sales',
                'create_invoices',
                'manage_cash_drawer',
            ],
        };
    }

    public static function all(): array
    {
        return self::cases();
    }
}
