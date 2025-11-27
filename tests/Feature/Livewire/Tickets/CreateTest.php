<?php

declare(strict_types=1);

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{Customer, Device, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs};

beforeEach(function (): void {
    $this->user = createAdmin();
    $this->customer = Customer::factory()->create();
    $this->device = Device::factory()->for($this->customer)->create();
});

test('create ticket page can be rendered', function (): void {
    actingAs($this->user)
        ->get(route('tickets.create'))
        ->assertOk()
        ->assertSee('Create Ticket')
        ->assertSee('Customer')
        ->assertSee('Device');
});

test('user can create a ticket', function (): void {
    $technician = User::factory()->technician()->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('selectedCustomerId', $this->customer->id)
        ->set('selectedDeviceId', $this->device->id)
        ->set('form.problem_description', 'Screen is cracked and not responding to touch')
        ->set('form.priority', 'high')
        ->set('form.assigned_to', $technician->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $ticket = Ticket::latest()->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket->customer_id)->toBe($this->customer->id)
        ->and($ticket->device_id)->toBe($this->device->id)
        ->and($ticket->problem_description)->toBe('Screen is cracked and not responding to touch')
        ->and($ticket->priority)->toBe(TicketPriority::High)
        ->and($ticket->status)->toBe(TicketStatus::New)
        ->and($ticket->assigned_to)->toBe($technician->id)
        ->and($ticket->created_by)->toBe($this->user->id);
});

test('ticket requires customer', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('form.device_id', $this->device->id)
        ->set('form.problem_description', 'Screen is cracked')
        ->set('form.priority', 'high')
        ->call('save')
        ->assertHasErrors(['form.customer_id']);
});

test('ticket requires device', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('form.customer_id', $this->customer->id)
        ->set('form.problem_description', 'Screen is cracked')
        ->set('form.priority', 'high')
        ->call('save')
        ->assertHasErrors(['form.device_id']);
});

test('ticket requires problem description', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('form.customer_id', $this->customer->id)
        ->set('form.device_id', $this->device->id)
        ->set('form.priority', 'high')
        ->call('save')
        ->assertHasErrors(['form.problem_description']);
});

test('problem description must be at least 10 characters', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('form.customer_id', $this->customer->id)
        ->set('form.device_id', $this->device->id)
        ->set('form.problem_description', 'Too short')
        ->set('form.priority', 'high')
        ->call('save')
        ->assertHasErrors(['form.problem_description']);
});

test('ticket requires priority', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('form.customer_id', $this->customer->id)
        ->set('form.device_id', $this->device->id)
        ->set('form.problem_description', 'Screen is cracked')
        ->call('save')
        ->assertHasErrors(['form.priority']);
});

test('priority must be valid', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('form.customer_id', $this->customer->id)
        ->set('form.device_id', $this->device->id)
        ->set('form.problem_description', 'Screen is cracked')
        ->set('form.priority', 'invalid')
        ->call('save')
        ->assertHasErrors(['form.priority']);
});

test('assigned technician is optional', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('selectedCustomerId', $this->customer->id)
        ->set('selectedDeviceId', $this->device->id)
        ->set('form.problem_description', 'Screen is cracked and not responding')
        ->set('form.priority', 'high')
        ->call('save')
        ->assertHasNoErrors();

    $ticket = Ticket::latest()->first();
    expect($ticket->assigned_to)->toBeNull();
});

test('estimated completion date must be in the future', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('form.customer_id', $this->customer->id)
        ->set('form.device_id', $this->device->id)
        ->set('form.problem_description', 'Screen is cracked')
        ->set('form.priority', 'high')
        ->set('form.estimated_completion', now()->subDay()->format('Y-m-d'))
        ->call('save')
        ->assertHasErrors(['form.estimated_completion']);
});

test('devices list updates when customer is selected', function (): void {
    $customer2 = Customer::factory()->create();
    $device2 = Device::factory()->for($customer2)->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->assertSet('selectedCustomerId', null)
        ->set('selectedCustomerId', $this->customer->id)
        ->assertSet('form.customer_id', $this->customer->id)
        ->assertSet('selectedDeviceId', null);
});

test('device field resets when customer changes', function (): void {
    $customer2 = Customer::factory()->create();
    $device2 = Device::factory()->for($customer2)->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('selectedCustomerId', $this->customer->id)
        ->set('selectedDeviceId', $this->device->id)
        ->assertSet('form.device_id', $this->device->id)
        ->set('selectedCustomerId', $customer2->id)
        ->assertSet('form.device_id', '')
        ->assertSet('selectedDeviceId', null);
});

test('diagnosis field is optional', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('selectedCustomerId', $this->customer->id)
        ->set('selectedDeviceId', $this->device->id)
        ->set('form.problem_description', 'Screen is cracked and not responding')
        ->set('form.priority', 'high')
        ->set('form.diagnosis', 'LCD panel needs replacement')
        ->call('save')
        ->assertHasNoErrors();

    $ticket = Ticket::latest()->first();
    expect($ticket->diagnosis)->toBe('LCD panel needs replacement');
});

test('ticket is created with new status', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('selectedCustomerId', $this->customer->id)
        ->set('selectedDeviceId', $this->device->id)
        ->set('form.problem_description', 'Screen is cracked')
        ->set('form.priority', 'urgent')
        ->call('save');

    $ticket = Ticket::latest()->first();
    expect($ticket->status)->toBe(TicketStatus::New);
});

test('ticket number is auto-generated', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Create::class)
        ->set('selectedCustomerId', $this->customer->id)
        ->set('selectedDeviceId', $this->device->id)
        ->set('form.problem_description', 'Screen is cracked')
        ->set('form.priority', 'high')
        ->call('save');

    $ticket = Ticket::latest()->first();
    expect($ticket->ticket_number)->toStartWith('TKT-');
});
