<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Repair Complete',
                'key' => 'repair_complete',
                'message' => 'Hi {{customer_name}}, your device repair ({{ticket_number}}) is complete and ready for pickup at RepairDesk!',
                'description' => 'Sent when a repair ticket is marked as complete',
                'variables' => ['customer_name', 'ticket_number'],
                'is_active' => true,
            ],
            [
                'name' => 'Payment Reminder',
                'key' => 'payment_reminder',
                'message' => 'Hi {{customer_name}}, reminder: Invoice {{invoice_number}} of {{amount}} is due. Please visit our portal to pay.',
                'description' => 'Sent as a reminder for unpaid invoices',
                'variables' => ['customer_name', 'invoice_number', 'amount'],
                'is_active' => true,
            ],
            [
                'name' => 'Status Update',
                'key' => 'status_update',
                'message' => 'Hi {{customer_name}}, your repair {{ticket_number}} status has been updated to: {{status}}. Check the portal for details.',
                'description' => 'Sent when a ticket status changes',
                'variables' => ['customer_name', 'ticket_number', 'status'],
                'is_active' => true,
            ],
            [
                'name' => 'Welcome Message',
                'key' => 'welcome',
                'message' => 'Welcome to RepairDesk, {{customer_name}}! We\'re excited to serve you. Visit {{portal_url}} to track your repairs.',
                'description' => 'Sent to new customers',
                'variables' => ['customer_name', 'portal_url'],
                'is_active' => true,
            ],
            [
                'name' => 'Appointment Reminder',
                'key' => 'appointment_reminder',
                'message' => 'Hi {{customer_name}}, reminder: Your appointment is scheduled for {{appointment_date}} at {{appointment_time}}.',
                'description' => 'Sent as a reminder for scheduled appointments',
                'variables' => ['customer_name', 'appointment_date', 'appointment_time'],
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
    }
}
