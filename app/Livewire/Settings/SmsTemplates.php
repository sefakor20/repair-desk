<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Models\SmsTemplate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class SmsTemplates extends Component
{
    use WithPagination;

    public $search = '';
    public $showCreateModal = false;
    public $editingTemplate;

    // Form fields
    public $name = '';
    public $key = '';
    public $message = '';
    public $description = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'key' => 'required|string|max:255|unique:sms_templates,key',
        'message' => 'required|string',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function mount(): void
    {
        $this->authorize('manage_sms');
    }

    #[Computed]
    public function templates()
    {
        return SmsTemplate::query()
            ->when($this->search, function ($query): void {
                $query->where(function ($subQuery): void {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('key', 'like', '%' . $this->search . '%')
                        ->orWhere('message', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(SmsTemplate $template): void
    {
        $this->editingTemplate = $template;
        $this->name = $template->name;
        $this->key = $template->key;
        $this->message = $template->message;
        $this->description = $template->description;
        $this->is_active = $template->is_active;
        $this->showCreateModal = true;
    }

    public function save(): void
    {
        if ($this->editingTemplate) {
            $this->update();
        } else {
            $this->create();
        }
    }

    protected function create(): void
    {
        $this->validate();

        SmsTemplate::create([
            'name' => $this->name,
            'key' => $this->key,
            'message' => $this->message,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;
        $this->dispatch('template-created');
    }

    protected function update(): void
    {
        $this->rules['key'] = 'required|string|max:255|unique:sms_templates,key,' . $this->editingTemplate->id;
        $this->validate();

        $this->editingTemplate->update([
            'name' => $this->name,
            'key' => $this->key,
            'message' => $this->message,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;
        $this->dispatch('template-updated');
    }

    public function deleteTemplate(SmsTemplate $template): void
    {
        // Check if template is being used by automation triggers
        if ($template->automationTriggers()->exists()) {
            $this->dispatch('error', 'This template cannot be deleted because it is being used by automation triggers.');
            return;
        }

        $template->delete();
        $this->dispatch('template-deleted');
    }

    public function toggleStatus(SmsTemplate $template): void
    {
        $template->update(['is_active' => !$template->is_active]);
        $this->dispatch('template-status-changed');
    }

    public function previewTemplate(SmsTemplate $template): void
    {
        $sampleVariables = $this->generateSampleVariables($template);
        $previewMessage = $template->render($sampleVariables);

        $this->dispatch('show-preview', [
            'template' => $template->name,
            'message' => $previewMessage,
            'variables' => $sampleVariables,
        ]);
    }

    protected function generateSampleVariables(SmsTemplate $template): array
    {
        $variables = $template->extractVariables();
        $sampleData = [];

        foreach ($variables as $variable) {
            $sampleData[$variable] = match ($variable) {
                'customer_name' => 'John Doe',
                'customer_phone' => '+233123456789',
                'ticket_number' => 'TKT-2024-001',
                'device' => 'iPhone 12 Pro',
                'status' => 'In Progress',
                'branch_name' => session('current_branch')?->name ?? 'Main Branch',
                'branch_phone' => session('current_branch')?->phone ?? '+233123456789',
                'current_date' => now()->format('M j, Y'),
                'current_time' => now()->format('g:i A'),
                'amount' => format_currency(150.00),
                'date' => now()->addDays(3)->format('M d, Y'),
                'expiry_date' => now()->addDays(30)->format('M d, Y'),
                'appointment_date' => now()->addDays(2)->format('M d, Y g:i A'),
                default => '[' . mb_strtoupper($variable) . ']',
            };
        }

        return $sampleData;
    }

    protected function resetForm(): void
    {
        $this->editingTemplate = null;
        $this->name = '';
        $this->key = '';
        $this->message = '';
        $this->description = '';
        $this->is_active = true;
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.settings.sms-templates');
    }
}
