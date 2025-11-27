<?php

declare(strict_types=1);

use App\Livewire\Portal\NotificationHistory;
use App\Models\{Customer, SmsDeliveryLog};
use Livewire\Livewire;

it('renders successfully', function () {
    $customer = Customer::factory()->create();

    Livewire::test(NotificationHistory::class, ['customer' => $customer])
        ->assertStatus(200);
});

it('displays SMS delivery stats correctly', function () {
    $customer = Customer::factory()->create();

    // Create various SMS logs
    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer->id,
        'status' => 'sent',
    ]);

    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer->id,
        'status' => 'sent',
    ]);

    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer->id,
        'status' => 'failed',
    ]);

    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer->id,
        'status' => 'pending',
    ]);

    Livewire::test(NotificationHistory::class, ['customer' => $customer])
        ->assertSee('4') // Total
        ->assertSee('2') // Sent
        ->assertSee('1'); // Failed/Pending
});

it('filters SMS logs by status', function () {
    $customer = Customer::factory()->create();

    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer->id,
        'status' => 'sent',
        'message' => 'Sent message',
    ]);

    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer->id,
        'status' => 'failed',
        'message' => 'Failed message',
    ]);

    Livewire::test(NotificationHistory::class, ['customer' => $customer])
        ->set('filter', 'sent')
        ->assertSee('Sent message')
        ->assertDontSee('Failed message')
        ->set('filter', 'failed')
        ->assertSee('Failed message')
        ->assertDontSee('Sent message');
});

it('searches SMS logs by message content', function () {
    $customer = Customer::factory()->create();

    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer->id,
        'message' => 'Your repair is complete',
    ]);

    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer->id,
        'message' => 'Invoice payment reminder',
    ]);

    Livewire::test(NotificationHistory::class, ['customer' => $customer])
        ->set('search', 'repair')
        ->assertSee('Your repair is complete')
        ->assertDontSee('Invoice payment reminder');
});

it('clears filters correctly', function () {
    $customer = Customer::factory()->create();

    Livewire::test(NotificationHistory::class, ['customer' => $customer])
        ->set('filter', 'sent')
        ->set('search', 'test')
        ->call('clearFilters')
        ->assertSet('filter', 'all')
        ->assertSet('search', '');
});

it('paginates SMS logs', function () {
    $customer = Customer::factory()->create();

    SmsDeliveryLog::factory()->count(25)->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer->id,
    ]);

    Livewire::test(NotificationHistory::class, ['customer' => $customer])
        ->assertSee('1')
        ->assertSee('2'); // Page numbers
});

it('shows empty state when no SMS logs exist', function () {
    $customer = Customer::factory()->create();

    Livewire::test(NotificationHistory::class, ['customer' => $customer])
        ->assertSee('No SMS notifications yet');
});

it('only displays SMS logs for the authenticated customer', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer1->id,
        'message' => 'Customer 1 message',
    ]);

    SmsDeliveryLog::factory()->create([
        'notifiable_type' => Customer::class,
        'notifiable_id' => $customer2->id,
        'message' => 'Customer 2 message',
    ]);

    Livewire::test(NotificationHistory::class, ['customer' => $customer1])
        ->assertSee('Customer 1 message')
        ->assertDontSee('Customer 2 message');
});
