<?php

declare(strict_types=1);

use App\Enums\{InvoiceStatus, PaymentMethod};
use App\Livewire\Invoices\Show;
use App\Models\{Invoice, Payment, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('show invoice page can be rendered', function () {
    $invoice = Invoice::factory()->create();

    get(route('invoices.show', $invoice))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});

test('show page displays invoice details', function () {
    $invoice = Invoice::factory()->create([
        'invoice_number' => 'INV-TEST-001',
        'subtotal' => '100.00',
        'tax_rate' => '10.00',
        'tax_amount' => '10.00',
        'discount' => '5.00',
        'total' => '105.00',
        'status' => InvoiceStatus::Pending,
    ]);

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->assertSee('INV-TEST-001')
        ->assertSee($invoice->customer->name)
        ->assertSee($invoice->ticket->ticket_number)
        ->assertSee('$100.00')
        ->assertSee('$105.00')
        ->assertSee('Pending');
});

test('authorized user can open payment modal', function () {
    $frontDesk = User::factory()->create(); // Default role is FrontDesk
    actingAs($frontDesk);

    $invoice = Invoice::factory()->pending()->create();

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->call('openPaymentModal')
        ->assertSet('showPaymentModal', true)
        ->assertSet('amount', (string) $invoice->balance_due);
});

test('unauthorized user cannot open payment modal', function () {
    $technician = User::factory()->technician()->create();
    actingAs($technician);

    $invoice = Invoice::factory()->pending()->create();

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->call('openPaymentModal')
        ->assertForbidden();
});

test('can close payment modal', function () {
    $frontDesk = User::factory()->create(); // Default role is FrontDesk
    actingAs($frontDesk);

    $invoice = Invoice::factory()->pending()->create();

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->set('showPaymentModal', true)
        ->call('closePaymentModal')
        ->assertSet('showPaymentModal', false);
});

test('can record payment', function () {
    $frontDesk = User::factory()->create(); // Default role is FrontDesk
    actingAs($frontDesk);

    $invoice = Invoice::factory()->create(['total' => '100.00']);

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->set('amount', '50.00')
        ->set('paymentMethod', 'cash')
        ->set('transactionReference', 'REF-001')
        ->set('paymentNotes', 'Partial payment')
        ->call('recordPayment')
        ->assertHasNoErrors()
        ->assertSet('showPaymentModal', false);

    $payment = Payment::where('invoice_id', $invoice->id)->first();

    expect($payment)->not->toBeNull()
        ->and($payment->amount)->toBe('50.00')
        ->and($payment->payment_method)->toBe(PaymentMethod::Cash)
        ->and($payment->transaction_reference)->toBe('REF-001')
        ->and($payment->notes)->toBe('Partial payment')
        ->and($payment->processed_by)->toBe($frontDesk->id);
});

test('invoice status updates to paid when fully paid', function () {
    $frontDesk = User::factory()->create(); // Default role is FrontDesk
    actingAs($frontDesk);

    $invoice = Invoice::factory()->pending()->create(['total' => '100.00']);

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->set('amount', '100.00')
        ->set('paymentMethod', 'card')
        ->call('recordPayment')
        ->assertHasNoErrors();

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::Paid);
});

test('payment amount is required', function () {
    $frontDesk = User::factory()->create(); // Default role is FrontDesk
    actingAs($frontDesk);

    $invoice = Invoice::factory()->create();

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->set('paymentMethod', 'cash')
        ->call('recordPayment')
        ->assertHasErrors(['amount' => 'required']);
});

test('payment amount must be numeric', function () {
    $frontDesk = User::factory()->create(); // Default role is FrontDesk
    actingAs($frontDesk);

    $invoice = Invoice::factory()->create();

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->set('amount', 'invalid')
        ->set('paymentMethod', 'cash')
        ->call('recordPayment')
        ->assertHasErrors(['amount' => 'numeric']);
});

test('payment amount cannot exceed balance due', function () {
    $frontDesk = User::factory()->create(); // Default role is FrontDesk
    actingAs($frontDesk);

    $invoice = Invoice::factory()->create(['total' => '100.00']);

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->set('amount', '150.00')
        ->set('paymentMethod', 'cash')
        ->call('recordPayment')
        ->assertHasErrors(['amount' => 'max']);
});

test('payment method is required', function () {
    $frontDesk = User::factory()->create(); // Default role is FrontDesk
    actingAs($frontDesk);

    $invoice = Invoice::factory()->create();

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->set('amount', '50.00')
        ->call('recordPayment')
        ->assertHasErrors(['paymentMethod' => 'required']);
});

test('shows payment history', function () {
    $invoice = Invoice::factory()->create();
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => '50.00',
        'payment_method' => PaymentMethod::Card,
    ]);

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->assertSee('$50.00')
        ->assertSee('Card')
        ->assertSee($payment->processedBy->name);
});

test('displays balance due correctly', function () {
    $invoice = Invoice::factory()->create(['total' => '100.00']);
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => '30.00',
    ]);

    $invoice->refresh();

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->assertSee('$100.00') // Total
        ->assertSee('$30.00') // Total Paid
        ->assertSee('$70.00'); // Balance Due
});
