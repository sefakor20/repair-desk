<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Models\{Customer, Device, Ticket};
use App\Notifications\TicketStatusChanged;
use Illuminate\Support\Facades\Notification;

test('ticket status changed notification is sent to customer', function () {
    Notification::fake();

    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create([
        'status' => TicketStatus::New,
    ]);

    // Update ticket status
    $ticket->update(['status' => TicketStatus::InProgress]);

    Notification::assertSentTo(
        $customer,
        TicketStatusChanged::class,
        function ($notification) use ($ticket) {
            return $notification->ticket->id === $ticket->id
                && $notification->oldStatus === TicketStatus::New->value
                && $notification->newStatus === TicketStatus::InProgress->value;
        },
    );
});

test('ticket status changed notification contains correct information', function () {
    $customer = Customer::factory()->create(['first_name' => 'John', 'phone' => '+1234567890']);
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create();

    $notification = new TicketStatusChanged($ticket, TicketStatus::New->value, TicketStatus::InProgress->value);

    $mailData = $notification->toMail($customer);

    expect($mailData->subject)->toBe('Ticket Status Update - #' . $ticket->ticket_number)
        ->and($mailData->greeting)->toContain('Hello John');
});

test('ticket status changed notification generates sms message', function () {
    $customer = Customer::factory()->create(['phone' => '+1234567890']);
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create();

    $notification = new TicketStatusChanged($ticket, TicketStatus::New->value, TicketStatus::InProgress->value);

    $smsMessage = $notification->toSms($customer);

    expect($smsMessage)
        ->toContain($ticket->ticket_number)
        ->toContain('New')
        ->toContain('In progress');
});

test('ticket status changed notification includes sms channel when customer has phone', function () {
    $customer = Customer::factory()->create(['phone' => '+1234567890']);
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create();

    $notification = new TicketStatusChanged($ticket, TicketStatus::New->value, TicketStatus::InProgress->value);

    $channels = $notification->via($customer);

    expect($channels)->toContain('mail')
        ->toContain(\App\Channels\SmsChannel::class);
});

test('ticket status changed notification excludes sms channel when customer has no phone', function () {
    $customer = Customer::factory()->create(['phone' => '+1234567890']);
    $customer->updateQuietly(['phone' => null]);

    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create();

    $notification = new TicketStatusChanged($ticket, TicketStatus::New->value, TicketStatus::InProgress->value);

    $channels = $notification->via($customer);

    expect($channels)->toContain('mail')
        ->not->toContain(\App\Channels\SmsChannel::class);
});
