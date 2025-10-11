<?php

declare(strict_types=1);

use App\Livewire\Settings\Shop;
use App\Models\{ShopSettings, User};
use Livewire\Livewire;

test('only admin can access shop settings page', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->assertSuccessful();
});

test('non-admin users cannot access shop settings page', function () {
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    Livewire::actingAs($manager)
        ->test(Shop::class)
        ->assertForbidden();

    Livewire::actingAs($technician)
        ->test(Shop::class)
        ->assertForbidden();

    Livewire::actingAs($frontDesk)
        ->test(Shop::class)
        ->assertForbidden();
});

test('shop settings form is pre-filled with existing data', function () {
    $admin = User::factory()->admin()->create();

    $settings = ShopSettings::getInstance();
    $settings->update([
        'shop_name' => 'Test Shop',
        'address' => '123 Main St',
        'city' => 'Test City',
        'tax_rate' => 10.5,
    ]);

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->assertSet('shop_name', 'Test Shop')
        ->assertSet('address', '123 Main St')
        ->assertSet('city', 'Test City')
        ->assertSet('tax_rate', '10.50');
});

test('admin can update shop settings', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('shop_name', 'Updated Shop')
        ->set('address', '456 New St')
        ->set('city', 'New City')
        ->set('state', 'CA')
        ->set('zip', '12345')
        ->set('country', 'USA')
        ->set('phone', '555-1234')
        ->set('email', 'shop@test.com')
        ->set('website', 'https://test.com')
        ->set('tax_rate', '8.25')
        ->set('currency', 'USD')
        ->call('save')
        ->assertHasNoErrors();

    $settings = ShopSettings::getInstance();
    expect($settings->shop_name)->toBe('Updated Shop')
        ->and($settings->address)->toBe('456 New St')
        ->and($settings->city)->toBe('New City')
        ->and($settings->state)->toBe('CA')
        ->and($settings->zip)->toBe('12345')
        ->and($settings->phone)->toBe('555-1234')
        ->and($settings->email)->toBe('shop@test.com')
        ->and($settings->website)->toBe('https://test.com')
        ->and((float) $settings->tax_rate)->toBe(8.25)
        ->and($settings->currency)->toBe('USD');
});

test('shop name is required', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('shop_name', '')
        ->call('save')
        ->assertHasErrors(['shop_name' => 'required']);
});

test('email must be valid', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('email', 'invalid-email')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('website must be valid url', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('website', 'not-a-url')
        ->call('save')
        ->assertHasErrors(['website']);
});

test('tax rate is required', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('tax_rate', '')
        ->call('save')
        ->assertHasErrors(['tax_rate' => 'required']);
});

test('tax rate must be numeric', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('tax_rate', 'not-a-number')
        ->call('save')
        ->assertHasErrors(['tax_rate']);
});

test('tax rate must be between 0 and 100', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('tax_rate', '-1')
        ->call('save')
        ->assertHasErrors(['tax_rate']);

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('tax_rate', '101')
        ->call('save')
        ->assertHasErrors(['tax_rate']);
});

test('currency is required', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('currency', '')
        ->call('save')
        ->assertHasErrors(['currency' => 'required']);
});

test('currency must be 3 characters', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('currency', 'US')
        ->call('save')
        ->assertHasErrors(['currency']);

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('currency', 'USDD')
        ->call('save')
        ->assertHasErrors(['currency']);
});

test('country is required', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('country', '')
        ->call('save')
        ->assertHasErrors(['country' => 'required']);
});

test('optional fields can be null', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('shop_name', 'Test Shop')
        ->set('address', '')
        ->set('city', '')
        ->set('state', '')
        ->set('zip', '')
        ->set('phone', '')
        ->set('email', '')
        ->set('website', '')
        ->set('tax_rate', '0')
        ->set('country', 'USA')
        ->set('currency', 'USD')
        ->call('save')
        ->assertHasNoErrors();

    $settings = ShopSettings::getInstance();
    expect($settings->address)->toBeNull()
        ->and($settings->city)->toBeNull()
        ->and($settings->state)->toBeNull()
        ->and($settings->zip)->toBeNull()
        ->and($settings->phone)->toBeNull()
        ->and($settings->email)->toBeNull()
        ->and($settings->website)->toBeNull();
});

test('success message is dispatched after saving', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Shop::class)
        ->set('shop_name', 'Test Shop')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('settings-saved');
});

test('shop settings uses singleton pattern', function () {
    $settings1 = ShopSettings::getInstance();
    $settings2 = ShopSettings::getInstance();

    expect($settings1->id)->toBe($settings2->id)
        ->and(ShopSettings::count())->toBe(1);
});

test('full address accessor works correctly', function () {
    $settings = ShopSettings::getInstance();
    $settings->update([
        'address' => '123 Main St',
        'city' => 'Test City',
        'state' => 'CA',
        'zip' => '12345',
        'country' => 'USA',
    ]);

    expect($settings->full_address)->toBe('123 Main St, Test City, CA, 12345, USA');
});

test('full address accessor handles partial data', function () {
    $settings = ShopSettings::getInstance();
    $settings->update([
        'address' => '123 Main St',
        'city' => 'Test City',
        'state' => null,
        'zip' => null,
        'country' => 'USA',
    ]);

    expect($settings->full_address)->toBe('123 Main St, Test City, USA');
});
