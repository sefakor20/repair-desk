<?php

declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Enums\DeviceCategory;
use App\Models\{Customer, Device, DeviceBrand, DeviceModel};
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Register Device'])]
class Create extends Component
{
    public array $form = [
        'customer_id' => '',
        'type' => '',
        'brand' => '',
        'model' => '',
        'color' => '',
        'storage_capacity' => '',
        'serial_number' => '',
        'imei' => '',
        'notes' => '',
        'condition' => '',
        'condition_notes' => '',
        'purchase_date' => '',
        'warranty_expiry' => '',
        'warranty_provider' => '',
        'warranty_notes' => '',
        'password_pin' => '',
    ];

    // New fields for enum and relational data
    public ?string $device_type = null;

    public ?int $brand_id = null;

    public ?int $model_id = null;

    public function mount(): void
    {
        $this->authorize('create', Device::class);
        $this->device_type = DeviceCategory::Smartphone->value;
    }

    public function updatedDeviceType(): void
    {
        // Reset brand and model when device type changes
        $this->brand_id = null;
        $this->model_id = null;
        $this->form['brand'] = '';
        $this->form['model'] = '';
    }

    public function updatedBrandId(): void
    {
        // Reset model when brand changes
        $this->model_id = null;
        $this->form['model'] = '';
    }

    public function save(): void
    {
        $this->authorize('create', Device::class);

        $validated = $this->validate([
            'form.customer_id' => ['required', 'exists:customers,id'],
            'device_type' => ['required', 'string'],
            'brand_id' => ['nullable', 'exists:device_brands,id'],
            'model_id' => ['nullable', 'exists:device_models,id'],
            'form.type' => ['nullable', 'string', 'max:255'],
            'form.brand' => ['nullable', 'string', 'max:255'],
            'form.model' => ['nullable', 'string', 'max:255'],
            'form.color' => ['nullable', 'string', 'max:255'],
            'form.storage_capacity' => ['nullable', 'string', 'max:255'],
            'form.serial_number' => ['nullable', 'string', 'max:255'],
            'form.imei' => ['nullable', 'string', 'max:255'],
            'form.notes' => ['nullable', 'string'],
            'form.condition' => ['nullable', 'string', 'in:excellent,good,fair,poor,damaged'],
            'form.condition_notes' => ['nullable', 'string'],
            'form.purchase_date' => ['nullable', 'date'],
            'form.warranty_expiry' => ['nullable', 'date', 'after:form.purchase_date'],
            'form.warranty_provider' => ['nullable', 'string', 'max:255'],
            'form.warranty_notes' => ['nullable', 'string'],
            'form.password_pin' => ['nullable', 'string', 'max:255'],
        ]);

        // Convert empty strings to null for proper enum handling
        $data = array_map(fn($value) => $value === '' ? null : $value, $validated['form']);

        // Add new relational fields
        $data['device_type'] = $this->device_type;
        $data['brand_id'] = $this->brand_id;
        $data['model_id'] = $this->model_id;

        // Populate legacy text fields from selected brand/model if not manually entered
        if ($this->brand_id && ! $data['brand']) {
            $data['brand'] = DeviceBrand::find($this->brand_id)?->name;
        }
        if ($this->model_id && ! $data['model']) {
            $data['model'] = DeviceModel::find($this->model_id)?->name;
        }

        // Automatically set the branch_id to the current user's branch
        $data['branch_id'] = auth()->user()->branch_id;

        $device = Device::create($data);

        session()->flash('success', 'Device registered successfully.');

        $this->redirect(route('devices.show', $device), navigate: true);
    }

    public function getBrandsProperty()
    {
        if (! $this->device_type) {
            return collect();
        }

        return DeviceBrand::query()
            ->active()
            ->where('category', $this->device_type)
            ->orderBy('name')
            ->get();
    }

    public function getModelsProperty()
    {
        if (! $this->brand_id) {
            return collect();
        }

        return DeviceModel::query()
            ->active()
            ->where('brand_id', $this->brand_id)
            ->orderBy('name')
            ->get();
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.devices.create', [
            'customers' => Customer::orderBy('first_name')->get(),
            'deviceCategories' => DeviceCategory::options(),
        ]);
    }
}
