<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\{PosSale, User};

test('all authenticated users can view POS sales', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    $technician = User::factory()->create(['role' => UserRole::Technician]);
    $frontDesk = User::factory()->create(['role' => UserRole::FrontDesk]);

    expect($admin->can('viewAny', PosSale::class))->toBeTrue();
    expect($manager->can('viewAny', PosSale::class))->toBeTrue();
    expect($technician->can('viewAny', PosSale::class))->toBeTrue();
    expect($frontDesk->can('viewAny', PosSale::class))->toBeTrue();
});

test('all authenticated users can create POS sales', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    $technician = User::factory()->create(['role' => UserRole::Technician]);
    $frontDesk = User::factory()->create(['role' => UserRole::FrontDesk]);

    expect($admin->can('create', PosSale::class))->toBeTrue();
    expect($manager->can('create', PosSale::class))->toBeTrue();
    expect($technician->can('create', PosSale::class))->toBeTrue();
    expect($frontDesk->can('create', PosSale::class))->toBeTrue();
});

test('only admin and manager can update POS sales', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    $technician = User::factory()->create(['role' => UserRole::Technician]);
    $frontDesk = User::factory()->create(['role' => UserRole::FrontDesk]);

    $sale = PosSale::factory()->create();

    expect($admin->can('update', $sale))->toBeTrue();
    expect($manager->can('update', $sale))->toBeTrue();
    expect($technician->can('update', $sale))->toBeFalse();
    expect($frontDesk->can('update', $sale))->toBeFalse();
});

test('only admin and manager can delete POS sales', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    $technician = User::factory()->create(['role' => UserRole::Technician]);
    $frontDesk = User::factory()->create(['role' => UserRole::FrontDesk]);

    $sale = PosSale::factory()->create();

    expect($admin->can('delete', $sale))->toBeTrue();
    expect($manager->can('delete', $sale))->toBeTrue();
    expect($technician->can('delete', $sale))->toBeFalse();
    expect($frontDesk->can('delete', $sale))->toBeFalse();
});

test('only admin and manager can refund POS sales', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    $technician = User::factory()->create(['role' => UserRole::Technician]);
    $frontDesk = User::factory()->create(['role' => UserRole::FrontDesk]);

    $sale = PosSale::factory()->create();

    expect($admin->can('refund', $sale))->toBeTrue();
    expect($manager->can('refund', $sale))->toBeTrue();
    expect($technician->can('refund', $sale))->toBeFalse();
    expect($frontDesk->can('refund', $sale))->toBeFalse();
});
