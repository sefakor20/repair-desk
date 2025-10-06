<?php

declare(strict_types=1);

use App\Enums\{InvoiceStatus, TicketPriority, TicketStatus};
use App\Models\{Customer, Device, InventoryItem, Invoice, Payment, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('dashboard page can be rendered', function (): void {
    actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Overview of your repair shop operations');
});

test('dashboard shows urgent tickets count', function (): void {
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();

    // Create urgent tickets that are active
    Ticket::factory()
        ->for($customer)
        ->for($device)
        ->count(3)
        ->create([
            'priority' => TicketPriority::Urgent,
            'status' => TicketStatus::New,
            'created_by' => $this->user->id,
        ]);

    // Create urgent but delivered ticket (should not count)
    Ticket::factory()
        ->for($customer)
        ->for($device)
        ->create([
            'priority' => TicketPriority::Urgent,
            'status' => TicketStatus::Delivered,
            'created_by' => $this->user->id,
        ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->assertSee('3')
        ->assertSee('Urgent Tickets');
});

test('dashboard shows today revenue', function (): void {
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create(['created_by' => $this->user->id]);
    $invoice = Invoice::factory()->for($ticket)->for($customer)->create();

    // Today's payments
    Payment::factory()->for($invoice)->for($ticket)->create([
        'amount' => 150.00,
        'payment_date' => today(),
        'processed_by' => $this->user->id,
    ]);

    Payment::factory()->for($invoice)->for($ticket)->create([
        'amount' => 75.50,
        'payment_date' => today(),
        'processed_by' => $this->user->id,
    ]);

    // Yesterday's payment (should not count)
    Payment::factory()->for($invoice)->for($ticket)->create([
        'amount' => 100.00,
        'payment_date' => today()->subDay(),
        'processed_by' => $this->user->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->assertSee('225.50')
        ->assertSee('Today\'s Revenue');
});

test('dashboard shows pending invoices count and total', function (): void {
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();
    $ticket1 = Ticket::factory()->for($customer)->for($device)->create(['created_by' => $this->user->id]);
    $ticket2 = Ticket::factory()->for($customer)->for($device)->create(['created_by' => $this->user->id]);
    $ticket3 = Ticket::factory()->for($customer)->for($device)->create(['created_by' => $this->user->id]);

    // Pending invoice with no payment
    Invoice::factory()->for($ticket1)->for($customer)->create([
        'status' => InvoiceStatus::Pending,
        'total' => 200.00,
    ]);

    // Pending invoice with partial payment
    $invoice2 = Invoice::factory()->for($ticket2)->for($customer)->create([
        'status' => InvoiceStatus::Pending,
        'total' => 300.00,
    ]);
    Payment::factory()->for($invoice2)->for($ticket2)->create([
        'amount' => 100.00,
        'payment_date' => now(),
        'processed_by' => $this->user->id,
    ]);

    // Paid invoice (should not count)
    $invoice3 = Invoice::factory()->for($ticket3)->for($customer)->create([
        'status' => InvoiceStatus::Paid,
        'total' => 150.00,
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->assertSee('2') // Count of pending invoices
        ->assertSee('400.00') // Total outstanding (200 + 200)
        ->assertSee('Pending Invoices');
});

test('dashboard shows low stock items count', function (): void {
    // Items at or below low stock threshold
    InventoryItem::factory()->create([
        'quantity' => 5,
        'reorder_level' => 10,
    ]);

    InventoryItem::factory()->create([
        'quantity' => 10,
        'reorder_level' => 10,
    ]);

    // Item with sufficient stock
    InventoryItem::factory()->create([
        'quantity' => 50,
        'reorder_level' => 10,
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->assertSee('2')
        ->assertSee('Low Stock Items');
});

test('dashboard shows tickets by status breakdown', function (): void {
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();

    // Create tickets with different statuses
    Ticket::factory()->for($customer)->for($device)->count(3)->create(['status' => TicketStatus::New, 'created_by' => $this->user->id]);
    Ticket::factory()->for($customer)->for($device)->count(5)->create(['status' => TicketStatus::InProgress, 'created_by' => $this->user->id]);
    Ticket::factory()->for($customer)->for($device)->count(2)->create(['status' => TicketStatus::WaitingForParts, 'created_by' => $this->user->id]);
    Ticket::factory()->for($customer)->for($device)->count(4)->create(['status' => TicketStatus::Completed, 'created_by' => $this->user->id]);
    Ticket::factory()->for($customer)->for($device)->count(1)->create(['status' => TicketStatus::Delivered, 'created_by' => $this->user->id]);

    actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSee('Tickets by Status')
        ->assertSee('3') // New
        ->assertSee('5') // In Progress
        ->assertSee('2') // Waiting for parts
        ->assertSee('4') // Completed
        ->assertSee('1'); // Delivered
});

test('dashboard shows recent tickets with details', function (): void {
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();
    $technician = User::factory()->technician()->create();

    $ticket = Ticket::factory()
        ->for($customer)
        ->for($device)
        ->create([
            'created_by' => $this->user->id,
            'assigned_to' => $technician->id,
            'status' => TicketStatus::InProgress,
        ]);

    actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSee($ticket->ticket_number)
        ->assertSee($customer->full_name)
        ->assertSee($device->device_name)
        ->assertSee($technician->name);
});

test('dashboard limits recent tickets to 5', function (): void {
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();

    // Create 10 tickets
    $tickets = Ticket::factory()
        ->for($customer)
        ->for($device)
        ->count(10)
        ->create(['created_by' => $this->user->id]);

    $response = actingAs($this->user)
        ->get(route('dashboard'));

    // Should see first 5 tickets (most recent)
    $recentFive = $tickets->sortByDesc('created_at')->take(5);
    foreach ($recentFive as $ticket) {
        $response->assertSee($ticket->ticket_number);
    }
});

test('dashboard shows message when no tickets exist', function (): void {
    actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSee('No tickets yet');
});

test('dashboard shows unassigned for tickets without technician', function (): void {
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();

    Ticket::factory()
        ->for($customer)
        ->for($device)
        ->create([
            'created_by' => $this->user->id,
            'assigned_to' => null,
        ]);

    actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSee('Unassigned');
});

test('dashboard view all link navigates to tickets index', function (): void {
    actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSee('View all')
        ->assertSee(route('tickets.index'));
});

test('unauthorized user cannot access dashboard', function (): void {
    get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('dashboard displays all status badges', function (): void {
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();

    // Create at least one ticket of each status
    Ticket::factory()->for($customer)->for($device)->create(['status' => TicketStatus::New, 'created_by' => $this->user->id]);
    Ticket::factory()->for($customer)->for($device)->create(['status' => TicketStatus::InProgress, 'created_by' => $this->user->id]);
    Ticket::factory()->for($customer)->for($device)->create(['status' => TicketStatus::WaitingForParts, 'created_by' => $this->user->id]);
    Ticket::factory()->for($customer)->for($device)->create(['status' => TicketStatus::Completed, 'created_by' => $this->user->id]);
    Ticket::factory()->for($customer)->for($device)->create(['status' => TicketStatus::Delivered, 'created_by' => $this->user->id]);

    actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSee('Tickets by Status')
        ->assertSee(TicketStatus::New->label())
        ->assertSee(TicketStatus::InProgress->label())
        ->assertSee(TicketStatus::WaitingForParts->label())
        ->assertSee(TicketStatus::Completed->label())
        ->assertSee(TicketStatus::Delivered->label());
});
