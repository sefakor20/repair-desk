<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Models;

use App\Enums\DeviceCategory;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
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

    public ?int $brandFilter = null;

    public function mount(): void
    {
        $this->authorize('viewAny', DeviceModel::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->brandFilter = null;
        $this->resetPage();
    }

    public function updatedBrandFilter(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $modelId): void
    {
        $model = DeviceModel::findOrFail($modelId);
        $this->authorize('update', $model);

        $model->update(['is_active' => ! $model->is_active]);

        session()->flash('success', 'Model status updated successfully.');
    }

    public function delete(int $modelId): void
    {
        $model = DeviceModel::findOrFail($modelId);
        $this->authorize('delete', $model);

        $model->delete();

        session()->flash('success', 'Model deleted successfully.');
    }

    public function getModelsProperty()
    {
        return DeviceModel::query()
            ->with('brand')
            ->when($this->search, function ($query): void {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->categoryFilter, function ($query): void {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->brandFilter, function ($query): void {
                $query->where('brand_id', $this->brandFilter);
            })
            ->orderBy('name')
            ->paginate(20);
    }

    public function getBrandsProperty()
    {
        $query = DeviceBrand::query()->active()->orderBy('name');

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        return $query->get();
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.admin.models.index', [
            'categories' => DeviceCategory::options(),
        ]);
    }
}
