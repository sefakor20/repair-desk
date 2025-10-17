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
        'invoice_number' => 'INV-2024-001',
        'subtotal' => 150.00,
        'tax_rate' => 10.0,
        'tax_amount' => 15.00,
        'discount' => 0.00,
        'total' => 165.00,
        'status' => InvoiceStatus::Pending,
    ]);
});

it('generates invoice PDF successfully with valid token', function (): void {
    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertDownload("invoice-{$this->invoice->invoice_number}.pdf");
});

it('validates customer portal token', function (): void {
    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'invalid-token',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertRedirect();
});

it('validates invoice ownership', function (): void {
    $otherCustomer = Customer::factory()->create();
    $otherInvoice = Invoice::factory()->create([
        'customer_id' => $otherCustomer->id,
    ]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $otherInvoice->id,
    ]));

    $response->assertForbidden();
});

it('generates PDF for paid invoice', function (): void {
    $this->invoice->update(['status' => InvoiceStatus::Paid]);

    $user = User::factory()->create();
    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 165.00,
        'payment_method' => PaymentMethod::Card,
        'processed_by' => $user->id,
    ]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

it('generates PDF for partially paid invoice', function (): void {
    $user = User::factory()->create();
    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 80.00,
        'payment_method' => PaymentMethod::Cash,
        'processed_by' => $user->id,
    ]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});



it('generates PDF for cancelled invoice', function (): void {
    $this->invoice->update(['status' => InvoiceStatus::Cancelled]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertSuccessful();
});

it('generates PDF with invoice notes', function (): void {
    $this->invoice->update([
        'notes' => 'Thank you for your business. Please pay within 30 days.',
    ]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertSuccessful();
});

it('generates PDF with discount applied', function (): void {
    $this->invoice->update([
        'subtotal' => 200.00,
        'discount' => 50.00,
        'tax_amount' => 15.00,
        'total' => 165.00,
    ]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertSuccessful();
});

it('generates PDF with multiple payments', function (): void {
    $user = User::factory()->create();

    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 50.00,
        'payment_method' => PaymentMethod::Cash,
        'processed_by' => $user->id,
    ]);

    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 50.00,
        'payment_method' => PaymentMethod::Card,
        'processed_by' => $user->id,
    ]);

    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 65.00,
        'payment_method' => PaymentMethod::MobileMoney,
        'processed_by' => $user->id,
    ]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertSuccessful();
});

it('requires valid customer model', function (): void {
    $response = get(route('portal.invoices.pdf', [
        'customer' => 99999,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertNotFound();
});

it('requires valid invoice model', function (): void {
    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => 99999,
    ]));

    $response->assertNotFound();
});

it('fails when customer has no portal access token', function (): void {
    $this->customer->update(['portal_access_token' => null]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertRedirect();
});

it('generates PDF with customer without email', function (): void {
    $this->customer->update(['email' => null]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertSuccessful();
});

it('generates PDF for invoice without ticket', function (): void {
    $standaloneInvoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => null,
        'total' => 100.00,
    ]);

    $response = get(route('portal.invoices.pdf', [
        'customer' => $this->customer->id,
        'token' => 'test-token-123',
        'invoice' => $standaloneInvoice->id,
    ]));

    $response->assertSuccessful();
});
