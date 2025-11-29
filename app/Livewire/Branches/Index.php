<?php

declare(strict_types=1);

namespace App\Livewire\Branches;

use App\Models\Branch;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(Branch $branch): void
    {
        $this->authorize('delete', $branch);

        // Check if branch has related records
        $relatedCounts = [
            'users' => $branch->users()->count(),
            'tickets' => $branch->tickets()->count(),
            'inventory items' => $branch->inventoryItems()->count(),
            'POS sales' => $branch->posSales()->count(),
        ];

        $hasRelated = array_sum($relatedCounts) > 0;

        if ($hasRelated) {
            $messages = [];
            foreach ($relatedCounts as $type => $count) {
                if ($count > 0) {
                    $messages[] = "{$count} {$type}";
                }
            }

            Flux::toast(
                text: 'Cannot delete branch with related records: ' . implode(', ', $messages),
                variant: 'danger',
            );

            return;
        }

        $branch->delete();

        Flux::toast(
            text: 'Branch deleted successfully.',
            variant: 'success',
        );
    }

    public function toggleStatus(Branch $branch): void
    {
        $this->authorize('update', $branch);

        $branch->update(['is_active' => !$branch->is_active]);

        Flux::toast(
            text: 'Branch status updated successfully.',
            variant: 'success',
        );
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    public function render()
    {
        $branches = Branch::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('city', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->withCount(['users', 'tickets', 'inventoryItems', 'posSales'])
            ->orderBy('is_main', 'desc')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.branches.index', [
            'branches' => $branches,
        ]);
    }
}
