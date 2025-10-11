<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\{Device, User};

test('any authenticated user can view any device', function () {
    $user = User::factory()->create(['role' => UserRole::Technician]);
    $device = Device::factory()->create();

    expect($user->can('viewAny', Device::class))->toBeTrue();
});

test('any authenticated user can view a device', function () {
    $user = User::factory()->create(['role' => UserRole::Technician]);
    $device = Device::factory()->create();

    expect($user->can('view', $device))->toBeTrue();
});

test('any authenticated user can create a device', function () {
    $user = User::factory()->create(['role' => UserRole::Technician]);

    expect($user->can('create', Device::class))->toBeTrue();
});

test('any authenticated user can update a device', function () {
    $user = User::factory()->create(['role' => UserRole::Technician]);
    $device = Device::factory()->create();

    expect($user->can('update', $device))->toBeTrue();
});

test('admin can delete a device', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    $device = Device::factory()->create();

    expect($user->can('delete', $device))->toBeTrue();
});

test('manager can delete a device', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);
    $device = Device::factory()->create();

    expect($user->can('delete', $device))->toBeTrue();
});

test('technician cannot delete a device', function () {
    $user = User::factory()->create(['role' => UserRole::Technician]);
    $device = Device::factory()->create();

    expect($user->can('delete', $device))->toBeFalse();
});

test('admin can restore a device', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    $device = Device::factory()->create();

    expect($user->can('restore', $device))->toBeTrue();
});

test('manager can restore a device', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);
    $device = Device::factory()->create();

    expect($user->can('restore', $device))->toBeTrue();
});

test('technician cannot restore a device', function () {
    $user = User::factory()->create(['role' => UserRole::Technician]);
    $device = Device::factory()->create();

    expect($user->can('restore', $device))->toBeFalse();
});

test('only admin can force delete a device', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    $technician = User::factory()->create(['role' => UserRole::Technician]);
    $device = Device::factory()->create();

    expect($admin->can('forceDelete', $device))->toBeTrue();
    expect($manager->can('forceDelete', $device))->toBeFalse();
    expect($technician->can('forceDelete', $device))->toBeFalse();
});
