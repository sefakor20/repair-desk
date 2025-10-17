<?php

declare(strict_types=1);

use App\Mail\PaymentReceiptMail;
use App\Models\{Customer, Device, Invoice, Payment, Ticket};
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->customer = Customer::factory()->create([
        'email' => 'customer@example.com',
        'portal_access_token' => 'test-token-123',
    ]);

    $this->device = Device::factory()->create([
        'customer_id' => $this->customer->id,
    ]);

    $this->ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
    ]);

    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $this->ticket->id,
        'subtotal' => 100.00,
        'tax_amount' => 10.00,
        'total' => 110.00,
    ]);

    $this->payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'ticket_id' => $this->ticket->id,
        'amount' => 110.00,
        'transaction_reference' => 'TXN-12345',
    ]);
});

test('payment receipt email has correct subject', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    expect($mailable->envelope()->subject)
        ->toBe('Payment Receipt - TXN-12345');
});

test('payment receipt email uses correct markdown template', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    expect($mailable->content()->markdown)
        ->toBe('emails.payment-receipt');
});

test('payment receipt email attaches PDF', function () {
    $mailable = new PaymentReceiptMail($this->payment);
    $attachments = $mailable->attachments();

    expect($attachments)->toHaveCount(1)
        ->and($attachments[0])->toBeInstanceOf(\Illuminate\Mail\Mailables\Attachment::class);
});

test('payment receipt email includes payment amount', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    $mailable->assertSeeInHtml('110.00');
});

test('payment receipt email includes transaction reference', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    $mailable->assertSeeInHtml('TXN-12345');
});

test('payment receipt email includes invoice number', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    $mailable->assertSeeInHtml($this->invoice->invoice_number);
});

test('payment receipt email includes customer name', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    $mailable->assertSeeInHtml($this->customer->first_name);
});

test('payment receipt email includes ticket information', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    $mailable->assertSeeInHtml($this->ticket->ticket_number);
});

test('payment receipt email includes device information', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    $mailable->assertSeeInHtml($this->device->brand)
        ->assertSeeInHtml($this->device->model);
});

test('payment receipt email shows balance due', function () {
    // Create partial payment
    $partialPayment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'ticket_id' => $this->ticket->id,
        'amount' => 50.00,
        'transaction_reference' => 'TXN-PARTIAL',
    ]);

    $mailable = new PaymentReceiptMail($partialPayment);

    $mailable->assertSeeInHtml('60.00'); // Balance due
});

test('payment receipt email shows fully paid status', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    $mailable->assertSeeInHtml('Invoice Fully Paid');
});

test('payment receipt email includes portal link when ticket exists', function () {
    $mailable = new PaymentReceiptMail($this->payment);

    $portalUrl = route('portal.tickets.show', [
        'customer' => $this->customer->id,
        'token' => $this->customer->portal_access_token,
        'ticket' => $this->ticket->id,
    ]);

    $mailable->assertSeeInHtml($portalUrl);
});

test('payment receipt email handles payment without ticket', function () {
    $invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => null,
        'subtotal' => 50.00,
        'tax_amount' => 5.00,
        'total' => 55.00,
    ]);

    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'ticket_id' => null,
        'amount' => 55.00,
        'transaction_reference' => 'TXN-NO-TICKET',
    ]);

    $mailable = new PaymentReceiptMail($payment);

    expect($mailable->envelope()->subject)
        ->toBe('Payment Receipt - TXN-NO-TICKET');
});

test('payment receipt email is queued', function () {
    expect(new PaymentReceiptMail($this->payment))
        ->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});

test('payment receipt email sends successfully', function () {
    Mail::fake();

    Mail::to($this->customer->email)->send(new PaymentReceiptMail($this->payment));

    Mail::assertQueued(PaymentReceiptMail::class, function ($mail) {
        return $mail->payment->id === $this->payment->id;
    });
});

test('payment receipt email loads required relationships', function () {
    $payment = Payment::find($this->payment->id);

    $mailable = new PaymentReceiptMail($payment);

    expect($mailable->payment->relationLoaded('invoice'))->toBeTrue()
        ->and($mailable->payment->invoice->relationLoaded('customer'))->toBeTrue()
        ->and($mailable->payment->invoice->relationLoaded('ticket'))->toBeTrue();
});
