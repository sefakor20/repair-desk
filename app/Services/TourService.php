<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;

class TourService
{
    /**
     * Get the appropriate tour name based on user role
     */
    public function getTourNameForUser(User $user): string
    {
        return match ($user->role) {
            UserRole::Admin => 'admin_onboarding',
            UserRole::Manager => 'manager_onboarding',
            UserRole::Technician => 'technician_onboarding',
            UserRole::FrontDesk => 'front_desk_onboarding',
            default => 'general_onboarding',
        };
    }

    /**
     * Check if user should see the tour
     */
    public function shouldShowTour(User $user): bool
    {
        // Check if tours are globally enabled
        if (!config('app.onboarding_tour_enabled', false)) {
            return false;
        }

        $tourName = $this->getTourNameForUser($user);
        return !$user->hasCompletedTour($tourName);
    }

    /**
     * Get tour steps based on user role
     */
    public function getTourSteps(User $user): array
    {
        return match ($user->role) {
            UserRole::Admin => $this->getAdminTourSteps(),
            UserRole::Manager => $this->getManagerTourSteps(),
            UserRole::Technician => $this->getTechnicianTourSteps(),
            UserRole::FrontDesk => $this->getFrontDeskTourSteps(),
            default => $this->getGeneralTourSteps(),
        };
    }

    /**
     * Admin tour steps
     */
    protected function getAdminTourSteps(): array
    {
        return [
            [
                'id' => 'dashboard_overview',
                'title' => 'Welcome to Repair Desk!',
                'content' => 'As an admin, you have full access to manage your repair business. Let\'s start with the dashboard overview.',
                'target' => '[data-tour="dashboard"]',
                'position' => 'bottom',
            ],
            [
                'id' => 'manage_users',
                'title' => 'User Management',
                'content' => 'From here you can manage all users, create technicians, and assign roles. Click on Users in the navigation.',
                'target' => '[data-tour="users-nav"]',
                'position' => 'right',
                'action' => 'highlight',
            ],
            [
                'id' => 'manage_branches',
                'title' => 'Branch Management',
                'content' => 'Manage multiple locations, assign staff to branches, and track branch-specific data.',
                'target' => '[data-tour="branches-nav"]',
                'position' => 'right',
                'action' => 'highlight',
            ],
            [
                'id' => 'inventory_system',
                'title' => 'Inventory Management',
                'content' => 'Track parts, manage stock levels, and get low stock alerts. Essential for repair operations.',
                'target' => '[data-tour="inventory-nav"]',
                'position' => 'right',
                'action' => 'highlight',
            ],
            [
                'id' => 'customer_management',
                'title' => 'Customer & Device Tracking',
                'content' => 'Manage customer information and their devices. This is the foundation of your repair tickets.',
                'target' => '[data-tour="customers-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'ticket_system',
                'title' => 'Repair Ticket System',
                'content' => 'The heart of your business - create, track, and manage repair tickets from intake to completion.',
                'target' => '[data-tour="tickets-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'pos_system',
                'title' => 'Point of Sale',
                'content' => 'Sell parts and accessories, process payments, and manage your retail operations.',
                'target' => '[data-tour="pos-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'reports_analytics',
                'title' => 'Reports & Analytics',
                'content' => 'Monitor your business performance with comprehensive reports on sales, repairs, and efficiency.',
                'target' => '[data-tour="reports-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'settings_overview',
                'title' => 'System Settings',
                'content' => 'Configure shop settings, SMS templates, return policies, and loyalty programs.',
                'target' => '[data-tour="settings-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'tour_complete',
                'title' => 'You\'re All Set!',
                'content' => 'You now know the basics of managing your repair business with Repair Desk. Explore each section to discover more features!',
                'target' => null,
                'position' => 'center',
            ],
        ];
    }

    /**
     * Manager tour steps
     */
    protected function getManagerTourSteps(): array
    {
        return [
            [
                'id' => 'dashboard_overview',
                'title' => 'Manager Dashboard',
                'content' => 'Welcome! As a manager, you can oversee operations, manage staff, and monitor performance.',
                'target' => '[data-tour="dashboard"]',
                'position' => 'bottom',
            ],
            [
                'id' => 'staff_management',
                'title' => 'Staff Management',
                'content' => 'Manage your branch staff, assign roles, and monitor productivity.',
                'target' => '[data-tour="users-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'inventory_oversight',
                'title' => 'Inventory Oversight',
                'content' => 'Monitor stock levels, approve purchases, and manage inventory across your branch.',
                'target' => '[data-tour="inventory-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'ticket_supervision',
                'title' => 'Repair Supervision',
                'content' => 'Oversee repair tickets, assign technicians, and ensure quality service delivery.',
                'target' => '[data-tour="tickets-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'sales_oversight',
                'title' => 'Sales Management',
                'content' => 'Monitor POS sales, process refunds, and manage retail operations.',
                'target' => '[data-tour="pos-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'branch_reports',
                'title' => 'Performance Reports',
                'content' => 'Access detailed reports to track branch performance and make informed decisions.',
                'target' => '[data-tour="reports-nav"]',
                'position' => 'right',
            ],
        ];
    }

