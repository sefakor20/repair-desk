<?php

declare(strict_types=1);

use App\Enums\{InvoiceStatus, PaymentMethod};
use App\Mail\PaymentReceiptMail;
use App\Models\{Customer, Device, Invoice, Payment, Ticket, User};
use App\Services\PaystackService;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\{get, mock};

beforeEach(function (): void {
    // Create a system user first
    User::factory()->create();

    $this->customer = Customer::factory()->create([
        'email' => 'customer@example.com',
        'portal_access_token' => 'valid-token-123',
    ]);
    $this->device = Device::factory()->create(['customer_id' => $this->customer->id]);
    $this->ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
    ]);
    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $this->ticket->id,
        'status' => InvoiceStatus::Pending,
        'subtotal' => 100.00,
        'tax_rate' => 10.00,
        'tax_amount' => 10.00,
        'discount' => 0,
        'total' => 110.00,
    ]);
});

test('handles successful payment verification', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->with('PS_TEST_123')
        ->andReturn([
            'status' => true,
            'data' => [
                'amount' => 11000, // 110.00 in pesewas
                'reference' => 'PS_TEST_123',
                'status' => 'success',
            ],
        ]);

    $response = get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    $response->assertRedirect(route('portal.tickets.show', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'ticket' => $this->ticket->id,
    ]));

    $response->assertSessionHas('success');
    $response->assertSessionHas('payment_id');
    $response->assertSessionHas('show_receipt', true);

    expect(Payment::where('invoice_id', $this->invoice->id)->count())->toBe(1);

    $payment = Payment::where('invoice_id', $this->invoice->id)->first();
    expect($payment)->not->toBeNull()
        ->and((string) $payment->amount)->toBe('110.00')
        ->and($payment->payment_method)->toBe(PaymentMethod::Card)
        ->and($payment->transaction_reference)->toBe('PS_TEST_123')
        ->and($payment->ticket_id)->toBe($this->ticket->id);
});

test('updates invoice status to paid when fully paid', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andReturn([
            'status' => true,
            'data' => [
                'amount' => 11000,
                'reference' => 'PS_TEST_123',
                'status' => 'success',
            ],
        ]);

    get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    expect($this->invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
});

test('does not update status for partial payments', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andReturn([
            'status' => true,
            'data' => [
                'amount' => 5000, // Only 50.00
                'reference' => 'PS_TEST_123',
                'status' => 'success',
            ],
        ]);

    get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    expect($this->invoice->fresh()->status)->toBe(InvoiceStatus::Pending);
});

test('handles failed verification', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andReturn([
            'status' => false,
            'message' => 'Transaction verification failed',
        ]);

    $response = get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    $response->assertRedirect(route('portal.invoices.index', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
    ]));

    $response->assertSessionHas('error');

    expect(Payment::where('invoice_id', $this->invoice->id)->count())->toBe(0);
});

test('validates customer portal token', function (): void {
    $response = get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'invalid-token',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    $response->assertRedirect(route('portal.login'));
});

test('validates invoice ownership', function (): void {
    $otherCustomer = Customer::factory()->create(['portal_access_token' => 'other-token']);

    $paystackMock = mock(PaystackService::class);

    $response = get(route('portal.invoices.payment.callback', [
        'customer' => $otherCustomer->id,
        'token' => 'other-token',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    $response->assertForbidden();
});

test('handles missing reference parameter', function (): void {
    $response = get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
    ]));

    $response->assertRedirect(route('portal.invoices.index', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
    ]));

    $response->assertSessionHas('error');
});

test('converts pesewas to cedis correctly', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andReturn([
            'status' => true,
            'data' => [
                'amount' => 15050, // 150.50 in pesewas
                'reference' => 'PS_TEST_123',
                'status' => 'success',
            ],
        ]);

    get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    $payment = Payment::where('invoice_id', $this->invoice->id)->first();
    expect($payment)->not->toBeNull()
        ->and((string) $payment->amount)->toBe('150.50');
});

test('includes customer portal note in payment', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andReturn([
            'status' => true,
            'data' => [
                'amount' => 11000,
                'reference' => 'PS_TEST_123',
                'status' => 'success',
            ],
        ]);

    get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    $payment = Payment::where('invoice_id', $this->invoice->id)->first();
    expect($payment)->not->toBeNull()
        ->and($payment->notes)->toContain('Online payment via Paystack - Customer Portal');
});

test('handles exception during payment processing', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andThrow(new \Exception('API Error'));

    $response = get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    $response->assertRedirect(route('portal.invoices.index', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
    ]));

    $response->assertSessionHas('error');

    expect(Payment::where('invoice_id', $this->invoice->id)->count())->toBe(0);
});

test('handles already paid invoice gracefully', function (): void {
    // Create existing payment
    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'ticket_id' => $this->ticket->id,
        'amount' => 110.00,
        'payment_method' => PaymentMethod::Cash,
    ]);

    $this->invoice->update(['status' => InvoiceStatus::Paid]);

    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andReturn([
            'status' => true,
            'data' => [
                'amount' => 11000,
                'reference' => 'PS_TEST_123',
                'status' => 'success',
            ],
        ]);

    $response = get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Should still create the payment record
    expect(Payment::where('transaction_reference', 'PS_TEST_123')->count())->toBe(1);
});

test('uses correct payment method enum', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andReturn([
            'status' => true,
            'data' => [
                'amount' => 11000,
                'reference' => 'PS_TEST_123',
                'status' => 'success',
            ],
        ]);

    get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_123',
    ]));

    $payment = Payment::where('invoice_id', $this->invoice->id)->first();
    expect($payment)->not->toBeNull()
        ->and($payment->payment_method)->toBeInstanceOf(PaymentMethod::class)
        ->and($payment->payment_method->value)->toBe('card');
});

test('sends receipt email after successful payment', function (): void {
    Mail::fake();

    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andReturn([
            'status' => true,
            'data' => [
                'amount' => 11000,
                'reference' => 'PS_TEST_EMAIL',
                'status' => 'success',
            ],
        ]);

    get(route('portal.invoices.payment.callback', [
        'customer' => $this->customer->id,
        'token' => 'valid-token-123',
        'invoice' => $this->invoice->id,
        'reference' => 'PS_TEST_EMAIL',
    ]));

    Mail::assertQueued(PaymentReceiptMail::class, function ($mail): bool {
        return $mail->hasTo($this->customer->email)
            && $mail->payment->transaction_reference === 'PS_TEST_EMAIL';
    });
});

test('does not send receipt email when customer has no email', function (): void {
    Mail::fake();

    $customerWithoutEmail = Customer::factory()->create([
        'email' => null,
        'portal_access_token' => 'token-456',
    ]);

    $invoiceForCustomerWithoutEmail = Invoice::factory()->create([
        'customer_id' => $customerWithoutEmail->id,
        'ticket_id' => null,
        'total' => 50.00,
    ]);

    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('verifyTransaction')
        ->once()
        ->andReturn([
            'status' => true,
            'data' => [
                'amount' => 5000,
                'reference' => 'PS_NO_EMAIL',
                'status' => 'success',
            ],
        ]);

    get(route('portal.invoices.payment.callback', [
        'customer' => $customerWithoutEmail->id,
        'token' => 'token-456',
        'invoice' => $invoiceForCustomerWithoutEmail->id,
        'reference' => 'PS_NO_EMAIL',
    ]));

    Mail::assertNothingSent();
});
