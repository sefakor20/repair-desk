<?php

declare(strict_types=1);

use App\Models\{Customer, User};

test('all authenticated users can view any customers', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('viewAny', Customer::class))->toBeTrue()
        ->and($manager->can('viewAny', Customer::class))->toBeTrue()
        ->and($technician->can('viewAny', Customer::class))->toBeTrue()
        ->and($frontDesk->can('viewAny', Customer::class))->toBeTrue();
});

test('all authenticated users can view a customer', function () {
    $customer = Customer::factory()->create();
    $admin = User::factory()->admin()->create();
    $technician = User::factory()->technician()->create();

    expect($admin->can('view', $customer))->toBeTrue()
        ->and($technician->can('view', $customer))->toBeTrue();
});

test('all authenticated users can create customers', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('create', Customer::class))->toBeTrue()
        ->and($manager->can('create', Customer::class))->toBeTrue()
        ->and($technician->can('create', Customer::class))->toBeTrue()
        ->and($frontDesk->can('create', Customer::class))->toBeTrue();
});

test('all authenticated users can update customers', function () {
    $customer = Customer::factory()->create();
    $admin = User::factory()->admin()->create();
    $technician = User::factory()->technician()->create();

    expect($admin->can('update', $customer))->toBeTrue()
        ->and($technician->can('update', $customer))->toBeTrue();
});

test('only admin and manager can delete customers', function () {
    $customer = Customer::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('delete', $customer))->toBeTrue()
        ->and($manager->can('delete', $customer))->toBeTrue()
        ->and($technician->can('delete', $customer))->toBeFalse()
        ->and($frontDesk->can('delete', $customer))->toBeFalse();
});

test('only admin can force delete customers', function () {
    $customer = Customer::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();

    expect($admin->can('forceDelete', $customer))->toBeTrue()
        ->and($manager->can('forceDelete', $customer))->toBeFalse()
        ->and($technician->can('forceDelete', $customer))->toBeFalse();
});