    /**
     * Technician tour steps
     */
    protected function getTechnicianTourSteps(): array
    {
        return [
            [
                'id' => 'dashboard_overview',
                'title' => 'Technician Dashboard',
                'content' => 'Welcome! Here you can see your assigned tickets and daily tasks.',
                'target' => '[data-tour="dashboard"]',
                'position' => 'bottom',
            ],
            [
                'id' => 'assigned_tickets',
                'title' => 'Your Assigned Tickets',
                'content' => 'View all tickets assigned to you. These are your main work items for repair jobs.',
                'target' => '[data-tour="tickets-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'ticket_workflow',
                'title' => 'Ticket Workflow',
                'content' => 'Update ticket status, add diagnostic notes, and track repair progress from start to finish.',
                'target' => '[data-tour="ticket-status"]',
                'position' => 'bottom',
            ],
            [
                'id' => 'inventory_access',
                'title' => 'Parts & Inventory',
                'content' => 'Access parts inventory to check availability and request items needed for repairs.',
                'target' => '[data-tour="inventory-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'customer_communication',
                'title' => 'Customer Updates',
                'content' => 'View customer information and send status updates through the system.',
                'target' => '[data-tour="customers-nav"]',
                'position' => 'right',
            ],
        ];
    }

    /**
     * Front desk tour steps
     */
    protected function getFrontDeskTourSteps(): array
    {
        return [
            [
                'id' => 'dashboard_overview',
                'title' => 'Front Desk Dashboard',
                'content' => 'Welcome! Your role is crucial for customer service and intake management.',
                'target' => '[data-tour="dashboard"]',
                'position' => 'bottom',
            ],
            [
                'id' => 'customer_intake',
                'title' => 'Customer Management',
                'content' => 'Register new customers, update contact information, and manage customer relationships.',
                'target' => '[data-tour="customers-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'device_registration',
                'title' => 'Device Registration',
                'content' => 'Register customer devices and document their condition for repair tracking.',
                'target' => '[data-tour="devices-section"]',
                'position' => 'bottom',
            ],
            [
                'id' => 'ticket_creation',
                'title' => 'Creating Repair Tickets',
                'content' => 'Create new repair tickets, describe problems, and assign priority levels.',
                'target' => '[data-tour="tickets-nav"]',
                'position' => 'right',
            ],
            [
                'id' => 'payment_processing',
                'title' => 'Payment Processing',
                'content' => 'Process payments, create invoices, and handle customer transactions.',
                'target' => '[data-tour="invoices-section"]',
                'position' => 'bottom',
            ],
            [
                'id' => 'pos_operations',
                'title' => 'Point of Sale',
                'content' => 'Handle accessory sales, process transactions, and manage the retail counter.',
                'target' => '[data-tour="pos-nav"]',
                'position' => 'right',
            ],
        ];
    }

    /**
     * General tour steps (fallback)
     */
    protected function getGeneralTourSteps(): array
    {
        return [
            [
                'id' => 'dashboard_overview',
                'title' => 'Welcome to Repair Desk!',
                'content' => 'Get familiar with the main features available to you in this repair management system.',
                'target' => '[data-tour="dashboard"]',
                'position' => 'bottom',
            ],
            [
                'id' => 'navigation',
                'title' => 'Navigation Menu',
                'content' => 'Use this sidebar to navigate between different sections of the application.',
                'target' => '[data-tour="sidebar"]',
                'position' => 'right',
            ],
            [
                'id' => 'your_profile',
                'title' => 'Your Profile',
                'content' => 'Access your profile settings and preferences from the top-right corner.',
                'target' => '[data-tour="profile-menu"]',
                'position' => 'bottom',
            ],
        ];
    }

    /**
     * Mark tour step as completed
     */
    public function markStepCompleted(User $user, string $stepId): void
    {
        $tourName = $this->getTourNameForUser($user);
        $tour = $user->getOrCreateTour($tourName);
        $tour->markStepCompleted($stepId);
    }

    /**
     * Complete the entire tour
     */
    public function completeTour(User $user): void
    {
        $tourName = $this->getTourNameForUser($user);
        $tour = $user->getOrCreateTour($tourName);
        $tour->markCompleted();
    }

    /**
     * Skip the tour
     */
    public function skipTour(User $user): void
    {
        $tourName = $this->getTourNameForUser($user);
        $tour = $user->getOrCreateTour($tourName);
        $tour->markSkipped();
    }
}
