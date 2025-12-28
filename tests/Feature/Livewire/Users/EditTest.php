<?php

declare(strict_types=1);

use App\Livewire\Users\Edit;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('admin can access user edit page', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->assertSuccessful();
});

test('user can edit their own profile', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['user' => $user])
        ->assertSuccessful();
});

test('user cannot edit another users profile', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['user' => $otherUser])
        ->assertForbidden();
});

test('admin can update a user', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'role' => 'technician',
    ]);

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->set('role', 'manager')
        ->set('phone', '9876543210')
        ->set('active', false)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com')
        ->and($user->role->value)->toBe('manager')
        ->and($user->phone)->toBe('9876543210')
        ->and($user->active)->toBeFalse();
});

test('password can be updated', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['password' => Hash::make('oldpassword')]);

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

test('password is optional when editing', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
    $originalPassword = $user->password;

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('name', 'Updated Name')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->password)->toBe($originalPassword);
});

test('password must be confirmed when updating', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'different')
        ->call('save')
        ->assertHasErrors(['password']);
});

test('name is required', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('email is required', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('email', '')
        ->call('save')
        ->assertHasErrors(['email' => 'required']);
});

test('email must be valid', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('email', 'invalid-email')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('email must be unique except for current user', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['email' => 'user@example.com']);
    $otherUser = User::factory()->create(['email' => 'other@example.com']);

    // Can keep same email
    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('email', 'user@example.com')
        ->call('save')
        ->assertHasNoErrors();

    // Cannot use another user's email
    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('email', 'other@example.com')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('role is required', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('role', '')
        ->call('save')
        ->assertHasErrors(['role' => 'required']);
});

test('role must be valid', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('role', 'invalid_role')
        ->call('save')
        ->assertHasErrors(['role']);
});

test('form is pre-filled with user data', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'technician',
        'phone' => '1234567890',
        'active' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->assertSet('name', 'Test User')
        ->assertSet('email', 'test@example.com')
        ->assertSet('role', 'technician')
        ->assertSet('phone', '1234567890')
        ->assertSet('active', false);
});

test('phone can be cleared', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['phone' => '1234567890']);

    Livewire::actingAs($admin)
        ->test(Edit::class, ['user' => $user])
        ->set('phone', '')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->phone)->toBeNull();
});
