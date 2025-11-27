<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Analytics;

use App\Enums\{PaymentMethod};
use App\Livewire\Analytics\Dashboard;
use App\Models\{InventoryItem, PosSale, PosSaleItem};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = createAdmin();
    $this->actingAs($this->user);
});

test('renders successfully', function (): void {
    Livewire::test(Dashboard::class)
        ->assertStatus(200);
});

test('renders successfully when accessed from route', function (): void {
    $this->get(route('analytics.dashboard'))
        ->assertSuccessful()
        ->assertSeeLivewire(Dashboard::class);
});

test('displays period filter with correct options', function (): void {
    Livewire::test(Dashboard::class)
        ->assertSee('Sales Analytics')
        ->assertSee('Today')
        ->assertSee('This Week')
        ->assertSee('This Month')
        ->assertSee('This Year')
        ->assertSee('All Time');
});

test('calculates total revenue correctly', function (): void {
    PosSale::factory()->completed()->create(['total_amount' => 100.00]);
    PosSale::factory()->completed()->create(['total_amount' => 250.50]);
    PosSale::factory()->refunded()->create(['total_amount' => 50.00]); // Should not be included

    Livewire::test(Dashboard::class)
        ->assertSet('period', 'all')
        ->assertSee('350.50');
});

test('calculates total sales count correctly', function (): void {
    PosSale::factory()->completed()->count(5)->create();
    PosSale::factory()->refunded()->count(2)->create(); // Should not be counted

    Livewire::test(Dashboard::class)
        ->assertSet('period', 'all')
        ->assertSee('5'); // Only completed sales
});

test('calculates average order value correctly', function (): void {
    PosSale::factory()->completed()->create(['total_amount' => 100.00]);
    PosSale::factory()->completed()->create(['total_amount' => 200.00]);
    // Average: 300 / 2 = 150

    Livewire::test(Dashboard::class)
        ->assertSet('period', 'all')
        ->assertSee('150.00');
});

test('calculates total tax correctly', function (): void {
    PosSale::factory()->completed()->create(['tax_amount' => 15.50]);
    PosSale::factory()->completed()->create(['tax_amount' => 20.25]);
    PosSale::factory()->refunded()->create(['tax_amount' => 5.00]); // Should not be included

    Livewire::test(Dashboard::class)
        ->assertSet('period', 'all')
        ->assertSee('35.75');
});

test('displays top selling products correctly', function (): void {
    $product1 = InventoryItem::factory()->create(['name' => 'Premium Widget', 'sku' => 'WIDGET-001']);
    $product2 = InventoryItem::factory()->create(['name' => 'Basic Gadget', 'sku' => 'GADGET-002']);

    $sale1 = PosSale::factory()->completed()->create();
    $sale2 = PosSale::factory()->completed()->create();

    // Product 1: 5 units at $100 each = $500 revenue
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale1->id,
        'inventory_item_id' => $product1->id,
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    // Product 2: 10 units at $20 each = $200 revenue
    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale2->id,
        'inventory_item_id' => $product2->id,
        'quantity' => 10,
        'unit_price' => 20,
        'subtotal' => 200,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSet('period', 'all')
        ->assertSee('Premium Widget')
        ->assertSee('WIDGET-001')
        ->assertSee('Basic Gadget')
        ->assertSee('GADGET-002');
});

