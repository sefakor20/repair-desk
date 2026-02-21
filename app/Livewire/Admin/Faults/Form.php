<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Faults;

use App\Enums\DeviceCategory;
use App\Models\CommonFault;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public ?CommonFault $fault = null;

    public string $name = '';

    public string $description = '';

    public ?string $device_category = null;

    public int $sort_order = 0;

    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?CommonFault $fault = null): void
    {
        if ($fault && $fault->exists) {
            $this->authorize('update', $fault);
            $this->fault = $fault;
            $this->isEditing = true;
            $this->name = $fault->name;
            $this->description = $fault->description ?? '';
            $this->device_category = $fault->device_category?->value;
            $this->sort_order = $fault->sort_order;
            $this->is_active = $fault->is_active;
        } else {
            $this->authorize('create', CommonFault::class);
            $this->fault = new CommonFault();
            $this->device_category = 'universal';
            // Get the next sort order
            $this->sort_order = (CommonFault::max('sort_order') ?? 0) + 10;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'device_category' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        if ($this->isEditing) {
            $this->authorize('update', $this->fault);
        } else {
            $this->authorize('create', CommonFault::class);
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'device_category' => $this->device_category === 'universal' ? null : $this->device_category,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $this->fault->update($data);
            session()->flash('success', 'Fault updated successfully.');
        } else {
            CommonFault::create($data);
            session()->flash('success', 'Fault created successfully.');
        }

        $this->redirect(route('admin.faults.index'), navigate: true);
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $categories = DeviceCategory::options();
        $categories = array_merge(['universal' => 'Universal (All Devices)'], $categories);

        return view('livewire.admin.faults.form', [
            'categories' => $categories,
        ]);
    }
}
