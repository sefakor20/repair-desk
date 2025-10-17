<?php

declare(strict_types=1);

use App\Enums\{InvoiceStatus, PaymentMethod, TicketStatus};
use App\Models\{Customer, Device, Invoice, Payment, Ticket, User};

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->customer = Customer::factory()->create([
        'portal_access_token' => 'test-token-123',
    ]);

    $this->device = Device::factory()->create([
        'customer_id' => $this->customer->id,
    ]);

    $this->ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
        'status' => TicketStatus::Completed,
    ]);

    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $this->ticket->id,
        'total' => 165.00,
        'status' => InvoiceStatus::Paid,
    ]);

    $this->user = User::factory()->create();

    $this->payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'ticket_id' => $this->ticket->id,
        'amount' => 165.00,
        'payment_method' => PaymentMethod::Card,
        'transaction_reference' => 'PAY-' . now()->timestamp,
        'processed_by' => $this->user->id,
    ]);
});

it('generates receipt PDF successfully with valid token', function (): void {
    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $this->payment->id,
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertDownload("receipt-{$this->payment->transaction_reference}.pdf");
});

it('validates customer portal token', function (): void {
    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'invalid-token',
        'payment' => $this->payment->id,
    ]));

    $response->assertRedirect();
});

it('validates payment ownership through invoice', function (): void {
    $otherCustomer = Customer::factory()->create();
    $otherInvoice = Invoice::factory()->create([
        'customer_id' => $otherCustomer->id,
    ]);
    $otherPayment = Payment::factory()->create([
        'invoice_id' => $otherInvoice->id,
        'processed_by' => $this->user->id,
    ]);

    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $otherPayment->id,
    ]));

    $response->assertForbidden();
});

it('generates receipt for cash payment', function (): void {
    $cashPayment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 50.00,
        'payment_method' => PaymentMethod::Cash,
        'processed_by' => $this->user->id,
    ]);

    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $cashPayment->id,
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

it('generates receipt for mobile money payment', function (): void {
    $momoPayment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 75.00,
        'payment_method' => PaymentMethod::MobileMoney,
        'processed_by' => $this->user->id,
    ]);

    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $momoPayment->id,
    ]));

    $response->assertSuccessful();
});

it('generates receipt for bank transfer payment', function (): void {
    $bankPayment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 100.00,
        'payment_method' => PaymentMethod::BankTransfer,
        'processed_by' => $this->user->id,
    ]);

    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $bankPayment->id,
    ]));

    $response->assertSuccessful();
});

it('generates receipt with payment notes', function (): void {
    $this->payment->update([
        'notes' => 'Paid in full via online payment',
    ]);

    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $this->payment->id,
    ]));

    $response->assertSuccessful();
});

it('generates receipt for partial payment', function (): void {
    $this->invoice->update([
        'status' => InvoiceStatus::Pending,
        'total' => 300.00,
    ]);

    $partialPayment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 100.00,
        'payment_method' => PaymentMethod::Card,
        'processed_by' => $this->user->id,
    ]);

    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $partialPayment->id,
    ]));

    $response->assertSuccessful();
});

it('requires valid customer model', function (): void {
    $response = get(route('portal.invoices.receipt', [
        'customer' => 99999,
        'token' => 'test-token-123',
        'payment' => $this->payment->id,
    ]));

    $response->assertNotFound();
});

it('requires valid payment model', function (): void {
    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => 99999,
    ]));

    $response->assertNotFound();
});

it('fails when customer has no portal access token', function (): void {
    $this->customer->update(['portal_access_token' => null]);

    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $this->payment->id,
    ]));

    $response->assertRedirect();
});

it('generates receipt with customer without email', function (): void {
    $this->customer->update(['email' => null]);

    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $this->payment->id,
    ]));

    $response->assertSuccessful();
});

it('generates receipt for payment without ticket', function (): void {
    $standaloneInvoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => null,
        'total' => 100.00,
    ]);

    $standalonePayment = Payment::factory()->create([
        'invoice_id' => $standaloneInvoice->id,
        'ticket_id' => null,
        'amount' => 100.00,
        'payment_method' => PaymentMethod::Cash,
        'processed_by' => $this->user->id,
    ]);

    $response = get(route('portal.invoices.receipt', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'payment' => $standalonePayment->id,
    ]));

    $response->assertSuccessful();
});
