<?php

declare(strict_types=1);

use App\Models\{InventoryItem, User};

test('all authenticated users can view inventory', function () {
    $item = InventoryItem::factory()->create();
    $admin = User::factory()->admin()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('view', $item))->toBeTrue()
        ->and($technician->can('view', $item))->toBeTrue()
        ->and($frontDesk->can('view', $item))->toBeTrue();
});

test('only admin and manager can create inventory items', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('create', InventoryItem::class))->toBeTrue()
        ->and($manager->can('create', InventoryItem::class))->toBeTrue()
        ->and($technician->can('create', InventoryItem::class))->toBeFalse()
        ->and($frontDesk->can('create', InventoryItem::class))->toBeFalse();
});

test('only admin and manager can update inventory items', function () {
    $item = InventoryItem::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('update', $item))->toBeTrue()
        ->and($manager->can('update', $item))->toBeTrue()
        ->and($technician->can('update', $item))->toBeFalse()
        ->and($frontDesk->can('update', $item))->toBeFalse();
});

test('only admin and manager can delete inventory items', function () {
    $item = InventoryItem::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('delete', $item))->toBeTrue()
        ->and($manager->can('delete', $item))->toBeTrue()
        ->and($technician->can('delete', $item))->toBeFalse()
        ->and($frontDesk->can('delete', $item))->toBeFalse();
});

test('only admin and manager can adjust inventory quantities', function () {
    $item = InventoryItem::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('adjustQuantity', $item))->toBeTrue()
        ->and($manager->can('adjustQuantity', $item))->toBeTrue()
        ->and($technician->can('adjustQuantity', $item))->toBeFalse()
        ->and($frontDesk->can('adjustQuantity', $item))->toBeFalse();
});
