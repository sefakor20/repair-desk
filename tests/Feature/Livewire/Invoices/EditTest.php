<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Edit;
use App\Models\{Invoice, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function (): void {
    $this->user = createAdmin(); // Default role is FrontDesk
    actingAs($this->user);
});

test('edit invoice page can be rendered', function (): void {
    $invoice = Invoice::factory()->create();

    get(route('invoices.edit', $invoice))
        ->assertOk()
        ->assertSeeLivewire(Edit::class);
});

test('unauthorized user cannot access edit invoice page', function (): void {
    $technician = User::factory()->technician()->create();
    actingAs($technician);

    $invoice = Invoice::factory()->create();

    get(route('invoices.edit', $invoice))
        ->assertForbidden();
});

test('can update invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'subtotal' => '100.00',
        'tax_rate' => '10.00',
        'discount' => '5.00',
    ]);

    Livewire::test(Edit::class, ['invoice' => $invoice])
        ->set('subtotal', '150.00')
        ->set('taxRate', '15')
        ->set('discount', '10.00')
        ->set('status', InvoiceStatus::Paid)
        ->set('notes', 'Updated notes')
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('invoices.show', $invoice));

    $invoice->refresh();

    expect($invoice->subtotal)->toBe('150.00')
        ->and($invoice->tax_rate)->toBe('15.00')
        ->and($invoice->discount)->toBe('10.00')
        ->and($invoice->total)->toBe('161.00') // (150 - 10) * 1.15
        ->and($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->notes)->toBe('Updated notes');
});

test('subtotal field is required on update', function (): void {
    $invoice = Invoice::factory()->create();

    Livewire::test(Edit::class, ['invoice' => $invoice])
        ->set('subtotal', '')
        ->call('update')
        ->assertHasErrors(['subtotal' => 'required']);
});

test('subtotal must be numeric on update', function (): void {
    $invoice = Invoice::factory()->create();

    Livewire::test(Edit::class, ['invoice' => $invoice])
        ->set('subtotal', 'invalid')
        ->call('update')
        ->assertHasErrors(['subtotal' => 'numeric']);
});

test('status field is required on update', function (): void {
    $invoice = Invoice::factory()->create();

    Livewire::test(Edit::class, ['invoice' => $invoice])
        ->set('status', '')
        ->call('update')
        ->assertHasErrors(['status' => 'required']);
});

test('recalculates totals on update', function (): void {
    $invoice = Invoice::factory()->create();

    Livewire::test(Edit::class, ['invoice' => $invoice])
        ->set('subtotal', '500.00')
        ->set('taxRate', '20')
        ->set('discount', '50.00')
        ->call('update')
        ->assertHasNoErrors();

    $invoice->refresh();

    // (500 - 50) * 1.20 = 450 * 1.20 = 540
    expect($invoice->total)->toBe('540.00')
        ->and($invoice->tax_amount)->toBe('90.00');
});
