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
                'view_all_tickets',
                'manage_tickets',
                'manage_inventory',
                'manage_sales',
                'view_reports',
                'manage_shifts',
                'manage_cashier',
            ],
            self::Technician => [
                'view_assigned_tickets',
                'update_ticket_status',
                'add_ticket_notes',
                'use_inventory',
                'create_sales',
            ],
            self::Receptionist => [
                'create_tickets',
                'view_all_tickets',
                'update_ticket_priority',
                'manage_customers',
                'schedule_appointments',
            ],
            self::Inventory => [
                'view_inventory',
                'manage_inventory',
                'create_adjustments',
                'view_reports',
            ],
            self::Cashier => [
                'process_payments',
                'create_invoices',
                'view_sales',
                'manage_cashier',
            ],
        };
    }

    public static function all(): array
    {
        return self::cases();
    }
}
