<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Livewire\Portal\Invoices\PayInvoice;
use App\Models\{Customer, Device, Invoice, Payment, Ticket};
use App\Services\PaystackService;
use Livewire\Volt\Volt;

use function Pest\Laravel\mock;

beforeEach(function (): void {
    $this->customer = Customer::factory()->create(['email' => 'customer@example.com']);
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

test('renders successfully for authorized customer', function (): void {
    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->assertOk()
        ->assertSee($this->invoice->invoice_number);
});

test('prevents unauthorized access to invoice', function (): void {
    $otherCustomer = Customer::factory()->create();

    Volt::test(PayInvoice::class, ['customer' => $otherCustomer, 'invoice' => $this->invoice])
        ->assertForbidden();
});

test('redirects if invoice is already paid', function (): void {
    $this->invoice->update(['status' => InvoiceStatus::Paid]);

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->assertRedirect(route('portal.invoices.index', [
            'customer' => $this->customer->id,
            'token' => $this->customer->portal_access_token,
        ]));
});

test('displays invoice summary correctly', function (): void {
    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->assertSee($this->invoice->invoice_number)
        ->assertSee('GH₵ 100.00') // Subtotal
        ->assertSee('GH₵ 10.00') // Tax
        ->assertSee('GH₵ 110.00'); // Total
});

test('displays balance due correctly', function (): void {
    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'ticket_id' => $this->ticket->id,
        'amount' => 50.00,
        'payment_method' => PaymentMethod::Cash,
    ]);

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->assertSee('GH₵ 50.00') // Already Paid
        ->assertSee('GH₵ 60.00'); // Balance Due
});

test('pre-fills customer email', function (): void {
    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->assertSet('email', $this->customer->email);
});

test('validates email is required', function (): void {
    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->set('email', '')
        ->call('initializePayment')
        ->assertHasErrors(['email' => 'required']);
});

test('validates email format', function (): void {
    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->set('email', 'invalid-email')
        ->call('initializePayment')
        ->assertHasErrors(['email' => 'email']);
});

test('initializes payment successfully', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('generateReference')
        ->once()
        ->andReturn('PS_TEST_123');
    $paystackMock->shouldReceive('initializeTransaction')
        ->once()
        ->andReturn([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://paystack.com/pay/xyz',
                'reference' => 'PS_TEST_123',
            ],
        ]);

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->set('email', 'customer@example.com')
        ->call('initializePayment')
        ->assertSet('paymentInitialized', true)
        ->assertSet('paymentReference', 'PS_TEST_123')
        ->assertDispatched('redirect-to-paystack');
});

test('handles payment initialization failure', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('generateReference')
        ->once()
        ->andReturn('PS_TEST_123');
    $paystackMock->shouldReceive('initializeTransaction')
        ->once()
        ->andReturn([
            'status' => false,
            'message' => 'Payment initialization failed',
        ]);

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->set('email', 'customer@example.com')
        ->call('initializePayment')
        ->assertSet('paymentInitialized', false)
        ->assertSet('errorMessage', 'Payment initialization failed');
});

test('displays payment history when payments exist', function (): void {
    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'ticket_id' => $this->ticket->id,
        'amount' => 30.00,
        'payment_method' => PaymentMethod::Cash,
        'transaction_reference' => 'CASH_001',
    ]);

    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'ticket_id' => $this->ticket->id,
        'amount' => 20.00,
        'payment_method' => PaymentMethod::Card,
        'transaction_reference' => 'CARD_002',
    ]);

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->assertSee('GH₵ 30.00')
        ->assertSee('GH₵ 20.00')
        ->assertSee('CASH_001')
        ->assertSee('CARD_002');
});

test('converts amount to pesewas for paystack', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('generateReference')
        ->once()
        ->andReturn('PS_TEST_123');
    $paystackMock->shouldReceive('initializeTransaction')
        ->once()
        ->andReturnUsing(function ($data) {
            expect($data['amount'])->toBe(11000); // 110.00 * 100

            return [
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://paystack.com/pay/xyz',
                    'reference' => 'PS_TEST_123',
                ],
            ];
        });

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->set('email', 'customer@example.com')
        ->call('initializePayment')
        ->assertOk();
});

test('includes invoice metadata in payment initialization', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('generateReference')
        ->once()
        ->andReturn('PS_TEST_123');
    $paystackMock->shouldReceive('initializeTransaction')
        ->once()
        ->withArgs(function ($data): bool {
            return isset($data['metadata'])
                && $data['metadata']['invoice_id'] === $this->invoice->id
                && $data['metadata']['invoice_number'] === $this->invoice->invoice_number
                && $data['metadata']['customer_id'] === $this->customer->id;
        })
        ->andReturn([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://paystack.com/pay/xyz',
                'reference' => 'PS_TEST_123',
            ],
        ]);

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->set('email', 'customer@example.com')
        ->call('initializePayment')
        ->assertOk();
});

test('generates portal access token if missing', function (): void {
    $this->customer->update(['portal_access_token' => null]);

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->assertOk();

    expect($this->customer->fresh()->portal_access_token)->not->toBeNull();
});

test('displays device information', function (): void {
    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->assertSee($this->device->brand)
        ->assertSee($this->device->model);
});

test('shows correct callback url in metadata', function (): void {
    $paystackMock = mock(PaystackService::class);
    $paystackMock->shouldReceive('generateReference')
        ->once()
        ->andReturn('PS_TEST_123');
    $paystackMock->shouldReceive('initializeTransaction')
        ->once()
        ->withArgs(function ($data): bool {
            return str_contains($data['callback_url'], 'payment/callback');
        })
        ->andReturn([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://paystack.com/pay/xyz',
                'reference' => 'PS_TEST_123',
            ],
        ]);

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->set('email', 'customer@example.com')
        ->call('initializePayment')
        ->assertOk();
});

test('displays discount when applied', function (): void {
    $this->invoice->update([
        'discount' => 10.00,
        'total' => 100.00, // 110.00 - 10.00
    ]);

    Volt::test(PayInvoice::class, ['customer' => $this->customer, 'invoice' => $this->invoice])
        ->assertSee('GH₵ 10.00'); // Discount
});
