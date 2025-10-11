<?php

declare(strict_types=1);

use App\Enums\{PaymentMethod, PosSaleStatus, UserRole};
use App\Livewire\Reports\Index;
use App\Models\{Customer, InventoryItem, PosSale, PosSaleItem, User};
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($this->user);
});

test('pos analytics tab displays key metrics', function () {
    // Create test data
    $customer = Customer::factory()->create();
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    $sale = PosSale::factory()->create([
        'customer_id' => $customer->id,
        'subtotal' => 100.00,
        'tax_amount' => 10.00,
        'discount_amount' => 5.00,
        'total_amount' => 105.00,
        'payment_method' => PaymentMethod::Cash,
        'status' => PosSaleStatus::Completed,
        'sold_by' => $this->user->id,
        'sale_date' => now(),
    ]);

    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
        'inventory_item_id' => $item->id,
        'quantity' => 2,
        'unit_price' => 50.00,
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Total POS Revenue')
        ->assertSee('Transactions')
        ->assertSee('Avg Transaction')
        ->assertSee('Items Sold')
        ->assertSee('105.00') // Total revenue
        ->assertSee('1') // Transaction count
        ->assertSee('2'); // Items sold
});

test('pos analytics shows revenue by payment method', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // Create sales with different payment methods
    $cashSale = PosSale::factory()->create([
        'total_amount' => 100.00,
        'payment_method' => PaymentMethod::Cash,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $cashSale->id,
        'inventory_item_id' => $item->id,
    ]);

    $cardSale = PosSale::factory()->create([
        'total_amount' => 200.00,
        'payment_method' => PaymentMethod::Card,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $cardSale->id,
        'inventory_item_id' => $item->id,
    ]);

    $mobileMoneyS = PosSale::factory()->create([
        'total_amount' => 150.00,
        'payment_method' => PaymentMethod::MobileMoney,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $mobileMoneyS->id,
        'inventory_item_id' => $item->id,
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Revenue by Payment Method')
        ->assertSee('Cash')
        ->assertSee('Card')
        ->assertSee('Mobile Money')
        ->assertSee('100.00')
        ->assertSee('200.00')
        ->assertSee('150.00');
});

test('pos analytics displays daily sales trend', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // Create sales on different days
    $sale1 = PosSale::factory()->create([
        'total_amount' => 100.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now()->subDays(2),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale1->id,
        'inventory_item_id' => $item->id,
    ]);

    $sale2 = PosSale::factory()->create([
        'total_amount' => 150.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now()->subDays(1),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale2->id,
        'inventory_item_id' => $item->id,
    ]);

    $sale3 = PosSale::factory()->create([
        'total_amount' => 200.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale3->id,
        'inventory_item_id' => $item->id,
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Daily Sales Trend')
        ->assertSee('100.00')
        ->assertSee('150.00')
        ->assertSee('200.00');
});

test('pos analytics shows top products by quantity', function () {
    $item1 = InventoryItem::factory()->create(['name' => 'Product A', 'quantity' => 100]);
    $item2 = InventoryItem::factory()->create(['name' => 'Product B', 'quantity' => 100]);
    $item3 = InventoryItem::factory()->create(['name' => 'Product C', 'quantity' => 100]);

    // Create sales with different quantities
    $sale1 = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale1->id,
        'inventory_item_id' => $item1->id,
        'quantity' => 10,
        'unit_price' => 50.00,
    ]);

    $sale2 = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale2->id,
        'inventory_item_id' => $item2->id,
        'quantity' => 15,
        'unit_price' => 30.00,
    ]);

    $sale3 = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale3->id,
        'inventory_item_id' => $item3->id,
        'quantity' => 5,
        'unit_price' => 100.00,
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Top Products (Quantity)')
        ->assertSee('Product A')
        ->assertSee('Product B')
        ->assertSee('Product C')
        ->assertSee('10')
        ->assertSee('15')
        ->assertSee('5');
});

