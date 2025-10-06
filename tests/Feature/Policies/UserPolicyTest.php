<?php

declare(strict_types=1);

use App\Models\User;

test('only admin can view all users', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();

    expect($admin->can('viewAny', User::class))->toBeTrue()
        ->and($manager->can('viewAny', User::class))->toBeFalse()
        ->and($technician->can('viewAny', User::class))->toBeFalse();
});

test('admin can view any user but others can only view themselves', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $otherUser = User::factory()->create();

    expect($admin->can('view', $otherUser))->toBeTrue()
        ->and($manager->can('view', $manager))->toBeTrue()
        ->and($manager->can('view', $otherUser))->toBeFalse()
        ->and($technician->can('view', $technician))->toBeTrue()
        ->and($technician->can('view', $otherUser))->toBeFalse();
});

test('only admin can create users', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();

    expect($admin->can('create', User::class))->toBeTrue()
        ->and($manager->can('create', User::class))->toBeFalse()
        ->and($technician->can('create', User::class))->toBeFalse();
});

test('admin can update any user but others can only update themselves', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $otherUser = User::factory()->create();

    expect($admin->can('update', $otherUser))->toBeTrue()
        ->and($manager->can('update', $manager))->toBeTrue()
        ->and($manager->can('update', $otherUser))->toBeFalse()
        ->and($technician->can('update', $technician))->toBeTrue()
        ->and($technician->can('update', $otherUser))->toBeFalse();
});

test('only admin can delete users but not themselves', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $otherAdmin = User::factory()->admin()->create();

    expect($admin->can('delete', $manager))->toBeTrue()
        ->and($admin->can('delete', $admin))->toBeFalse()
        ->and($manager->can('delete', $technician))->toBeFalse()
        ->and($technician->can('delete', $otherAdmin))->toBeFalse();
});

test('only admin and manager can view reports', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('viewReports', User::class))->toBeTrue()
        ->and($manager->can('viewReports', User::class))->toBeTrue()
        ->and($technician->can('viewReports', User::class))->toBeFalse()
        ->and($frontDesk->can('viewReports', User::class))->toBeFalse();
});

test('only admin can access system settings', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();

    expect($admin->can('accessSettings', User::class))->toBeTrue()
        ->and($manager->can('accessSettings', User::class))->toBeFalse()
        ->and($technician->can('accessSettings', User::class))->toBeFalse();
});
