<?php

declare(strict_types=1);

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{Customer, Device, Invoice, Payment, Ticket, TicketNote, TicketPart, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function (): void {
    $this->user = createAdmin();
    $this->customer = Customer::factory()->create();
    $this->device = Device::factory()->for($this->customer)->create();
    $this->createdBy = User::factory()->create(['name' => 'John Creator']);
    $this->assignedTo = User::factory()->technician()->create(['name' => 'Jane Technician']);
});

test('ticket show page can be rendered', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create([
            'created_by' => $this->createdBy->id,
            'assigned_to' => $this->assignedTo->id,
        ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertSee($ticket->ticket_number)
        ->assertSee($ticket->problem_description)
        ->assertSee($this->customer->full_name);
});

test('ticket shows customer information', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee($this->customer->full_name)
        ->assertSee($this->customer->email)
        ->assertSee($this->customer->phone);
});

test('ticket shows device information', function (): void {
    $device = Device::factory()->for($this->customer)->create([
        'brand' => 'Apple',
        'model' => 'iPhone 14',
        'serial_number' => 'SN123456',
        'imei' => '123456789012345',
    ]);

    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($device)
        ->create(['created_by' => $this->createdBy->id]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee('Apple iPhone 14')
        ->assertSee('SN123456')
        ->assertSee('123456789012345');
});

test('ticket shows status and priority badges', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create([
            'status' => TicketStatus::InProgress,
            'priority' => TicketPriority::Urgent,
            'created_by' => $this->createdBy->id,
        ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee($ticket->status->label())
        ->assertSee($ticket->priority->label());
});

test('ticket shows assigned technician', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create([
            'created_by' => $this->createdBy->id,
            'assigned_to' => $this->assignedTo->id,
        ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee($this->assignedTo->name);
});

test('ticket shows unassigned if no technician assigned', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create([
            'created_by' => $this->createdBy->id,
            'assigned_to' => null,
        ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee('Unassigned');
});

test('ticket shows diagnosis when present', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create([
            'diagnosis' => 'LCD screen needs replacement',
            'created_by' => $this->createdBy->id,
        ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee('LCD screen needs replacement');
});

test('ticket shows estimated completion date', function (): void {
    $estimatedDate = now()->addDays(5);
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create([
            'estimated_completion' => $estimatedDate,
            'created_by' => $this->createdBy->id,
        ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee($estimatedDate->format('M d, Y'));
});

test('ticket shows actual completion date when completed', function (): void {
    $completedDate = now();
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->completed()
        ->create([
            'actual_completion' => $completedDate,
            'created_by' => $this->createdBy->id,
        ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee($completedDate->format('M d, Y'));
});

test('ticket shows parts with pricing', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    TicketPart::factory()->for($ticket)->create([
        'part_name' => 'LCD Screen',
        'quantity' => 1,
        'selling_price' => 150.00,
    ]);

    TicketPart::factory()->for($ticket)->create([
        'part_name' => 'Battery',
        'quantity' => 2,
        'selling_price' => 25.00,
    ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee('LCD Screen')
        ->assertSee('Battery')
        ->assertSee('150.00')
        ->assertSee('25.00')
        ->assertSee('200.00'); // Total (150 + 25*2)
});

test('ticket shows notes in timeline', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    TicketNote::factory()->for($ticket)->for($this->user)->create([
        'note' => 'Customer called to check status',
        'is_internal' => false,
    ]);

    TicketNote::factory()->for($ticket)->for($this->assignedTo)->create([
        'note' => 'Waiting for part delivery',
        'is_internal' => true,
    ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee('Customer called to check status')
        ->assertSee('Waiting for part delivery')
        ->assertSee('Internal');
});

test('ticket shows invoice information when invoice exists', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    $invoice = Invoice::factory()
        ->for($ticket)
        ->for($this->customer)
        ->create([
            'invoice_number' => 'INV-12345',
            'total' => 250.00,
        ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee('INV-12345')
        ->assertSee('250.00');
});

test('ticket shows payment information when payments exist', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    $invoice = Invoice::factory()
        ->for($ticket)
        ->for($this->customer)
        ->create(['total' => 250.00]);

    Payment::factory()->for($invoice)->for($ticket)->create([
        'amount' => 150.00,
        'processed_by' => $this->user->id,
    ]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee('150.00')
        ->assertSee('100.00'); // Balance due
});

test('ticket shows edit button when user can update', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    actingAs($this->user)
        ->get(route('tickets.show', $ticket))
        ->assertSee('Edit Ticket');
});

test('unauthorized user cannot view ticket', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['created_by' => $this->createdBy->id]);

    get(route('tickets.show', $ticket))
        ->assertRedirect(route('login'));
});

test('component loads with all relationships', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->has(TicketNote::factory()->for($this->user)->count(2), 'notes')
        ->has(TicketPart::factory()->count(2), 'parts')
        ->create([
            'created_by' => $this->createdBy->id,
            'assigned_to' => $this->assignedTo->id,
        ]);

    $invoice = Invoice::factory()
        ->for($ticket)
        ->for($this->customer)
        ->has(Payment::factory()->state(['processed_by' => $this->user->id]), 'payments')
        ->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Show::class, ['ticket' => $ticket])
        ->assertSet('ticket.id', $ticket->id)
        ->assertSee($ticket->ticket_number)
        ->assertSee($this->customer->full_name)
        ->assertSee($this->device->device_name)
        ->assertOk();
});
