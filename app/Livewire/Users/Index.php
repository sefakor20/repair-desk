<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public string $statusFilter = '';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteUser(string $userId): void
    {
        $user = User::findOrFail($userId);

        $this->authorize('delete', $user);

        $user->delete();

        session()->flash('success', 'User deleted successfully.');

        $this->dispatch('user-deleted');
    }

    public function toggleStatus(string $userId): void
    {
        $user = User::findOrFail($userId);

        $this->authorize('update', $user);

        $user->update(['active' => !$user->active]);

        session()->flash('success', 'User status updated successfully.');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter, fn($query) => $query->where('role', $this->roleFilter))
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('active', $this->statusFilter === '1');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.users.index', [
            'users' => $users,
        ]);
    }
}
