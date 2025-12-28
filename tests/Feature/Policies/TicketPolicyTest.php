<?php

declare(strict_types=1);

use App\Models\{Ticket, User};

test('all authenticated users can view any tickets', function (): void {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('viewAny', Ticket::class))->toBeTrue()
        ->and($manager->can('viewAny', Ticket::class))->toBeTrue()
        ->and($technician->can('viewAny', Ticket::class))->toBeTrue()
        ->and($frontDesk->can('viewAny', Ticket::class))->toBeTrue();
});

test('all authenticated users can view a ticket', function (): void {
    $ticket = Ticket::factory()->create();
    $admin = User::factory()->admin()->create();
    $technician = User::factory()->technician()->create();

    expect($admin->can('view', $ticket))->toBeTrue()
        ->and($technician->can('view', $ticket))->toBeTrue();
});

test('all authenticated users can create tickets', function (): void {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('create', Ticket::class))->toBeTrue()
        ->and($manager->can('create', Ticket::class))->toBeTrue()
        ->and($technician->can('create', Ticket::class))->toBeTrue()
        ->and($frontDesk->can('create', Ticket::class))->toBeTrue();
});

test('all authenticated users can update tickets', function (): void {
    $ticket = Ticket::factory()->create();
    $admin = User::factory()->admin()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('update', $ticket))->toBeTrue()
        ->and($technician->can('update', $ticket))->toBeTrue()
        ->and($frontDesk->can('update', $ticket))->toBeTrue();
});

test('only admin and manager can delete tickets', function (): void {
    $ticket = Ticket::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('delete', $ticket))->toBeTrue()
        ->and($manager->can('delete', $ticket))->toBeTrue()
        ->and($technician->can('delete', $ticket))->toBeFalse()
        ->and($frontDesk->can('delete', $ticket))->toBeFalse();
});

test('admin manager and front desk can assign tickets', function (): void {
    $ticket = Ticket::factory()->create();
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    expect($admin->can('assign', $ticket))->toBeTrue()
        ->and($manager->can('assign', $ticket))->toBeTrue()
        ->and($frontDesk->can('assign', $ticket))->toBeTrue()
        ->and($technician->can('assign', $ticket))->toBeFalse();
});
