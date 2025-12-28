<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\{Branch, ShopSettings, SmsTemplate, User};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupProductionDefaults extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:setup-production {--force : Force overwrite existing data}';

    /**
     * The console command description.
     */
    protected $description = 'Set up essential default data for production environment';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Setting up production defaults...');

        // Setup main branch
        $this->setupMainBranch();

        // Setup shop settings
        $this->setupShopSettings();

        // Setup SMS templates
        $this->setupSmsTemplates();

        // Setup admin user
        $this->setupAdminUser();

        $this->info('✅ Production setup completed successfully!');
        $this->newLine();
        $this->warn('🔒 IMPORTANT SECURITY NOTES:');
        $this->warn('1. Change the default admin password immediately');
        $this->warn('2. Update shop settings with your actual business information');
        $this->warn('3. Review and customize SMS templates as needed');

        return Command::SUCCESS;
    }

    private function setupMainBranch(): void
    {
        if (Branch::count() === 0 || $this->option('force')) {
            $branch = Branch::updateOrCreate(
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
                    'notes' => 'Default main branch created during setup. Please update with your actual business details.',
                ],
            );

            $this->info('✓ Main branch created: ' . $branch->name);
        } else {
            $this->comment('⚪ Main branch already exists, skipping...');
        }
    }

    private function setupShopSettings(): void
    {
        if (ShopSettings::count() === 0 || $this->option('force')) {
            $settings = ShopSettings::updateOrCreate(
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

            $this->info('✓ Shop settings created: ' . $settings->shop_name);
        } else {
            $this->comment('⚪ Shop settings already exist, skipping...');
        }
    }

    private function setupSmsTemplates(): void
    {
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

        $created = 0;
        foreach ($templates as $template) {
            $smsTemplate = SmsTemplate::updateOrCreate(
                ['key' => $template['key']],
                $template,
            );

            if ($smsTemplate->wasRecentlyCreated) {
                $created++;
            }
        }

        if ($created > 0) {
            $this->info("✓ Created {$created} SMS templates");
        } else {
            $this->comment('⚪ SMS templates already exist, skipping...');
        }
    }

    private function setupAdminUser(): void
    {
        $existingAdmin = User::where('role', UserRole::Admin)->first();

        if (!$existingAdmin || $this->option('force')) {
            $mainBranch = Branch::where('is_main', true)->first();

            if ($this->option('force') && $existingAdmin) {
                $this->warn('Updating existing admin user...');
            }

            $admin = User::updateOrCreate(
                ['email' => 'admin@yourcompany.com'],
                [
                    'name' => 'System Administrator',
                    'email' => 'admin@yourcompany.com',
                    'password' => Hash::make('change-me-in-production'),
                    'role' => UserRole::Admin,
                    'email_verified_at' => now(),
                    'branch_id' => $mainBranch?->id,
                ],
            );

            $this->info('✓ Admin user created: ' . $admin->email);
            $this->warn('Default password: change-me-in-production');
        } else {
            $this->comment('⚪ Admin user already exists: ' . $existingAdmin->email);
        }
    }
}
