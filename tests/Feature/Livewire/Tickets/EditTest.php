<?php

declare(strict_types=1);

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{Customer, Device, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create();
    $this->device = Device::factory()->for($this->customer)->create();
    $this->createdBy = User::factory()->create();
});

test('edit ticket page can be rendered', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    actingAs($this->user)
        ->get(route('tickets.edit', $ticket))
        ->assertOk()
        ->assertSee('Edit Ticket')
        ->assertSee($ticket->ticket_number)
        ->assertSee($ticket->problem_description);
});

test('user can update a ticket', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    $newCustomer = Customer::factory()->create();
    $newDevice = Device::factory()->for($newCustomer)->create();
    $technician = User::factory()->technician()->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('selectedCustomerId', $newCustomer->id)
        ->set('selectedDeviceId', $newDevice->id)
        ->set('form.customer_id', $newCustomer->id)
        ->set('form.device_id', $newDevice->id)
        ->set('form.problem_description', 'Updated problem description')
        ->set('form.diagnosis', 'Updated diagnosis')
        ->set('form.status', 'in_progress')
        ->set('form.priority', 'high')
        ->set('form.assigned_to', $technician->id)
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('tickets.show', $ticket));

    $ticket->refresh();

    expect($ticket->customer_id)->toBe($newCustomer->id)
        ->and($ticket->device_id)->toBe($newDevice->id)
        ->and($ticket->problem_description)->toBe('Updated problem description')
        ->and($ticket->diagnosis)->toBe('Updated diagnosis')
        ->and($ticket->status)->toBe(TicketStatus::InProgress)
        ->and($ticket->priority)->toBe(TicketPriority::High)
        ->and($ticket->assigned_to)->toBe($technician->id);
});

test('ticket status can be changed', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->statusNew()
        ->create(['created_by' => $this->createdBy->id]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.status', 'completed')
        ->call('update')
        ->assertHasNoErrors();

    expect($ticket->fresh()->status)->toBe(TicketStatus::Completed);
});

test('ticket priority can be changed', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->lowPriority()
        ->create(['created_by' => $this->createdBy->id]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.priority', 'urgent')
        ->call('update')
        ->assertHasNoErrors();

    expect($ticket->fresh()->priority)->toBe(TicketPriority::Urgent);
});

test('ticket can be reassigned to different technician', function (): void {
    $originalTechnician = User::factory()->technician()->create();
    $newTechnician = User::factory()->technician()->create();

    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create([
            'created_by' => $this->createdBy->id,
            'assigned_to' => $originalTechnician->id,
        ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.assigned_to', $newTechnician->id)
        ->call('update')
        ->assertHasNoErrors();

    expect($ticket->fresh()->assigned_to)->toBe($newTechnician->id);
});

test('ticket can be unassigned', function (): void {
    $technician = User::factory()->technician()->create();

    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create([
            'created_by' => $this->createdBy->id,
            'assigned_to' => $technician->id,
        ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.assigned_to', '')
        ->call('update')
        ->assertHasNoErrors();

    expect($ticket->fresh()->assigned_to)->toBeNull();
});

test('estimated completion date can be updated', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    $estimatedDate = now()->addDays(7)->format('Y-m-d');

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.estimated_completion', $estimatedDate)
        ->call('update')
        ->assertHasNoErrors();

    expect($ticket->fresh()->estimated_completion->format('Y-m-d'))->toBe($estimatedDate);
});

test('actual completion date can be set', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    $completionDate = now()->format('Y-m-d');

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.actual_completion', $completionDate)
        ->call('update')
        ->assertHasNoErrors();

    expect($ticket->fresh()->actual_completion->format('Y-m-d'))->toBe($completionDate);
});

test('form loads with existing ticket data', function (): void {
    $technician = User::factory()->technician()->create();
    $estimatedDate = now()->addDays(3);

    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create([
            'problem_description' => 'Original problem',
            'diagnosis' => 'Original diagnosis',
            'status' => TicketStatus::InProgress,
            'priority' => TicketPriority::High,
            'assigned_to' => $technician->id,
            'estimated_completion' => $estimatedDate,
            'created_by' => $this->createdBy->id,
        ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->assertSet('form.customer_id', $this->customer->id)
        ->assertSet('form.device_id', $this->device->id)
        ->assertSet('form.problem_description', 'Original problem')
        ->assertSet('form.diagnosis', 'Original diagnosis')
        ->assertSet('form.status', 'in_progress')
        ->assertSet('form.priority', 'high')
        ->assertSet('form.assigned_to', $technician->id)
        ->assertSet('form.estimated_completion', $estimatedDate->format('Y-m-d'));
});

test('validation works for required fields', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.customer_id', '')
        ->set('form.device_id', '')
        ->set('form.problem_description', '')
        ->set('form.status', '')
        ->set('form.priority', '')
        ->call('update')
        ->assertHasErrors([
            'form.customer_id',
            'form.device_id',
            'form.problem_description',
            'form.status',
            'form.priority',
        ]);
});

test('problem description must be at least 10 characters', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.problem_description', 'Short')
        ->call('update')
        ->assertHasErrors('form.problem_description');
});

test('status must be valid enum value', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.status', 'invalid_status')
        ->call('update')
        ->assertHasErrors('form.status');
});

test('priority must be valid enum value', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->set('form.priority', 'invalid_priority')
        ->call('update')
        ->assertHasErrors('form.priority');
});

test('devices list updates when customer is selected', function (): void {
    $newCustomer = Customer::factory()->create();
    Device::factory()->count(3)->for($newCustomer)->create();

    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Edit::class, ['ticket' => $ticket])
        ->assertSet('form.customer_id', $this->customer->id)
        ->assertSet('form.device_id', $this->device->id)
        ->set('selectedCustomerId', $newCustomer->id)
        ->assertSet('form.device_id', '')
        ->assertSet('selectedDeviceId', '');
});

test('unauthorized user cannot edit ticket', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    get(route('tickets.edit', $ticket))
        ->assertRedirect(route('login'));
});

test('page shows all status options', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    actingAs($this->user)
        ->get(route('tickets.edit', $ticket))
        ->assertSee(TicketStatus::New->label())
        ->assertSee(TicketStatus::InProgress->label())
        ->assertSee(TicketStatus::WaitingForParts->label())
        ->assertSee(TicketStatus::Completed->label())
        ->assertSee(TicketStatus::Delivered->label());
});

test('page shows all priority options', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    actingAs($this->user)
        ->get(route('tickets.edit', $ticket))
        ->assertSee(TicketPriority::Low->label())
        ->assertSee(TicketPriority::Normal->label())
        ->assertSee(TicketPriority::High->label())
        ->assertSee(TicketPriority::Urgent->label());
});
