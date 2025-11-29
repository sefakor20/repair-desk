<?php

declare(strict_types=1);

use App\Channels\SmsChannel;
use App\Models\{Customer};
use App\Notifications\{RepairCompleted, TicketStatusChanged};
use App\Services\SmsService;

it('respects customer SMS enabled preference', function () {
    $customer = Customer::factory()->create(['phone' => '+233123456789']);
    $customer->preferences()->create([
        'sms_enabled' => false,
        'sms_ticket_updates' => true,
    ]);

    $ticket = \App\Models\Ticket::factory()->create(['customer_id' => $customer->id]);

    $notification = new TicketStatusChanged($ticket, 'new', 'in_progress');
    $channel = new SmsChannel(app(SmsService::class));

    // Should not send SMS when sms_enabled is false
    $channel->send($customer, $notification);

    expect($customer->smsDeliveryLogs()->count())->toBe(0);
});

it('respects ticket updates preference', function () {
    $customer = Customer::factory()->create(['phone' => '+233123456789']);
    $customer->preferences()->create([
        'sms_enabled' => true,
        'sms_ticket_updates' => false,
    ]);

    $ticket = \App\Models\Ticket::factory()->create(['customer_id' => $customer->id]);

    $notification = new TicketStatusChanged($ticket, 'new', 'in_progress');
    $channel = new SmsChannel(app(SmsService::class));

    // Should not send SMS when sms_ticket_updates is false
    $channel->send($customer, $notification);

    expect($customer->smsDeliveryLogs()->count())->toBe(0);
});

it('respects repair completed preference', function () {
    $customer = Customer::factory()->create(['phone' => '+233123456789']);
    $customer->preferences()->create([
        'sms_enabled' => true,
        'sms_repair_completed' => false,
    ]);

    $ticket = \App\Models\Ticket::factory()->create(['customer_id' => $customer->id]);

    $notification = new RepairCompleted($ticket);
    $channel = new SmsChannel(app(SmsService::class));

    // Should not send SMS when sms_repair_completed is false
    $channel->send($customer, $notification);

    expect($customer->smsDeliveryLogs()->count())->toBe(0);
});

it('sends SMS when all preferences are enabled', function () {
    config(['services.texttango.api_key' => 'test-key']);
    config(['services.texttango.url' => 'https://api.test.com']);

    $customer = Customer::factory()->create(['phone' => '+233123456789']);
    $customer->preferences()->create([
        'sms_enabled' => true,
        'sms_ticket_updates' => true,
    ]);

    $ticket = \App\Models\Ticket::factory()->create(['customer_id' => $customer->id]);

    $notification = new TicketStatusChanged($ticket, 'new', 'in_progress');
    $channel = new SmsChannel(app(SmsService::class));

    \Illuminate\Support\Facades\Http::fake([
        '*' => \Illuminate\Support\Facades\Http::response(['success' => true], 200),
    ]);

    $channel->send($customer, $notification);

    expect($customer->smsDeliveryLogs()->count())->toBeGreaterThan(0);
});

it('allows customer to opt out of all SMS notifications', function () {
    $customer = Customer::factory()->create(['phone' => '+233123456789']);

    $preferences = $customer->preferences()->create([
        'sms_enabled' => false,
        'sms_ticket_updates' => false,
        'sms_repair_completed' => false,
        'sms_invoice_reminders' => false,
    ]);

    expect($preferences->sms_enabled)->toBeFalse()
        ->and($preferences->sms_ticket_updates)->toBeFalse()
        ->and($preferences->sms_repair_completed)->toBeFalse()
        ->and($preferences->sms_invoice_reminders)->toBeFalse();
});
