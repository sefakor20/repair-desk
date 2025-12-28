<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Index;
use App\Models\{Customer, Invoice, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function (): void {
    $this->user = createAdmin();
    actingAs($this->user);
});

test('invoices page can be rendered', function (): void {
    get(route('invoices.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

test('invoices page displays invoices', function (): void {
    $ticket = Ticket::factory()->create();
    $invoice = Invoice::factory()->create([
        'ticket_id' => $ticket->id,
        'customer_id' => $ticket->customer_id,
        'invoice_number' => 'INV-TEST-001',
        'total' => 150.00,
        'status' => InvoiceStatus::Pending,
    ]);

    Livewire::test(Index::class)
        ->assertSee('INV-TEST-001')
        ->assertSee($invoice->customer->name)
        ->assertSee($ticket->ticket_number)
        ->assertSee('GHS', false)
        ->assertSee('150.00')
        ->assertSee('Pending');
});

test('invoices page shows empty state when no invoices exist', function (): void {
    Livewire::test(Index::class)
        ->assertSee('No invoices yet.');
});

test('invoices page shows filtered empty state', function (): void {
    Invoice::factory()->create(['invoice_number' => 'INV-001']);

    Livewire::test(Index::class)
        ->set('search', 'NONEXISTENT')
        ->assertSee('No invoices found matching your filters.');
});

test('can search invoices by invoice number', function (): void {
    $invoice1 = Invoice::factory()->create(['invoice_number' => 'INV-001']);
    $invoice2 = Invoice::factory()->create(['invoice_number' => 'INV-002']);

    Livewire::test(Index::class)
        ->set('search', 'INV-001')
        ->assertSee('INV-001')
        ->assertDontSee('INV-002');
});

test('can search invoices by customer name', function (): void {
    $customer1 = Customer::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
    $customer2 = Customer::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

    $ticket1 = Ticket::factory()->create(['customer_id' => $customer1->id]);
    $ticket2 = Ticket::factory()->create(['customer_id' => $customer2->id]);

    Invoice::factory()->create([
        'ticket_id' => $ticket1->id,
        'customer_id' => $customer1->id,
    ]);
    Invoice::factory()->create([
        'ticket_id' => $ticket2->id,
        'customer_id' => $customer2->id,
    ]);

    Livewire::test(Index::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

test('can filter invoices by status', function (): void {
    $pendingInvoice = Invoice::factory()->pending()->create();
    $paidInvoice = Invoice::factory()->paid()->create();

    Livewire::test(Index::class)
        ->set('status', InvoiceStatus::Paid->value)
        ->assertSee($paidInvoice->invoice_number)
        ->assertDontSee($pendingInvoice->invoice_number);
});

test('can clear filters', function (): void {
    Livewire::test(Index::class)
        ->set('search', 'test')
        ->set('status', 'paid')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('status', '');
});

test('authorized user can delete invoice', function (): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    $invoice = Invoice::factory()->create();

    Livewire::test(Index::class)
        ->call('confirmDelete', $invoice->id)
        ->call('delete')
        ->assertSet('deletingInvoiceId', null);

    expect(Invoice::find($invoice->id))->toBeNull();
});

test('unauthorized user cannot delete invoice', function (): void {
    $technician = User::factory()->technician()->create();
    actingAs($technician);

    $invoice = Invoice::factory()->create();

    Livewire::test(Index::class)
        ->call('confirmDelete', $invoice->id)
        ->call('delete')
        ->assertForbidden();

    expect(Invoice::find($invoice->id))->not->toBeNull();
});

test('can cancel delete confirmation', function (): void {
    $invoice = Invoice::factory()->create();

    Livewire::test(Index::class)
        ->call('confirmDelete', $invoice->id)
        ->assertSet('deletingInvoiceId', $invoice->id)
        ->call('cancelDelete')
        ->assertSet('deletingInvoiceId', null);

    expect(Invoice::find($invoice->id))->not->toBeNull();
});