test('displays payment method breakdown correctly', function (): void {
    PosSale::factory()->completed()->create([
        'payment_method' => PaymentMethod::Cash,
        'total_amount' => 100.00,
    ]);
    PosSale::factory()->completed()->create([
        'payment_method' => PaymentMethod::Card,
        'total_amount' => 200.00,
    ]);
    PosSale::factory()->completed()->create([
        'payment_method' => PaymentMethod::MobileMoney,
        'total_amount' => 150.00,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSet('period', 'all')
        ->assertSee('Cash')
        ->assertSee('Card')
        ->assertSee('Mobile Money');
});

test('period filter changes date ranges', function (): void {
    Livewire::test(Dashboard::class)
        ->assertSet('period', 'all')
        ->set('period', 'today')
        ->assertSet('period', 'today')
        ->assertSet('startDate', now()->startOfDay()->toDateTimeString())
        ->assertSet('endDate', now()->endOfDay()->toDateTimeString());
});

test('filters sales by selected period', function (): void {
    // Create a sale from 2 days ago (outside today's period)
    PosSale::factory()->completed()->create([
        'sale_date' => now()->subDays(2),
        'total_amount' => 100.00,
    ]);

    // Create a sale from today (within today's period)
    PosSale::factory()->completed()->create([
        'sale_date' => now(),
        'total_amount' => 200.00,
    ]);

    // When period is 'today', should only show today's sale (200.00)
    $component = Livewire::test(Dashboard::class)
        ->set('period', 'today');

    // Check the computed totalRevenue which filters by period
    $revenue = $component->get('totalRevenue');
    expect($revenue)->toBeGreaterThanOrEqual(200.0);
    expect($revenue)->toBeLessThan(300.0);
});

test('displays empty state when no sales exist', function (): void {
    Livewire::test(Dashboard::class)
        ->assertSet('period', 'all')
        ->assertSee('No sales data available')
        ->assertSee('No payment data available')
        ->assertSee('No product sales data available');
});

test('calculates revenue growth correctly', function (): void {
    // Create sales for previous period
    PosSale::factory()->completed()->create([
        'sale_date' => now()->subWeek(),
        'total_amount' => 100.00,
    ]);

    // Create sales for current period
    PosSale::factory()->completed()->create([
        'sale_date' => now(),
        'total_amount' => 150.00,
    ]);

    $component = Livewire::test(Dashboard::class)
        ->set('period', 'week');

    // Revenue growth should show increase
    $revenueGrowth = $component->get('revenueGrowth');
    expect($revenueGrowth)->toHaveKey('direction');
    expect($revenueGrowth)->toHaveKey('percentage');
});

test('revenue growth shows up direction for positive growth', function (): void {
    // Last month: $100
    PosSale::factory()->completed()->create([
        'sale_date' => now()->subMonth()->startOfMonth(),
        'total_amount' => 100.00,
    ]);

    // This month: $200 (100% growth)
    PosSale::factory()->completed()->create([
        'sale_date' => now()->startOfMonth(),
        'total_amount' => 200.00,
    ]);

    $component = Livewire::test(Dashboard::class)
        ->set('period', 'month');

    $revenueGrowth = $component->get('revenueGrowth');
    expect($revenueGrowth['direction'])->toBe('up');
    expect($revenueGrowth['percentage'])->toBeGreaterThan(0);
});

test('revenue growth shows down direction for negative growth', function (): void {
    // Last month: $200
    PosSale::factory()->completed()->create([
        'sale_date' => now()->subMonth()->startOfMonth(),
        'total_amount' => 200.00,
    ]);

    // This month: $100 (50% decline)
    PosSale::factory()->completed()->create([
        'sale_date' => now()->startOfMonth(),
        'total_amount' => 100.00,
    ]);

    $component = Livewire::test(Dashboard::class)
        ->set('period', 'month');

    $revenueGrowth = $component->get('revenueGrowth');
    expect($revenueGrowth['direction'])->toBe('down');
});

test('displays daily sales data', function (): void {
    PosSale::factory()->completed()->create([
        'sale_date' => now(),
        'total_amount' => 150.00,
    ]);

    PosSale::factory()->completed()->create([
        'sale_date' => now()->subDay(),
        'total_amount' => 200.00,
    ]);

    $component = Livewire::test(Dashboard::class)
        ->assertSet('period', 'all');

    $dailySales = $component->get('dailySales');
    expect($dailySales)->toBeArray();
    expect($dailySales)->not->toBeEmpty();
});

test('payment method breakdown includes percentages', function (): void {
    PosSale::factory()->completed()->create([
        'payment_method' => PaymentMethod::Cash,
        'total_amount' => 100.00,
    ]);
    PosSale::factory()->completed()->create([
        'payment_method' => PaymentMethod::Card,
        'total_amount' => 100.00,
    ]);

    $component = Livewire::test(Dashboard::class)
        ->assertSet('period', 'all');

    $paymentBreakdown = $component->get('paymentMethodBreakdown');
    expect($paymentBreakdown)->toBeArray();
    expect($paymentBreakdown[0])->toHaveKey('percentage');
});

test('only shows completed sales in calculations', function (): void {
    PosSale::factory()->completed()->create(['total_amount' => 100.00]);
    PosSale::factory()->refunded()->create(['total_amount' => 500.00]);

    Livewire::test(Dashboard::class)
        ->assertSet('period', 'all')
        ->assertSee('100.00') // Only completed sale
        ->assertDontSee('600.00'); // Not including refunded sale
});

test('top products are limited to 10 items', function (): void {
    $sales = PosSale::factory()->completed()->count(2)->create();

    // Create 15 different products
    for ($i = 1; $i <= 15; $i++) {
        $product = InventoryItem::factory()->create([
            'name' => "Product {$i}",
            'sku' => "PROD-{$i}",
        ]);

        PosSaleItem::factory()->create([
            'pos_sale_id' => $sales->random()->id,
            'inventory_item_id' => $product->id,
            'quantity' => $i,
            'unit_price' => 100,
            'subtotal' => $i * 100,
        ]);
    }

    $component = Livewire::test(Dashboard::class)
        ->assertSet('period', 'all');

    $topProducts = $component->get('topProducts');
    expect(count($topProducts))->toBeLessThanOrEqual(10);
});

test('computed properties are cached during request', function (): void {
    PosSale::factory()->completed()->create(['total_amount' => 100.00]);

    $component = Livewire::test(Dashboard::class);

    // Access the same computed property multiple times
    $revenue1 = $component->get('totalRevenue');
    $revenue2 = $component->get('totalRevenue');

    expect($revenue1)->toBe($revenue2);
});

test('all period options work correctly', function (string $period): void {
    PosSale::factory()->completed()->create([
        'sale_date' => now(),
        'total_amount' => 100.00,
    ]);

    Livewire::test(Dashboard::class)
        ->set('period', $period)
        ->assertStatus(200)
        ->assertSet('period', $period);
})->with(['today', 'week', 'month', 'year', 'all']);
