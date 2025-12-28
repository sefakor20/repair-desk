<?php

declare(strict_types=1);

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{Customer, Invoice, Payment, Ticket, User};
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('dashboard shows trend indicators for urgent tickets', function (): void {
    $customer = Customer::factory()->create();

    // Create 2 urgent tickets today
    Ticket::factory()->count(2)->create([
        'priority' => TicketPriority::Urgent,
        'status' => TicketStatus::New,
        'customer_id' => $customer->id,
        'created_at' => now(),
    ]);

    // Create 1 urgent ticket yesterday
    Ticket::factory()->create([
        'priority' => TicketPriority::Urgent,
        'status' => TicketStatus::InProgress,
        'customer_id' => $customer->id,
        'created_at' => now()->subDay(),
    ]);

    Volt::test('dashboard')
        ->assertSee('Urgent Tickets')
        ->assertSee('2')
        ->assertSee('100%'); // 100% increase
});

test('dashboard shows trend indicators for revenue', function (): void {
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
    ]);

    // Create payment today for $1000
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 1000.00,
        'payment_date' => now(),
    ]);

    // Create payment yesterday for $800
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 800.00,
        'payment_date' => now()->subDay(),
    ]);

    Volt::test('dashboard')
        ->assertSee('Today\'s Revenue')
        ->assertSee('1,000.00')
        ->assertSee('25%'); // 25% increase
});

test('dashboard shows downward trend correctly', function (): void {
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
    ]);

    // Create payment today for $500
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 500.00,
        'payment_date' => now(),
    ]);

    // Create payment yesterday for $1000
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 1000.00,
        'payment_date' => now()->subDay(),
    ]);

    Volt::test('dashboard')
        ->assertSee('Today\'s Revenue')
        ->assertSee('500.00')
        ->assertSee('50%'); // 50% decrease
});

test('dashboard shows neutral trend when no change', function (): void {
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
    ]);

    // Create payment today for $1000
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 1000.00,
        'payment_date' => now(),
    ]);

    // Create payment yesterday for $1000
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 1000.00,
        'payment_date' => now()->subDay(),
    ]);

    Volt::test('dashboard')
        ->assertSee('Today\'s Revenue')
        ->assertSee('1,000.00')
        ->assertSee('No change');
});

test('dashboard handles zero previous value correctly', function (): void {
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
    ]);

    // Create payment today for $1000 with no payment yesterday
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 1000.00,
        'payment_date' => now(),
    ]);

    Volt::test('dashboard')
        ->assertSee('Today\'s Revenue')
        ->assertSee('1,000.00')
        ->assertSee('100%'); // 100% increase from zero
});

test('dashboard shows pending invoices trend', function (): void {
    $customer = Customer::factory()->create();

    // Create 3 pending invoices today
    Invoice::factory()->count(3)->create([
        'customer_id' => $customer->id,
        'status' => 'pending',
        'created_at' => now(),
    ]);

    // Create 2 pending invoices yesterday
    Invoice::factory()->count(2)->create([
        'customer_id' => $customer->id,
        'status' => 'pending',
        'created_at' => now()->subDay(),
    ]);

    Volt::test('dashboard')
        ->assertSee('Pending Invoices')
        ->assertSee('5') // Total pending invoices
        ->assertSee('new today');
});
