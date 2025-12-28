<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Models\{Customer, Device, Invoice, Ticket};
use App\Notifications\RepairCompleted;
use Illuminate\Support\Facades\Notification;

test('repair completed notification is sent when ticket status changes to completed', function (): void {
    Notification::fake();

    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create([
        'status' => TicketStatus::InProgress,
    ]);

    $ticket->update(['status' => TicketStatus::Completed, 'actual_completion' => now()]);

    Notification::assertSentTo($customer, RepairCompleted::class);
});

test('repair completed notification contains device and ticket information', function (): void {
    $customer = Customer::factory()->create(['first_name' => 'Jane']);
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create([
        'status' => TicketStatus::Completed,
        'actual_completion' => now(),
    ]);

    $notification = new RepairCompleted($ticket);
    $mailData = $notification->toMail($customer);

    expect($mailData->subject)->toContain('Repair Completed')
        ->and($mailData->subject)->toContain($ticket->ticket_number);
});

test('repair completed notification includes invoice balance when invoice exists', function (): void {
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create([
        'status' => TicketStatus::Completed,
    ]);
    $invoice = Invoice::factory()->for($ticket)->create([
        'total' => 500,
    ]);

    // Create payment to simulate partial payment
    \App\Models\Payment::factory()->for($invoice)->create([
        'amount' => 200,
    ]);

    $notification = new RepairCompleted($ticket->fresh());
    $smsMessage = $notification->toSms($customer);

    expect($smsMessage)->toContain('300.00');
});

test('repair completed notification generates sms message', function (): void {
    $customer = Customer::factory()->create(['phone' => '+1234567890']);
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create([
        'status' => TicketStatus::Completed,
    ]);

    $notification = new RepairCompleted($ticket);
    $smsMessage = $notification->toSms($customer);

    expect($smsMessage)
        ->toContain('repair is complete')
        ->toContain($ticket->ticket_number);
});
