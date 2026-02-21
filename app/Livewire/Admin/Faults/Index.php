<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Faults;

use App\Enums\DeviceCategory;
use App\Models\CommonFault;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public ?string $categoryFilter = null;

    public function mount(): void
    {
        $this->authorize('viewAny', CommonFault::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $faultId): void
    {
        $fault = CommonFault::findOrFail($faultId);
        $this->authorize('update', $fault);

        $fault->update(['is_active' => ! $fault->is_active]);

        session()->flash('success', 'Fault status updated successfully.');
    }

    public function delete(int $faultId): void
    {
        $fault = CommonFault::findOrFail($faultId);
        $this->authorize('delete', $fault);

        $fault->delete();

        session()->flash('success', 'Fault deleted successfully.');
    }

    public function getFaultsProperty()
    {
        return CommonFault::query()
            ->when($this->search, function ($query): void {
                $query->where(function ($q): void {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter === 'universal', function ($query): void {
                $query->whereNull('device_category');
            })
            ->when($this->categoryFilter && $this->categoryFilter !== 'universal', function ($query): void {
                $query->where('device_category', $this->categoryFilter);
            })
            ->ordered()
            ->paginate(20);
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $categories = DeviceCategory::options();
        $categories = array_merge(['universal' => 'Universal (All Devices)'], $categories);

        return view('livewire.admin.faults.index', [
            'categories' => $categories,
        ]);
    }
}
