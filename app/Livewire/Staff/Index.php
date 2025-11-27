<?php

declare(strict_types=1);

namespace App\Livewire\Staff;

use App\Enums\StaffRole;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\User;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Staff Management'])]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'role')]
    public string $roleFilter = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'branch')]
    public string $branchFilter = '';

    public bool $showCreateModal = false;

    public array $form = [
        'user_id' => '',
        'role' => '',
        'hire_date' => '',
        'notes' => '',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Staff::class);

        if (!$this->branchFilter && auth()->user()->branch_id) {
            $this->branchFilter = auth()->user()->branch_id;
        }
    }

    protected function rules(): array
    {
        $roleValues = implode(',', array_map(fn($r) => $r->value, StaffRole::cases()));

        return [
            'form.user_id' => 'required|exists:users,id|unique:staff,user_id',
            'form.role' => "required|in:{$roleValues}",
            'form.hire_date' => 'required|date',
            'form.notes' => 'nullable|string|max:1000',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedBranchFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', Staff::class);
        $this->form = ['user_id' => '', 'role' => '', 'hire_date' => '', 'notes' => ''];
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->authorize('create', Staff::class);
        $validated = $this->validate();

        Staff::create([
            'user_id' => $validated['form']['user_id'],
            'branch_id' => $this->branchFilter ?: auth()->user()->branch_id,
            'role' => $validated['form']['role'],
            'hire_date' => $validated['form']['hire_date'],
            'notes' => $validated['form']['notes'],
            'is_active' => true,
        ]);

        Flux::toast(text: 'Staff member added successfully', variant: 'success');
        $this->closeCreateModal();
        $this->resetPage();
    }

    public function updateRole(Staff $staff, string $role): void
    {
        $this->authorize('update', $staff);

        $staff->update(['role' => $role]);
        Flux::toast(text: 'Role updated successfully', variant: 'success');
    }

    public function toggleActive(Staff $staff): void
    {
        $this->authorize('update', $staff);

        $staff->update(['is_active' => !$staff->is_active]);
        Flux::toast(
            text: $staff->is_active ? 'Staff member activated' : 'Staff member deactivated',
            variant: 'success',
        );
    }

    public function delete(Staff $staff): void
    {
        $this->authorize('delete', $staff);

        $staff->delete();
        Flux::toast(text: 'Staff member removed successfully', variant: 'success');
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'roleFilter', 'statusFilter']);
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Staff::query()
            ->with(['user', 'branch'])
            ->when($this->search, fn($q) => $q->whereHas(
                'user',
                fn($u)
                => $u->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"),
            ))
            ->when($this->roleFilter, fn($q) => $q->byRole(StaffRole::from($this->roleFilter)))
            ->when($this->statusFilter === 'active', fn($q) => $q->active())
            ->when($this->statusFilter === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->branchFilter, fn($q) => $q->where('branch_id', $this->branchFilter))
            ->orderBy('created_at', 'desc');

        $staff = $query->paginate(15);

        $branches = auth()->user()->isSuperAdmin()
            ? Branch::active()->get()
            : collect([auth()->user()->branch]);

        $availableUsers = User::query()
            ->when(!auth()->user()->isSuperAdmin(), fn($q) => $q->where('branch_id', auth()->user()->branch_id))
            ->whereNotIn('id', Staff::pluck('user_id'))
            ->get();

        return view('livewire.staff.index', [
            'staff' => $staff,
            'branches' => $branches,
            'availableUsers' => $availableUsers,
            'roles' => StaffRole::cases(),
        ]);
    }
}
