<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';

    public string $phone = '';

    public bool $active = true;

    public function mount(User $user): void
    {
        $this->authorize('update', $user);

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->phone = $user->phone ?? '';
        $this->active = (bool) $user->active;
    }

    public function save(): void
    {
        $this->authorize('update', $this->user);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,manager,technician,front_desk'],
            'phone' => ['nullable', 'string', 'max:20'],
            'active' => ['boolean'],
        ]);

        $this->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?: null,
            'active' => $validated['active'],
        ]);

        if (!empty($validated['password'])) {
            $this->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        session()->flash('success', 'User updated successfully.');

        $this->redirect(route('users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.users.edit', [
            'roles' => UserRole::cases(),
        ]);
    }
}
