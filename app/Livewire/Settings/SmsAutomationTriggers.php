<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Models\SmsAutomationTrigger;
use App\Models\SmsTemplate;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class SmsAutomationTriggers extends Component
{
    use WithPagination;

    public $search = '';
    public $showCreateModal = false;
    public $editingTrigger = null;

    // Form fields
    public $name = '';
    public $description = '';
    public $trigger_event = '';
    public $trigger_conditions = [];
    public $sms_template_id = '';
    public $delay_minutes = 0;
    public $is_active = true;
    public $send_to_customer = true;
    public $send_to_staff = false;
    public $additional_recipients = [];

    // Condition form fields
    public $newConditionField = '';
    public $newConditionValue = '';

    // Additional recipient form
    public $newRecipient = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'trigger_event' => 'required|string',
        'sms_template_id' => 'required|exists:sms_templates,id',
        'delay_minutes' => 'required|integer|min:0',
        'is_active' => 'boolean',
        'send_to_customer' => 'boolean',
        'send_to_staff' => 'boolean',
    ];

    public function mount(): void
    {
        $this->authorize('manage_sms');
    }

    #[Computed]
    public function triggers()
    {
        return SmsAutomationTrigger::query()
            ->with(['smsTemplate'])
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('trigger_event', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);
    }

    #[Computed]
    public function availableTemplates()
    {
        return SmsTemplate::active()->orderBy('name')->get();
    }

    #[Computed]
    public function availableTriggerEvents()
    {
        return SmsAutomationTrigger::TRIGGER_EVENTS;
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(SmsAutomationTrigger $trigger): void
    {
        $this->editingTrigger = $trigger;
        $this->name = $trigger->name;
        $this->description = $trigger->description;
        $this->trigger_event = $trigger->trigger_event;
        $this->trigger_conditions = $trigger->trigger_conditions ?? [];
        $this->sms_template_id = $trigger->sms_template_id;
        $this->delay_minutes = $trigger->delay_minutes;
        $this->is_active = $trigger->is_active;
        $this->send_to_customer = $trigger->send_to_customer;
        $this->send_to_staff = $trigger->send_to_staff;
        $this->additional_recipients = $trigger->additional_recipients ?? [];
        $this->showCreateModal = true;
    }

    public function save(): void
    {
        if ($this->editingTrigger) {
            $this->update();
        } else {
            $this->create();
        }
    }

    protected function create(): void
    {
        $this->validate();

        SmsAutomationTrigger::create([
            'name' => $this->name,
            'description' => $this->description,
            'trigger_event' => $this->trigger_event,
            'trigger_conditions' => $this->trigger_conditions,
            'sms_template_id' => $this->sms_template_id,
            'delay_minutes' => $this->delay_minutes,
            'is_active' => $this->is_active,
            'send_to_customer' => $this->send_to_customer,
            'send_to_staff' => $this->send_to_staff,
            'additional_recipients' => $this->additional_recipients,
            'created_by' => auth()->id(),
        ]);

        $this->resetForm();
        $this->showCreateModal = false;
        $this->dispatch('trigger-created');
    }

    protected function update(): void
    {
        $this->validate();

        $this->editingTrigger->update([
            'name' => $this->name,
            'description' => $this->description,
            'trigger_event' => $this->trigger_event,
            'trigger_conditions' => $this->trigger_conditions,
            'sms_template_id' => $this->sms_template_id,
            'delay_minutes' => $this->delay_minutes,
            'is_active' => $this->is_active,
            'send_to_customer' => $this->send_to_customer,
            'send_to_staff' => $this->send_to_staff,
            'additional_recipients' => $this->additional_recipients,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;
        $this->dispatch('trigger-updated');
    }

    public function deleteTrigger(SmsAutomationTrigger $trigger): void
    {
        $trigger->delete();
        $this->dispatch('trigger-deleted');
    }

    public function toggleStatus(SmsAutomationTrigger $trigger): void
    {
        $trigger->update(['is_active' => !$trigger->is_active]);
        $this->dispatch('trigger-status-changed');
    }

    public function addCondition(): void
    {
        if (empty($this->newConditionField) || empty($this->newConditionValue)) {
            return;
        }

        $this->trigger_conditions[$this->newConditionField] = $this->newConditionValue;
        $this->newConditionField = '';
        $this->newConditionValue = '';
    }

    public function removeCondition(string $field): void
    {
        unset($this->trigger_conditions[$field]);
    }

    public function addRecipient(): void
    {
        if (empty($this->newRecipient)) {
            return;
        }

        // Simple phone number validation
        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $this->newRecipient)) {
            $this->addError('newRecipient', 'Please enter a valid phone number.');
            return;
        }

        if (!in_array($this->newRecipient, $this->additional_recipients)) {
            $this->additional_recipients[] = $this->newRecipient;
        }

        $this->newRecipient = '';
        $this->resetErrorBag('newRecipient');
    }

    public function removeRecipient(string $recipient): void
    {
        $this->additional_recipients = array_values(array_filter($this->additional_recipients, fn($r) => $r !== $recipient));
    }

    public function resetForm(): void
    {
        $this->editingTrigger = null;
        $this->name = '';
        $this->description = '';
        $this->trigger_event = '';
        $this->trigger_conditions = [];
        $this->sms_template_id = '';
        $this->delay_minutes = 0;
        $this->is_active = true;
        $this->send_to_customer = true;
        $this->send_to_staff = false;
        $this->additional_recipients = [];
        $this->newConditionField = '';
        $this->newConditionValue = '';
        $this->newRecipient = '';
        $this->resetValidation();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function getDelayDisplayProperty(): string
    {
        if ($this->delay_minutes === 0) {
            return 'Immediately';
        }

        if ($this->delay_minutes < 60) {
            return $this->delay_minutes . ' minute' . ($this->delay_minutes === 1 ? '' : 's');
        }

        $hours = intval($this->delay_minutes / 60);
        $minutes = $this->delay_minutes % 60;

        $display = $hours . ' hour' . ($hours === 1 ? '' : 's');
        if ($minutes > 0) {
            $display .= ' ' . $minutes . ' minute' . ($minutes === 1 ? '' : 's');
        }

        return $display;
    }

    public function render()
    {
        return view('livewire.settings.sms-automation-triggers');
    }
}
