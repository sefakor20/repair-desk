<?php

declare(strict_types=1);

use App\Livewire\Users\Index;
use App\Models\User;
use Livewire\Livewire;

test('only admin can access users index page', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->assertSuccessful();
});

test('non-admin users cannot access users index page', function () {
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    Livewire::actingAs($manager)
        ->test(Index::class)
        ->assertForbidden();

    Livewire::actingAs($technician)
        ->test(Index::class)
        ->assertForbidden();

    Livewire::actingAs($frontDesk)
        ->test(Index::class)
        ->assertForbidden();
});

test('users index displays all users', function () {
    $admin = User::factory()->admin()->create();
    $users = User::factory()->count(5)->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->assertSee($users[0]->name)
        ->assertSee($users[0]->email);
});

test('search filters users by name', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['name' => 'John Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Smith']);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

test('search filters users by email', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['email' => 'john@example.com']);
    $user2 = User::factory()->create(['email' => 'jane@example.com']);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('search', 'john@')
        ->assertSee('john@example.com')
        ->assertDontSee('jane@example.com');
});

test('role filter works correctly', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('roleFilter', 'manager')
        ->assertSee($manager->email)
        ->assertDontSee($technician->email);
});

test('status filter shows only active users', function () {
    $admin = User::factory()->admin()->create();
    $activeUser = User::factory()->create(['active' => true]);
    $inactiveUser = User::factory()->create(['active' => false]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('statusFilter', '1')
        ->assertSee($activeUser->email)
        ->assertDontSee($inactiveUser->email);
});

test('status filter shows only inactive users', function () {
    $admin = User::factory()->admin()->create();
    $activeUser = User::factory()->create(['active' => true]);
    $inactiveUser = User::factory()->create(['active' => false]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('statusFilter', '0')
        ->assertSee($inactiveUser->email)
        ->assertDontSee($activeUser->email);
});

test('admin can delete other users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    expect(User::count())->toBe(2);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('deleteUser', $user->id)
        ->assertDispatched('user-deleted');

    expect(User::count())->toBe(1);
});

test('admin cannot delete themselves', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('deleteUser', $admin->id)
        ->assertForbidden();
});

test('admin can toggle user status', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['active' => true]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('toggleStatus', $user->id);

    expect($user->fresh()->active)->toBeFalse();
});

test('pagination works correctly', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(20)->create();

    $component = Livewire::actingAs($admin)
        ->test(Index::class);

    $component->assertSee('1')
        ->assertSee('2');
});
