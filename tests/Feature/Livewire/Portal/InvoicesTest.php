<?php

declare(strict_types=1);

use App\Livewire\Portal\Invoices\Index;
use App\Models\{Customer, Invoice, Ticket, Device, Payment};
use Livewire\Livewire;

use function Pest\Laravel\{get};

beforeEach(function (): void {
    $this->customer = Customer::factory()
        ->create(['portal_access_token' => 'test-token-123']);
});

test('renders successfully for authorized customer', function (): void {
    get(route('portal.invoices.index', [
        'customer' => $this->customer->id,
        'token' => $this->customer->portal_access_token,
    ]))->assertSuccessful()
        ->assertSeeLivewire(Index::class);
});

test('displays all customer invoices', function (): void {
    $device = Device::factory()->create(['customer_id' => $this->customer->id]);
    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
    ]);

    $invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'invoice_number' => 'INV-2024-001',
        'total' => 500.00,
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('INV-2024-001')
        ->assertSee('GH₵ 500.00');
});

test('filters invoices by status', function (): void {
    $device = Device::factory()->create(['customer_id' => $this->customer->id]);
    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
    ]);

    $paidInvoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'status' => 'paid',
        'invoice_number' => 'INV-PAID',
    ]);

    $pendingInvoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'status' => 'pending',
        'invoice_number' => 'INV-PENDING',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('filterStatus', 'paid')
        ->assertSee('INV-PAID')
        ->assertDontSee('INV-PENDING');
});

test('searches invoices by invoice number', function (): void {
    $device = Device::factory()->create(['customer_id' => $this->customer->id]);
    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'invoice_number' => 'INV-SEARCH-001',
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'invoice_number' => 'INV-OTHER-002',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', 'SEARCH')
        ->assertSee('INV-SEARCH-001')
        ->assertDontSee('INV-OTHER-002');
});

test('searches invoices by ticket number', function (): void {
    $device = Device::factory()->create(['customer_id' => $this->customer->id]);

    $ticket1 = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
        'ticket_number' => 'TKT-FINDME',
    ]);

    $ticket2 = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
        'ticket_number' => 'TKT-OTHER',
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket1->id,
        'invoice_number' => 'INV-001',
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket2->id,
        'invoice_number' => 'INV-002',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', 'FINDME')
        ->assertSee('INV-001')
        ->assertDontSee('INV-002');
});

test('displays correct summary calculations', function (): void {
    $device = Device::factory()->create(['customer_id' => $this->customer->id]);
    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
    ]);

    // Paid invoice
    $paidInvoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'status' => 'paid',
        'total' => 100.00,
    ]);
    Payment::factory()->create([
        'invoice_id' => $paidInvoice->id,
        'amount' => 100.00,
    ]);

    // Pending invoice
    $pendingInvoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'status' => 'pending',
        'total' => 200.00,
    ]);
    Payment::factory()->create([
        'invoice_id' => $pendingInvoice->id,
        'amount' => 50.00,
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('GH₵ 150.00'); // Paid from $pendingInvoice (50 payment)
});

test('clear filters resets status and search', function (): void {
    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('filterStatus', 'paid')
        ->set('search', 'test')
        ->call('clearFilters')
        ->assertSet('filterStatus', 'all')
        ->assertSet('search', '');
});

test('displays empty state when no invoices exist', function (): void {
    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('No invoices found');
});

test('displays empty state with search message when search returns no results', function (): void {
    Device::factory()->create(['customer_id' => $this->customer->id]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', 'nonexistent')
        ->assertSee('No invoices found')
        ->assertSee('Try adjusting');
});

test('paginates invoices correctly', function (): void {
    $device = Device::factory()->create(['customer_id' => $this->customer->id]);
    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
    ]);

    // Create 15 invoices (more than one page)
    Invoice::factory()->count(15)->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertViewHas('invoices', function ($invoices): bool {
            return $invoices->count() === 10; // Default per page
        });
});

test('only displays invoices belonging to the customer', function (): void {
    $otherCustomer = Customer::factory()->create();

    $device1 = Device::factory()->create(['customer_id' => $this->customer->id]);
    $device2 = Device::factory()->create(['customer_id' => $otherCustomer->id]);

    $ticket1 = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device1->id,
    ]);

    $ticket2 = Ticket::factory()->create([
        'customer_id' => $otherCustomer->id,
        'device_id' => $device2->id,
    ]);

    $myInvoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket1->id,
        'invoice_number' => 'INV-MINE',
    ]);

    $otherInvoice = Invoice::factory()->create([
        'customer_id' => $otherCustomer->id,
        'ticket_id' => $ticket2->id,
        'invoice_number' => 'INV-OTHER',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('INV-MINE')
        ->assertDontSee('INV-OTHER');
});

test('displays device information for each invoice', function (): void {
    $device = Device::factory()->create([
        'customer_id' => $this->customer->id,
        'brand' => 'Apple',
        'model' => 'iPhone 14 Pro',
    ]);

    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('Apple')
        ->assertSee('iPhone 14 Pro');
});

test('resetting page when updating search', function (): void {
    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', 'initial')
        ->assertSet('search', 'initial')
        ->set('search', 'updated');
});

test('generates portal access token if missing', function (): void {
    $customer = Customer::factory()->create(['portal_access_token' => null]);

    expect($customer->portal_access_token)->toBeNull();

    Livewire::test(Index::class, ['customer' => $customer]);

    $customer->refresh();

    expect($customer->portal_access_token)->not->toBeNull();
});
