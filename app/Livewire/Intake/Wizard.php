<?php

declare(strict_types=1);

namespace App\Livewire\Intake;

use App\Enums\AssessmentType;
use App\Enums\DeviceCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\CommonFault;
use App\Models\Customer;
use App\Models\Device;
use App\Models\DeviceAssessment;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\Ticket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Exception;

class Wizard extends Component
{
    use AuthorizesRequests;

    public int $currentStep = 1;

    public int $totalSteps = 5;

    // Customer data
    public ?int $customer_id = null;

    public string $customer_name = '';

    public string $customer_email = '';

    public string $customer_phone = '';

    public bool $createNewCustomer = false;

    // Device data
    public ?string $device_type = null;

    public ?int $brand_id = null;

    public ?int $model_id = null;

    public string $brand = '';

    public string $model = '';

    public string $imei = '';

    public string $device_password = '';

    public ?string $cosmetic_condition = null;

    // Warranty data
    public ?string $purchase_date = null;

    public bool $under_warranty = false;

    public ?string $warranty_expiry_date = null;

    public ?string $warranty_provider = null;

    // Ticket data
    public string $reported_issue = '';

    public array $diagnosed_faults = [];

    public string $priority = '';

    public ?string $estimated_completion = null;

    // Assessment data (from nested component)
    public array $assessmentData = [];

    // Models
    public ?Customer $selectedCustomer = null;

    public ?Device $device = null;

    public ?Ticket $ticket = null;

    protected $listeners = ['assessment-data-updated' => 'updateAssessmentData'];

    public function updateAssessmentData(array $assessmentData): void
    {
        $this->assessmentData = $assessmentData;
    }

    public function mount(): void
    {
        $this->authorize('create', Ticket::class);
        $this->priority = TicketPriority::Normal->value;
        $this->device_type = DeviceCategory::Smartphone->value;
    }

    public function updatedCustomerId(): void
    {
        if ($this->customer_id) {
            $this->selectedCustomer = Customer::find($this->customer_id);
            if ($this->selectedCustomer) {
                $this->customer_name = $this->selectedCustomer->name;
                $this->customer_email = $this->selectedCustomer->email ?? '';
                $this->customer_phone = $this->selectedCustomer->phone ?? '';
                $this->createNewCustomer = false;
            }
        }
    }

    public function updatedCreateNewCustomer(): void
    {
        if ($this->createNewCustomer) {
            $this->customer_id = null;
            $this->selectedCustomer = null;
            $this->customer_name = '';
            $this->customer_email = '';
            $this->customer_phone = '';
        }
    }

    public function updatedDeviceType(): void
    {
        // Reset brand and model when device type changes
        $this->brand_id = null;
        $this->model_id = null;
    }

    public function updatedBrandId(): void
    {
        // Reset model when brand changes
        $this->model_id = null;
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    protected function validateCurrentStep(): void
    {
        $rules = match ($this->currentStep) {
            1 => $this->getCustomerValidationRules(),
            2 => $this->getDeviceValidationRules(),
            3 => $this->getWarrantyValidationRules(),
            4 => [], // Assessment is optional
            5 => $this->getTicketValidationRules(),
            default => [],
        };

        if (! empty($rules)) {
            $this->validate($rules);
        }
    }

    protected function getCustomerValidationRules(): array
    {
        if ($this->createNewCustomer) {
            return [
                'customer_name' => ['required', 'string', 'max:255'],
                'customer_phone' => ['required', 'string', 'max:20'],
                'customer_email' => ['nullable', 'email', 'max:255'],
            ];
        }

        return [
            'customer_id' => ['required', 'exists:customers,id'],
        ];
    }

    protected function getDeviceValidationRules(): array
    {
        return [
            'device_type' => ['required', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'imei' => ['nullable', 'string', 'max:255'],
            'device_password' => ['nullable', 'string', 'max:255'],
            'cosmetic_condition' => ['nullable', 'string'],
        ];
    }

    protected function getWarrantyValidationRules(): array
    {
        return [
            'purchase_date' => ['nullable', 'date'],
            'under_warranty' => ['boolean'],
            'warranty_expiry_date' => ['nullable', 'date'],
            'warranty_provider' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function getTicketValidationRules(): array
    {
        return [
            'reported_issue' => ['required', 'string'],
            'priority' => ['required', 'string'],
            'estimated_completion' => ['nullable', 'date'],
        ];
    }

    public function submit(): void
    {
        $this->validateCurrentStep();

        DB::beginTransaction();

        try {
            // 1. Create or get customer
            if ($this->createNewCustomer) {
                $this->selectedCustomer = Customer::create([
                    'name' => $this->customer_name,
                    'email' => $this->customer_email ?: null,
                    'phone' => $this->customer_phone,
                    'branch_id' => auth()->user()?->branch_id,
                ]);
            } elseif ($this->customer_id) {
                $this->selectedCustomer = Customer::findOrFail($this->customer_id);
            }

            // 2. Create device
            $deviceData = [
                'customer_id' => $this->selectedCustomer->id,
                'device_type' => $this->device_type,
                'brand_id' => $this->brand_id,
                'model_id' => $this->model_id,
                'brand' => $this->brand ?: ($this->brand_id ? DeviceBrand::find($this->brand_id)?->name : null),
                'model' => $this->model ?: ($this->model_id ? DeviceModel::find($this->model_id)?->name : null),
                'imei' => $this->imei ?: null,
                'password' => $this->device_password ?: null,
                'cosmetic_condition' => $this->cosmetic_condition,
                'purchase_date' => $this->purchase_date,
                'under_warranty' => $this->under_warranty,
                'warranty_expiry_date' => $this->warranty_expiry_date,
                'warranty_provider' => $this->warranty_provider,
                'diagnosed_faults' => $this->diagnosed_faults,
                'branch_id' => auth()->user()?->branch_id,
            ];

            $this->device = Device::create($deviceData);

            // 3. Create ticket
            $this->ticket = Ticket::create([
                'customer_id' => $this->selectedCustomer->id,
                'device_id' => $this->device->id,
                'reported_issue' => $this->reported_issue,
                'priority' => $this->priority,
                'status' => TicketStatus::New->value,
                'assigned_to' => auth()->id(),
                'estimated_completion' => $this->estimated_completion,
                'branch_id' => auth()->user()?->branch_id,
            ]);

            // 4. Create assessment if data exists
            if (! empty($this->assessmentData)) {
                DeviceAssessment::create([
                    'device_id' => $this->device->id,
                    'ticket_id' => $this->ticket->id,
                    'type' => AssessmentType::CheckIn,
                    'assessment_data' => $this->assessmentData,
                    'assessed_by' => auth()->id(),
                    'assessed_at' => now(),
                ]);
            }

            DB::commit();

            session()->flash('success', 'Ticket created successfully!');
            $this->redirect(route('tickets.show', $this->ticket), navigate: true);
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Failed to create ticket: ' . $e->getMessage());
        }
    }

    public function getCustomersProperty()
    {
        return Customer::query()
            ->where('branch_id', auth()->user()?->branch_id)
            ->orderBy('name')
            ->limit(100)
            ->get();
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

    public function getCommonFaultsProperty()
    {
        return CommonFault::query()
            ->active()
            ->forDeviceCategory($this->device_type ? DeviceCategory::from($this->device_type) : null)
            ->ordered()
            ->get();
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.intake.wizard', [
            'priorities' => TicketPriority::options(),
            'deviceCategories' => DeviceCategory::options(),
        ]);
    }
}
