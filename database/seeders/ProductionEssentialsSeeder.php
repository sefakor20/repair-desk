<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\{Branch, ShopSettings, SmsTemplate, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionEssentialsSeeder extends Seeder
{
    /**
     * Seed essential data for production.
     * This seeder is safe to run multiple times as it uses updateOrCreate.
     */
    public function run(): void
    {
        $this->command->info('Seeding production essentials...');

        // Create main branch
        $mainBranch = Branch::updateOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Branch',
                'code' => 'MAIN',
                'address' => '123 Business Street',
                'city' => 'Your City',
                'state' => 'Your State',
                'zip' => '12345',
                'country' => 'Your Country',
                'phone' => '+1234567890',
                'email' => 'contact@yourcompany.com',
                'is_active' => true,
                'is_main' => true,
                'notes' => 'Default main branch. Please update with your actual business details.',
            ],
        );

        // Create shop settings
        ShopSettings::updateOrCreate(
            ['id' => 1],
            [
                'shop_name' => 'Repair Desk',
                'address' => '123 Business Street',
                'city' => 'Your City',
                'state' => 'Your State',
                'zip' => '12345',
                'country' => 'Your Country',
                'phone' => '+1234567890',
                'email' => 'contact@yourcompany.com',
                'website' => 'https://yourcompany.com',
                'tax_rate' => 0.00,
                'currency' => 'USD',
            ],
        );

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

        // Create admin user if none exists
        if (!User::where('role', UserRole::Admin)->exists()) {
            User::create([
                'name' => 'System Administrator',
                'email' => 'admin@yourcompany.com',
                'password' => Hash::make('change-me-in-production'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
                'branch_id' => $mainBranch->id,
            ]);

            $this->command->info('✓ Admin user created: admin@yourcompany.com');
            $this->command->warn('⚠️  Default password: change-me-in-production');
        }

        $this->command->info('✅ Production essentials seeded successfully!');
        $this->command->info('🔒 Remember to update default values with your actual business information');
    }
}
