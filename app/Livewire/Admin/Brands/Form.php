<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Brands;

use App\Enums\DeviceCategory;
use App\Models\DeviceBrand;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?DeviceBrand $brand = null;

    public string $name = '';

    public string $category = '';

    public $logo = null;

    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?DeviceBrand $brand = null): void
    {
        if ($brand && $brand->exists) {
            $this->authorize('update', $brand);
            $this->brand = $brand;
            $this->isEditing = true;
            $this->name = $brand->name;
            $this->category = $brand->category->value;
            $this->is_active = $brand->is_active;
        } else {
            $this->authorize('create', DeviceBrand::class);
            $this->brand = new DeviceBrand();
            $this->category = DeviceCategory::Smartphone->value;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if ($this->isEditing) {
            $this->authorize('update', $this->brand);
        } else {
            $this->authorize('create', DeviceBrand::class);
        }

        $data = [
            'name' => $this->name,
            'category' => $this->category,
            'is_active' => $this->is_active,
        ];

        if ($this->logo) {
            $data['logo_path'] = $this->logo->store('brands', 'public');
        }

        if ($this->isEditing) {
            $this->brand->update($data);
            session()->flash('success', 'Brand updated successfully.');
        } else {
            DeviceBrand::create($data);
            session()->flash('success', 'Brand created successfully.');
        }

        $this->redirect(route('admin.brands.index'), navigate: true);
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.admin.brands.form', [
            'categories' => DeviceCategory::options(),
        ]);
    }
}
