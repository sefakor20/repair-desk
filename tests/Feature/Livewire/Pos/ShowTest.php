<?php

declare(strict_types=1);

use App\Enums\{PosSaleStatus, UserRole};
use App\Livewire\Pos\Show;
use App\Models\{InventoryItem, PosSale, PosSaleItem, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

test('pos show page can be rendered', function () {
    $user = createAdmin();
    actingAs($user);

    $sale = PosSale::factory()->create([
        'sold_by' => $user->id,
    ]);

    get(route('pos.show', $sale))
        ->assertSuccessful();
});

test('unauthorized user cannot access pos show page', function () {
    $sale = PosSale::factory()->create();

    get(route('pos.show', $sale))
        ->assertRedirect(route('login'));
});

test('displays sale details correctly', function () {
    $user = createAdmin();
    actingAs($user);

    $sale = PosSale::factory()->create([
        'sale_number' => 'POS-12345',
        'subtotal' => 100.00,
        'tax_amount' => 8.50,
        'discount_amount' => 5.00,
        'total_amount' => 103.50,
        'sold_by' => $user->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->assertSee('POS-12345')
        ->assertSee('100.00')
        ->assertSee('8.50')
        ->assertSee('5.00')
        ->assertSee('103.50');
});

test('displays sale items', function () {
    $user = createAdmin();
    actingAs($user);

    $sale = PosSale::factory()->create([
        'sold_by' => $user->id,
    ]);

    $item = InventoryItem::factory()->create([
        'name' => 'Test Product',
        'sku' => 'TEST-001',
    ]);

    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
        'inventory_item_id' => $item->id,
        'quantity' => 2,
        'unit_price' => 50.00,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->assertSee('Test Product')
        ->assertSee('TEST-001')
        ->assertSee('2')
        ->assertSee('50.00');
});

test('displays walk-in customer for sales without customer', function () {
    $user = createAdmin();
    actingAs($user);

    $sale = PosSale::factory()->create([
        'customer_id' => null,
        'sold_by' => $user->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->assertSee('Walk-in Customer');
});

test('displays sale status badge', function () {
    $user = createAdmin();
    actingAs($user);

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sold_by' => $user->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->assertSee('Completed');
});

test('only admin and manager can see refund button', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sold_by' => $admin->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->assertSee('Refund Sale');
});

test('technician cannot see refund button', function () {
    $technician = User::factory()->create(['role' => UserRole::Technician]);
    actingAs($technician);

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sold_by' => $technician->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->assertSee('Back to Sales');
});

test('can open refund modal', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sold_by' => $admin->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->call('openRefundModal')
        ->assertSet('showRefundModal', true);
});

test('cannot open refund modal for already refunded sale', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Refunded,
        'sold_by' => $admin->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->call('openRefundModal')
        ->assertHasErrors('refund');
});

test('can close refund modal', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sold_by' => $admin->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->set('showRefundModal', true)
        ->call('closeRefundModal')
        ->assertSet('showRefundModal', false)
        ->assertSet('refundReason', '');
});

test('refund reason is required', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sold_by' => $admin->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->set('refundReason', '')
        ->call('processRefund')
        ->assertHasErrors(['refundReason' => 'required']);
});

test('can process refund', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $item = InventoryItem::factory()->create([
        'quantity' => 5,
    ]);

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sold_by' => $admin->id,
    ]);

    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
        'inventory_item_id' => $item->id,
        'quantity' => 2,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->set('refundReason', 'Customer not satisfied')
        ->call('processRefund')
        ->assertHasNoErrors()
        ->assertSet('showRefundModal', false);

    $sale->refresh();
    expect($sale->status)->toBe(PosSaleStatus::Refunded);
    expect($sale->notes)->toContain('REFUND');
    expect($sale->notes)->toContain('Customer not satisfied');
});

test('inventory is restored after refund', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $item = InventoryItem::factory()->create([
        'quantity' => 5,
    ]);

    $initialQuantity = $item->quantity;

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sold_by' => $admin->id,
    ]);

    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
        'inventory_item_id' => $item->id,
        'quantity' => 3,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->set('refundReason', 'Product defective')
        ->call('processRefund');

    $item->refresh();
    expect($item->quantity)->toBe($initialQuantity + 3);
});

test('cannot process refund for already refunded sale', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $sale = PosSale::factory()->create([
        'status' => PosSaleStatus::Refunded,
        'sold_by' => $admin->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->set('refundReason', 'Already refunded')
        ->call('processRefund')
        ->assertHasErrors('refund');
});

test('displays sale notes when present', function () {
    $user = createAdmin();
    actingAs($user);

    $sale = PosSale::factory()->create([
        'notes' => 'Special discount for loyal customer',
        'sold_by' => $user->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->assertSee('Special discount for loyal customer');
});

test('displays payment method', function () {
    $user = createAdmin();
    actingAs($user);

    $sale = PosSale::factory()->create([
        'payment_method' => 'card',
        'sold_by' => $user->id,
    ]);

    Livewire::test(Show::class, ['sale' => $sale])
        ->assertSee('Card');
});
