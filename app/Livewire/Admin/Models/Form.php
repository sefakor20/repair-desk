<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Models;

use App\Enums\DeviceCategory;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public ?DeviceModel $model = null;

    public string $name = '';

    public string $category = '';

    public ?int $brand_id = null;

    public array $specifications = [];

    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?DeviceModel $model = null): void
    {
        if ($model && $model->exists) {
            $this->authorize('update', $model);
            $this->model = $model;
            $this->isEditing = true;
            $this->name = $model->name;
            $this->category = $model->category->value;
            $this->brand_id = $model->brand_id;
            $this->specifications = $model->specifications ?? [];
            $this->is_active = $model->is_active;
        } else {
            $this->authorize('create', DeviceModel::class);
            $this->model = new DeviceModel();
            $this->category = DeviceCategory::Smartphone->value;
        }
    }

    public function updatedCategory(): void
    {
        // Reset brand when category changes
        $this->brand_id = null;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'brand_id' => ['required', 'exists:device_brands,id'],
            'specifications' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        if ($this->isEditing) {
            $this->authorize('update', $this->model);
        } else {
            $this->authorize('create', DeviceModel::class);
        }

        $data = [
            'name' => $this->name,
            'category' => $this->category,
            'brand_id' => $this->brand_id,
            'specifications' => ! empty($this->specifications) ? $this->specifications : null,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $this->model->update($data);
            session()->flash('success', 'Model updated successfully.');
        } else {
            DeviceModel::create($data);
            session()->flash('success', 'Model created successfully.');
        }

        $this->redirect(route('admin.models.index'), navigate: true);
    }

    public function getBrandsProperty()
    {
        return DeviceBrand::query()
            ->active()
            ->where('category', $this->category)
            ->orderBy('name')
            ->get();
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.admin.models.form', [
            'categories' => DeviceCategory::options(),
        ]);
    }
}
