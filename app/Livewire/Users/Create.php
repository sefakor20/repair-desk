<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';

    public string $phone = '';

    public bool $active = true;

    public function mount(): void
    {
        $this->authorize('create', User::class);

        $this->role = UserRole::FrontDesk->value;
    }

    public function save(): void
    {
        $this->authorize('create', User::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,manager,technician,front_desk'],
            'phone' => ['nullable', 'string', 'max:20'],
            'active' => ['boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?: null,
            'active' => $validated['active'],
        ]);

        session()->flash('success', 'User created successfully.');

        $this->redirect(route('users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.users.create', [
            'roles' => UserRole::cases(),
        ]);
    }
}
