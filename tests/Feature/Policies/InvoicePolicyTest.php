<?php

declare(strict_types=1);

use App\Models\{Invoice, User};

test('all authenticated users can view invoices', function (): void {
    $invoice = Invoice::factory()->create();
    $admin = User::factory()->admin()->create();
    $technician = User::factory()->technician()->create();

    expect($admin->can('view', $invoice))->toBeTrue()
        ->and($technician->can('view', $invoice))->toBeTrue();
});

test('admin manager and front desk can create invoices', function (): void {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('create', Invoice::class))->toBeTrue()
        ->and($manager->can('create', Invoice::class))->toBeTrue()
        ->and($frontDesk->can('create', Invoice::class))->toBeTrue()
        ->and($technician->can('create', Invoice::class))->toBeFalse();
});

test('admin manager and front desk can update invoices', function (): void {
    $invoice = Invoice::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('update', $invoice))->toBeTrue()
        ->and($manager->can('update', $invoice))->toBeTrue()
        ->and($frontDesk->can('update', $invoice))->toBeTrue()
        ->and($technician->can('update', $invoice))->toBeFalse();
});

test('only admin and manager can delete invoices', function (): void {
    $invoice = Invoice::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('delete', $invoice))->toBeTrue()
        ->and($manager->can('delete', $invoice))->toBeTrue()
        ->and($technician->can('delete', $invoice))->toBeFalse()
        ->and($frontDesk->can('delete', $invoice))->toBeFalse();
});

test('admin manager and front desk can process payments', function (): void {
    $invoice = Invoice::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('processPayment', $invoice))->toBeTrue()
        ->and($manager->can('processPayment', $invoice))->toBeTrue()
        ->and($frontDesk->can('processPayment', $invoice))->toBeTrue()
        ->and($technician->can('processPayment', $invoice))->toBeFalse();
});
