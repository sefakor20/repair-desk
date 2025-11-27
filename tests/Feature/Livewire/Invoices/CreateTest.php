<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Create;
use App\Models\{Invoice, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    $this->user = createAdmin(); // Default role is FrontDesk
    actingAs($this->user);
});

test('create invoice page can be rendered', function () {
    get(route('invoices.create'))
        ->assertOk()
        ->assertSeeLivewire(Create::class);
});

test('unauthorized user cannot access create invoice page', function () {
    $technician = User::factory()->technician()->create();
    actingAs($technician);

    get(route('invoices.create'))
        ->assertForbidden();
});

test('can create invoice', function () {
    $ticket = Ticket::factory()->create();

    Livewire::test(Create::class)
        ->set('ticketId', $ticket->id)
        ->set('subtotal', '100.00')
        ->set('taxRate', '10')
        ->set('discount', '5.00')
        ->set('notes', 'Test invoice notes')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('invoices.index'));

    $invoice = Invoice::where('ticket_id', $ticket->id)->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->customer_id)->toBe($ticket->customer_id)
        ->and($invoice->subtotal)->toBe('100.00')
        ->and($invoice->tax_rate)->toBe('10.00')
        ->and($invoice->discount)->toBe('5.00')
        ->and($invoice->total)->toBe('104.50') // (100 - 5) * 1.10
        ->and($invoice->status)->toBe(InvoiceStatus::Pending)
        ->and($invoice->notes)->toBe('Test invoice notes');
});

test('ticket field is required', function () {
    Livewire::test(Create::class)
        ->set('subtotal', '100.00')
        ->call('create')
        ->assertHasErrors(['ticketId' => 'required']);
});

test('subtotal field is required', function () {
    $ticket = Ticket::factory()->create();

    Livewire::test(Create::class)
        ->set('ticketId', $ticket->id)
        ->call('create')
        ->assertHasErrors(['subtotal' => 'required']);
});

test('subtotal must be numeric', function () {
    $ticket = Ticket::factory()->create();

    Livewire::test(Create::class)
        ->set('ticketId', $ticket->id)
        ->set('subtotal', 'invalid')
        ->call('create')
        ->assertHasErrors(['subtotal' => 'numeric']);
});

test('subtotal must be at least 0', function () {
    $ticket = Ticket::factory()->create();

    Livewire::test(Create::class)
        ->set('ticketId', $ticket->id)
        ->set('subtotal', '-10')
        ->call('create')
        ->assertHasErrors(['subtotal' => 'min']);
});

test('tax rate must be between 0 and 100', function () {
    $ticket = Ticket::factory()->create();

    Livewire::test(Create::class)
        ->set('ticketId', $ticket->id)
        ->set('subtotal', '100')
        ->set('taxRate', '150')
        ->call('create')
        ->assertHasErrors(['taxRate' => 'max']);
});

test('discount must be at least 0', function () {
    $ticket = Ticket::factory()->create();

    Livewire::test(Create::class)
        ->set('ticketId', $ticket->id)
        ->set('subtotal', '100')
        ->set('discount', '-10')
        ->call('create')
        ->assertHasErrors(['discount' => 'min']);
});

test('cannot create invoice for ticket that already has one', function () {
    $ticket = Ticket::factory()->create();
    Invoice::factory()->create(['ticket_id' => $ticket->id]);

    Livewire::test(Create::class)
        ->set('ticketId', $ticket->id)
        ->set('subtotal', '100')
        ->call('create')
        ->assertHasErrors(['ticketId']);
});

test('calculates totals correctly', function () {
    $ticket = Ticket::factory()->create();

    Livewire::test(Create::class)
        ->set('ticketId', $ticket->id)
        ->set('subtotal', '200.00')
        ->set('taxRate', '15')
        ->set('discount', '20.00')
        ->call('create')
        ->assertHasNoErrors();

    $invoice = Invoice::where('ticket_id', $ticket->id)->first();

    // (200 - 20) * 1.15 = 180 * 1.15 = 207
    expect($invoice->total)->toBe('207.00')
        ->and($invoice->tax_amount)->toBe('27.00');
});
