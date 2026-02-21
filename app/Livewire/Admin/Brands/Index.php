<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Brands;

use App\Enums\DeviceCategory;
use App\Models\DeviceBrand;
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
        $this->authorize('viewAny', DeviceBrand::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $brandId): void
    {
        $brand = DeviceBrand::findOrFail($brandId);
        $this->authorize('update', $brand);

        $brand->update(['is_active' => ! $brand->is_active]);

        session()->flash('success', 'Brand status updated successfully.');
    }

    public function delete(int $brandId): void
    {
        $brand = DeviceBrand::findOrFail($brandId);
        $this->authorize('delete', $brand);

        $brand->delete();

        session()->flash('success', 'Brand deleted successfully.');
    }

    public function getBrandsProperty()
    {
        return DeviceBrand::query()
            ->with('models')
            ->when($this->search, function ($query): void {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->categoryFilter, function ($query): void {
                $query->where('category', $this->categoryFilter);
            })
            ->orderBy('name')
            ->paginate(20);
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.admin.brands.index', [
            'categories' => DeviceCategory::options(),
        ]);
    }
}
