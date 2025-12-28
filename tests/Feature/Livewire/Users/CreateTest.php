<?php

declare(strict_types=1);

use App\Livewire\Users\Create;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('only admin can access user create page', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->assertSuccessful();
});

test('non-admin users cannot access user create page', function (): void {
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();
    $frontDesk = User::factory()->create();

    Livewire::actingAs($manager)
        ->test(Create::class)
        ->assertForbidden();

    Livewire::actingAs($technician)
        ->test(Create::class)
        ->assertForbidden();

    Livewire::actingAs($frontDesk)
        ->test(Create::class)
        ->assertForbidden();
});

test('admin can create a new user', function (): void {
    $admin = User::factory()->admin()->create();

    expect(User::count())->toBe(1);

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('role', 'technician')
        ->set('phone', '1234567890')
        ->set('active', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    expect(User::count())->toBe(2);

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('John Doe')
        ->and($user->role->value)->toBe('technician')
        ->and($user->phone)->toBe('1234567890')
        ->and($user->active)->toBeTrue()
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});

test('name is required', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', '')
        ->set('email', 'john@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('email is required', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', '')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('save')
        ->assertHasErrors(['email' => 'required']);
});

test('email must be valid', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'invalid-email')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('email must be unique', function (): void {
    $admin = User::factory()->admin()->create();
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'existing@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('password is required', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->call('save')
        ->assertHasErrors(['password' => 'required']);
});

test('password must be confirmed', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'different')
        ->call('save')
        ->assertHasErrors(['password']);
});

test('password must meet minimum requirements', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', 'short')
        ->set('password_confirmation', 'short')
        ->call('save')
        ->assertHasErrors(['password']);
});

test('role is required', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('role', '')
        ->call('save')
        ->assertHasErrors(['role' => 'required']);
});

test('role must be valid', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('role', 'invalid_role')
        ->call('save')
        ->assertHasErrors(['role']);
});

test('phone is optional', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('role', 'technician')
        ->set('phone', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(User::where('email', 'john@example.com')->first()->phone)->toBeNull();
});

test('user can be created as inactive', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('role', 'technician')
        ->set('active', false)
        ->call('save')
        ->assertHasNoErrors();

    expect(User::where('email', 'john@example.com')->first()->active)->toBeFalse();
});
