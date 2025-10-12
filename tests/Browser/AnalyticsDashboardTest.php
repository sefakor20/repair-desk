<?php

declare(strict_types=1);

use App\Enums\PosSaleStatus;
use App\Models\{PosSale, User};

uses()->group('browser');

beforeEach(function () {
    $this->user = User::factory()->create();

    // Create sales data for testing
    PosSale::factory()->count(3)->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
        'total_amount' => 100.00,
    ]);

    PosSale::factory()->count(2)->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now()->subDay(),
        'total_amount' => 75.00,
    ]);
});

it('loads analytics dashboard successfully', function () {
    $page = visit('/analytics')->actingAs($this->user);

    $page->assertSee('Sales Analytics')
        ->assertSee('Monitor your sales performance')
        ->assertSee('Total Revenue')
        ->assertSee('Total Sales')
        ->assertSee('Avg Order Value')
        ->assertSee('Total Tax')
        ->assertNoJavaScriptErrors();
});

it('displays correct metrics on analytics dashboard', function () {
    $page = visit('/analytics')->actingAs($this->user);

    // Verify metrics are displayed
    $page->assertSee('Total Revenue')
        ->assertSee('GHS') // Currency symbol
        ->assertSee('Total Sales')
        ->assertSee('5') // 3 today + 2 yesterday
        ->assertSee('Completed transactions')
        ->assertNoJavaScriptErrors();
});

it('filters analytics by period', function () {
    $page = visit('/analytics')->actingAs($this->user);

    // Default to "All Time"
    $page->assertSee('All Time');

    // Filter by today
    $page->select('@period-filter', 'today')
        ->waitFor('.metric-card')
        ->assertSee('3') // Only today's sales
        ->assertNoJavaScriptErrors();

    // Filter by this week
    $page->select('@period-filter', 'week')
        ->waitFor('.metric-card')
        ->assertSee('5') // All sales this week
        ->assertNoJavaScriptErrors();
});

it('displays sales over time chart', function () {
    $page = visit('/analytics')->actingAs($this->user);

    $page->assertSee('Sales Over Time')
        ->assertVisible('.sales-chart')
        ->assertNoJavaScriptErrors();

    // Verify chart has data points
    $chartItems = $page->elements('.chart-item');
    expect(count($chartItems))->toBeGreaterThan(0);
});

it('displays payment method breakdown', function () {
    $page = visit('/analytics')->actingAs($this->user);

    $page->assertSee('Payment Methods')
        ->assertSee('transactions')
        ->assertVisible('.payment-breakdown')
        ->assertNoJavaScriptErrors();
});

it('displays top selling products', function () {
    $page = visit('/analytics')->actingAs($this->user);

    $page->assertSee('Top Selling Products')
        ->assertSee('Quantity Sold')
        ->assertSee('Revenue')
        ->assertVisible('.products-table')
        ->assertNoJavaScriptErrors();
});

it('shows revenue growth indicator', function () {
    $page = visit('/analytics')->actingAs($this->user);

    // Filter to show growth indicator
    $page->select('@period-filter', 'today')
        ->waitFor('.growth-indicator')
        ->assertVisible('.growth-indicator')
        ->assertNoJavaScriptErrors();

    // Should show either up or down arrow
    $hasUpArrow = $page->hasElement('.growth-up');
    $hasDownArrow = $page->hasElement('.growth-down');

    expect($hasUpArrow || $hasDownArrow)->toBeTrue();
});

it('updates metrics when period changes', function () {
    $page = visit('/analytics')->actingAs($this->user);

    // Get initial total sales
    $page->select('@period-filter', 'all');
    $allTimeSales = $page->text('@total-sales-value');

    // Switch to today
    $page->select('@period-filter', 'today')
        ->waitFor('@total-sales-value');

    $todaySales = $page->text('@total-sales-value');

    // Values should be different
    expect($allTimeSales)->not->toBe($todaySales);
});

it('handles empty data gracefully', function () {
    // Clear all sales
    PosSale::query()->delete();

    $page = visit('/analytics')->actingAs($this->user);

    $page->assertSee('Sales Analytics')
        ->assertSee('No sales data available')
        ->assertNoJavaScriptErrors();
});

it('displays discount metrics', function () {
    // Create sale with discount
    PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'total_amount' => 100.00,
        'discount_amount' => 10.00,
    ]);

    $page = visit('/analytics')->actingAs($this->user);

    $page->assertSee('Total Discount')
        ->assertSee('Discounts applied')
        ->assertNoJavaScriptErrors();
});

it('navigates between different analytics views smoothly', function () {
    $page = visit('/analytics')->actingAs($this->user);

    // Test smooth navigation
    $page->select('@period-filter', 'today')
        ->waitFor('.metric-card')
        ->select('@period-filter', 'week')
        ->waitFor('.metric-card')
        ->select('@period-filter', 'month')
        ->waitFor('.metric-card')
        ->assertNoJavaScriptErrors();
});
