<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\{InventoryItem, User};
use App\Notifications\LowStockAlert;
use Illuminate\Support\Collection;

test('low stock alert notification contains correct item count', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $items = InventoryItem::factory()->count(3)->create();

    $notification = new LowStockAlert($items);
    $mailData = $notification->toMail($admin);

    expect($mailData->subject)->toContain('3 Items');
});

test('low stock alert notification lists all items', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $item1 = InventoryItem::factory()->create(['name' => 'Test Item 1', 'sku' => 'SKU001']);
    $item2 = InventoryItem::factory()->create(['name' => 'Test Item 2', 'sku' => 'SKU002']);
    $items = new Collection([$item1, $item2]);

    $notification = new LowStockAlert($items);
    $arrayData = $notification->toArray($admin);

    expect($arrayData['items_count'])->toBe(2)
        ->and($arrayData['items'])->toHaveCount(2);
});

test('low stock alert notification generates sms message', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'phone' => '+1234567890']);
    $items = InventoryItem::factory()->count(2)->create();

    $notification = new LowStockAlert($items);
    $smsMessage = $notification->toSms($admin);

    expect($smsMessage)
        ->toContain('LOW STOCK ALERT')
        ->toContain('2 items');
});

test('low stock alert includes sms channel for admin with phone', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'phone' => '+1234567890']);
    $items = InventoryItem::factory()->count(1)->create();

    $notification = new LowStockAlert($items);
    $channels = $notification->via($admin);

    expect($channels)->toContain('mail')
        ->toContain(\App\Channels\SmsChannel::class);
});

test('low stock alert excludes sms channel for admin without phone', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'phone' => null]);
    $items = InventoryItem::factory()->count(1)->create();

    $notification = new LowStockAlert($items);
    $channels = $notification->via($admin);

    expect($channels)->toContain('mail')
        ->not->toContain(\App\Channels\SmsChannel::class);
});

test('low stock alert excludes sms channel for non-admin users', function (): void {
    $manager = User::factory()->create(['role' => UserRole::Manager, 'phone' => '+1234567890']);
    $items = InventoryItem::factory()->count(1)->create();

    $notification = new LowStockAlert($items);
    $channels = $notification->via($manager);

    expect($channels)->toContain('mail')
        ->not->toContain(\App\Channels\SmsChannel::class);
});