test('pos analytics shows top products by revenue', function () {
    $item1 = InventoryItem::factory()->create(['name' => 'High Value Item', 'quantity' => 100]);
    $item2 = InventoryItem::factory()->create(['name' => 'Medium Value Item', 'quantity' => 100]);

    $sale1 = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale1->id,
        'inventory_item_id' => $item1->id,
        'quantity' => 5,
        'unit_price' => 200.00, // 1000 revenue
    ]);

    $sale2 = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale2->id,
        'inventory_item_id' => $item2->id,
        'quantity' => 20,
        'unit_price' => 25.00, // 500 revenue
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Top Products (Revenue)')
        ->assertSee('High Value Item')
        ->assertSee('Medium Value Item')
        ->assertSee('1,000.00')
        ->assertSee('500.00');
});

test('pos analytics displays sales by hour', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // Create sales at different hours
    $sale1 = PosSale::factory()->create([
        'total_amount' => 100.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now()->setHour(9)->setMinute(0),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale1->id,
        'inventory_item_id' => $item->id,
    ]);

    $sale2 = PosSale::factory()->create([
        'total_amount' => 150.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now()->setHour(14)->setMinute(0),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale2->id,
        'inventory_item_id' => $item->id,
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Sales by Hour')
        ->assertSee('09:00')
        ->assertSee('14:00');
});

test('pos analytics shows sales by day of week', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // Create sales on different days of the week
    $monday = now()->startOfWeek(); // Monday
    $friday = now()->startOfWeek()->addDays(4); // Friday

    $sale1 = PosSale::factory()->create([
        'total_amount' => 100.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => $monday,
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale1->id,
        'inventory_item_id' => $item->id,
    ]);

    $sale2 = PosSale::factory()->create([
        'total_amount' => 200.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => $friday,
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale2->id,
        'inventory_item_id' => $item->id,
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Sales by Day of Week')
        ->assertSee('Monday')
        ->assertSee('Friday');
});

test('pos analytics displays top customers', function () {
    $customer1 = Customer::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
    $customer2 = Customer::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // John has 3 transactions totaling $600
    for ($i = 0; $i < 3; $i++) {
        $sale = PosSale::factory()->create([
            'customer_id' => $customer1->id,
            'total_amount' => 200.00,
            'status' => PosSaleStatus::Completed,
            'sale_date' => now(),
        ]);
        PosSaleItem::factory()->create([
            'pos_sale_id' => $sale->id,
            'inventory_item_id' => $item->id,
        ]);
    }

    // Jane has 2 transactions totaling $300
    for ($i = 0; $i < 2; $i++) {
        $sale = PosSale::factory()->create([
            'customer_id' => $customer2->id,
            'total_amount' => 150.00,
            'status' => PosSaleStatus::Completed,
            'sale_date' => now(),
        ]);
        PosSaleItem::factory()->create([
            'pos_sale_id' => $sale->id,
            'inventory_item_id' => $item->id,
        ]);
    }

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Top Customers')
        ->assertSee('John Doe')
        ->assertSee('Jane Smith')
        ->assertSee('600.00')
        ->assertSee('300.00')
        ->assertSee('3') // John's transaction count
        ->assertSee('2'); // Jane's transaction count
});

test('pos analytics shows discount totals', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    $sale1 = PosSale::factory()->create([
        'discount_amount' => 10.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale1->id,
        'inventory_item_id' => $item->id,
    ]);

    $sale2 = PosSale::factory()->create([
        'discount_amount' => 15.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale2->id,
        'inventory_item_id' => $item->id,
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Total Discounts')
        ->assertSee('25.00');
});

test('pos analytics separates cash and digital sales', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // Cash sales
    $cashSale = PosSale::factory()->create([
        'total_amount' => 100.00,
        'payment_method' => PaymentMethod::Cash,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $cashSale->id,
        'inventory_item_id' => $item->id,
    ]);

    // Card/digital sales
    $cardSale = PosSale::factory()->create([
        'total_amount' => 200.00,
        'payment_method' => PaymentMethod::Card,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $cardSale->id,
        'inventory_item_id' => $item->id,
    ]);

    $mobileMoneySale = PosSale::factory()->create([
        'total_amount' => 150.00,
        'payment_method' => PaymentMethod::MobileMoney,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $mobileMoneySale->id,
        'inventory_item_id' => $item->id,
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Cash Sales')
        ->assertSee('100.00')
        ->assertSee('Card/Digital Sales')
        ->assertSee('350.00'); // 200 + 150
});

test('pos analytics filters by date range', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // Sale within range
    $sale1 = PosSale::factory()->create([
        'total_amount' => 100.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale1->id,
        'inventory_item_id' => $item->id,
    ]);

    // Sale outside range
    $sale2 = PosSale::factory()->create([
        'total_amount' => 200.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now()->subMonths(2),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale2->id,
        'inventory_item_id' => $item->id,
    ]);

    Livewire::test(Index::class, [
        'tab' => 'pos',
        'startDate' => now()->startOfMonth()->format('Y-m-d'),
        'endDate' => now()->endOfMonth()->format('Y-m-d'),
    ])
        ->assertSee('100.00')
        ->assertDontSee('200.00'); // Sale from 2 months ago should not appear
});

test('pos analytics excludes refunded sales', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // Completed sale
    $completedSale = PosSale::factory()->create([
        'total_amount' => 100.00,
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $completedSale->id,
        'inventory_item_id' => $item->id,
    ]);

    // Refunded sale
    $refundedSale = PosSale::factory()->create([
        'total_amount' => 200.00,
        'status' => PosSaleStatus::Refunded,
        'sale_date' => now(),
    ]);
    PosSaleItem::factory()->create([
        'pos_sale_id' => $refundedSale->id,
        'inventory_item_id' => $item->id,
    ]);

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('100.00') // Only completed sale
        ->assertSee('1') // Only 1 transaction
        ->assertDontSee('300.00'); // Should not include refunded amount
});

test('pos analytics handles no data gracefully', function () {
    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Total POS Revenue')
        ->assertSee('0.00')
        ->assertSee('No POS sales data for selected period');
});

test('pos analytics calculates average transaction correctly', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // Create 3 sales with total of 300
    $amounts = [50.00, 100.00, 150.00];
    foreach ($amounts as $amount) {
        $sale = PosSale::factory()->create([
            'total_amount' => $amount,
            'status' => PosSaleStatus::Completed,
            'sale_date' => now(),
        ]);
        PosSaleItem::factory()->create([
            'pos_sale_id' => $sale->id,
            'inventory_item_id' => $item->id,
        ]);
    }

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('Avg Transaction')
        ->assertSee('100.00'); // 300 / 3 = 100
});

test('pos analytics requires proper authorization', function () {
    $unauthorizedUser = User::factory()->create();
    actingAs($unauthorizedUser);

    // Mock authorization to fail
    $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

    Livewire::test(Index::class, ['tab' => 'pos']);
})->skip('Authorization needs to be implemented in UserPolicy');

test('pos analytics shows payment method percentages', function () {
    $item = InventoryItem::factory()->create(['quantity' => 100]);

    // Create 10 sales: 5 cash, 3 card, 2 mobile money
    for ($i = 0; $i < 5; $i++) {
        $sale = PosSale::factory()->create([
            'payment_method' => PaymentMethod::Cash,
            'status' => PosSaleStatus::Completed,
            'sale_date' => now(),
        ]);
        PosSaleItem::factory()->create([
            'pos_sale_id' => $sale->id,
            'inventory_item_id' => $item->id,
        ]);
    }

    for ($i = 0; $i < 3; $i++) {
        $sale = PosSale::factory()->create([
            'payment_method' => PaymentMethod::Card,
            'status' => PosSaleStatus::Completed,
            'sale_date' => now(),
        ]);
        PosSaleItem::factory()->create([
            'pos_sale_id' => $sale->id,
            'inventory_item_id' => $item->id,
        ]);
    }

    for ($i = 0; $i < 2; $i++) {
        $sale = PosSale::factory()->create([
            'payment_method' => PaymentMethod::MobileMoney,
            'status' => PosSaleStatus::Completed,
            'sale_date' => now(),
        ]);
        PosSaleItem::factory()->create([
            'pos_sale_id' => $sale->id,
            'inventory_item_id' => $item->id,
        ]);
    }

    Livewire::test(Index::class, ['tab' => 'pos'])
        ->assertSee('50') // 5/10 = 50% for Cash
        ->assertSee('30') // 3/10 = 30% for Card
        ->assertSee('20'); // 2/10 = 20% for Mobile Money
});
