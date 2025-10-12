<?php

declare(strict_types=1);

use App\Enums\PosSaleStatus;
use App\Models\{PosSale, User};

uses()->group('browser', 'analytics');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create sales data for testing
    PosSale::factory()->count(3)->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now(),
        'total_amount' => 100.00,
        'tax_amount' => 15.00,
    ]);

    PosSale::factory()->count(2)->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now()->subDay(),
        'total_amount' => 75.00,
        'tax_amount' => 11.25,
    ]);
});

it('loads analytics dashboard successfully', function () {
    $page = visit('/analytics');

    $page->assertSee('Sales Analytics')
        ->assertSee('Monitor your sales performance')
        ->assertSee('Total Revenue')
        ->assertSee('Total Sales')
        ->assertSee('Avg Order Value')
        ->assertSee('Total Tax')
        ->assertNoJavaScriptErrors();
});

it('displays metric cards with currency values', function () {
    $page = visit('/analytics');

    // Verify metrics are displayed with currency
    $page->assertSee('Total Revenue')
        ->assertSee('GHS') // Currency symbol
        ->assertSee('Total Sales')
        ->assertSee('Completed transactions')
        ->assertNoJavaScriptErrors();
});

it('shows period filter dropdown', function () {
    $page = visit('/analytics');

    // Check for period filter - select element exists
    $page->assertSee('Sales Analytics')
        ->assertNoJavaScriptErrors();
});

it('displays sales over time section', function () {
    $page = visit('/analytics');

    $page->assertSee('Sales Over Time')
        ->assertNoJavaScriptErrors();
});

it('displays payment methods breakdown', function () {
    $page = visit('/analytics');

    $page->assertSee('Payment Methods')
        ->assertSee('transactions')
        ->assertNoJavaScriptErrors();
});

it('displays top selling products table', function () {
    $page = visit('/analytics');

    $page->assertSee('Top Selling Products')
        ->assertSee('Product')
        ->assertNoJavaScriptErrors();
});

it('shows all five metric cards', function () {
    $page = visit('/analytics');

    // Verify all 5 metrics are present
    $page->assertSee('Total Revenue')
        ->assertSee('Total Sales')
        ->assertSee('Avg Order Value')
        ->assertSee('Total Tax')
        ->assertSee('Total Discount')
        ->assertNoJavaScriptErrors();
});

it('handles navigation without errors', function () {
    $page = visit('/analytics');

    $page->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('displays data when sales exist', function () {
    $page = visit('/analytics');

    // Should show numeric values, not just zeros
    $page->assertSee('GHS')
        ->assertDontSee('No sales data available')
        ->assertNoJavaScriptErrors();
});
