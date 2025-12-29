<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\{Branch, ShopSettings, SmsTemplate};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{Hash, App};

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * This migration creates essential default data needed for the application to function in production.
     * It only inserts data if it doesn't already exist to avoid duplicates.
     * This migration will NOT run in testing environment to avoid interfering with tests.
     */
    public function up(): void
    {
        // Skip this migration during testing to avoid test interference
        if (App::environment('testing')) {
            return;
        }

        // Create main branch if no branches exist
        if (Branch::count() === 0) {
            Branch::create([
                'name' => 'Main Branch',
                'code' => 'MAIN',
                'address' => '123 Business Street',
                'city' => 'Your City',
                'state' => 'Your State',
                'zip' => '12345',
                'country' => 'Your Country',
                'phone' => '+1234567890',
                'email' => 'contact@example.com',
                'is_active' => true,
                'is_main' => true,
                'notes' => 'Default main branch created during setup. Please update with your actual business details.',
            ]);
        }

        // Create shop settings if not exists
        if (ShopSettings::count() === 0) {
            ShopSettings::create([
                'shop_name' => 'Repair Desk',
                'address' => '123 Business Street',
                'city' => 'Your City',
                'state' => 'Your State',
                'zip' => '12345',
                'country' => 'Your Country',
                'phone' => '+1234567890',
                'email' => 'contact@example.com',
                'website' => 'https://example.com',
                'tax_rate' => 0.00,
                'currency' => 'USD',
            ]);
        }

        // Create essential SMS templates
        $templates = [
            [
                'name' => 'Repair Complete',
                'key' => 'repair_complete',
                'message' => 'Hi {{customer_name}}, your device repair ({{ticket_number}}) is complete and ready for pickup!',
                'description' => 'Sent when a repair ticket is marked as complete',
                'variables' => ['customer_name', 'ticket_number'],
                'is_active' => true,
            ],
            [
                'name' => 'Status Update',
                'key' => 'status_update',
                'message' => 'Hi {{customer_name}}, your repair {{ticket_number}} status has been updated to: {{status}}.',
                'description' => 'Sent when a ticket status changes',
                'variables' => ['customer_name', 'ticket_number', 'status'],
                'is_active' => true,
            ],
            [
                'name' => 'Payment Reminder',
                'key' => 'payment_reminder',
                'message' => 'Hi {{customer_name}}, reminder: Invoice {{invoice_number}} of {{amount}} is due.',
                'description' => 'Sent as a reminder for unpaid invoices',
                'variables' => ['customer_name', 'invoice_number', 'amount'],
                'is_active' => true,
            ],
            [
                'name' => 'Payment Received',
                'key' => 'payment_received',
                'message' => 'Thank you {{customer_name}}! We\'ve received your payment of {{amount}} for invoice {{invoice_number}}.',
                'description' => 'Sent when a payment is successfully processed',
                'variables' => ['customer_name', 'amount', 'invoice_number'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                ['key' => $template['key']],
                $template,
            );
        }

        // Create default admin user if no admin exists
        if (!\App\Models\User::where('role', UserRole::Admin)->exists()) {
            $mainBranch = Branch::where('is_main', true)->first();

            \App\Models\User::create([
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('change-me-in-production'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
                'branch_id' => $mainBranch?->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We generally don't reverse data seeding in production
        // But you could add cleanup logic here if needed
    }
};
