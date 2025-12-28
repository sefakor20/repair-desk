<?php

declare(strict_types=1);

use App\Enums\{InvoiceStatus, TicketStatus};
use App\Livewire\Reports\Index;
use App\Models\{Customer, Device, Invoice, InventoryItem, Payment, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

test('reports page can be rendered by admin', function (): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    get(route('reports.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

test('reports page can be rendered by manager', function (): void {
    $manager = User::factory()->manager()->create();
    actingAs($manager);

    get(route('reports.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

test('unauthorized users cannot access reports page', function (): void {
    $technician = User::factory()->technician()->create();
    actingAs($technician);

    get(route('reports.index'))
        ->assertForbidden();
});

test('sales report displays revenue metrics', function (): void {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create(['created_by' => $admin->id]);

    $invoice = Invoice::factory()->for($ticket)->for($customer)->create([
        'status' => InvoiceStatus::Paid,
        'total' => 500.00,
    ]);

    Payment::factory()->for($invoice)->for($ticket)->create([
        'amount' => 500.00,
        'payment_date' => now(),
        'processed_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->assertSee('Total Revenue')
        ->assertSee('500.00');
});

test('payment history displays all payments', function (): void {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create(['created_by' => $admin->id]);
    $invoice = Invoice::factory()->for($ticket)->for($customer)->create();

    Payment::factory()->for($invoice)->for($ticket)->create([
        'amount' => 250.00,
        'payment_date' => now(),
        'processed_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('tab', 'payments')
        ->assertSee('Total Collected')
        ->assertSee('250.00');
});

test('technician performance report shows metrics', function (): void {
    $admin = User::factory()->admin()->create();
    $technician = User::factory()->technician()->create();
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();

    Ticket::factory()->for($customer)->for($device)->create([
        'status' => TicketStatus::Completed,
        'assigned_to' => $technician->id,
        'created_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('tab', 'technicians')
        ->assertSee('Technician Performance')
        ->assertSee($technician->name);
});

test('inventory report shows stock metrics', function (): void {
    $admin = User::factory()->admin()->create();

    InventoryItem::factory()->create([
        'name' => 'Test Part',
        'quantity' => 5,
        'reorder_level' => 10,
        'cost_price' => 10.00,
        'selling_price' => 20.00,
    ]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('tab', 'inventory')
        ->assertSee('Inventory Value')
        ->assertSee('Low Stock Items');
});

test('date range filter works correctly', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('startDate', '2025-01-01')
        ->set('endDate', '2025-01-31')
        ->assertSet('startDate', '2025-01-01')
        ->assertSet('endDate', '2025-01-31');
});

test('end date must be after start date', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('startDate', '2025-01-31')
        ->set('endDate', '2025-01-01')
        ->assertHasErrors(['endDate']);
});

test('tabs switch correctly', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->assertSet('tab', 'sales')
        ->set('tab', 'payments')
        ->assertSet('tab', 'payments')
        ->set('tab', 'technicians')
        ->assertSet('tab', 'technicians')
        ->set('tab', 'inventory')
        ->assertSet('tab', 'inventory');
});

test('sales report calculates average transaction correctly', function (): void {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();

    // Create 2 paid invoices
    for ($i = 0; $i < 2; $i++) {
        $ticket = Ticket::factory()->for($customer)->for($device)->create(['created_by' => $admin->id]);
        Invoice::factory()->for($ticket)->for($customer)->create([
            'status' => InvoiceStatus::Paid,
            'total' => 100.00,
        ]);
    }

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->assertSee('Avg Transaction')
        ->assertSee('100.00');
});

test('payment history groups by payment method', function (): void {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();
    $device = Device::factory()->for($customer)->create();
    $ticket = Ticket::factory()->for($customer)->for($device)->create(['created_by' => $admin->id]);
    $invoice = Invoice::factory()->for($ticket)->for($customer)->create();

    Payment::factory()->for($invoice)->for($ticket)->create([
        'amount' => 100.00,
        'payment_method' => 'cash',
        'payment_date' => now(),
        'processed_by' => $admin->id,
    ]);

    Payment::factory()->for($invoice)->for($ticket)->create([
        'amount' => 200.00,
        'payment_method' => 'card',
        'payment_date' => now(),
        'processed_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('tab', 'payments')
        ->assertSee('Payments by Method')
        ->assertSee('Cash')
        ->assertSee('Card');
});
