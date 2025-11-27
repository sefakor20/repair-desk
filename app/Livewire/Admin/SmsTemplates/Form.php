<?php

declare(strict_types=1);

namespace App\Livewire\Admin\SmsTemplates;

use App\Models\SmsTemplate;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?SmsTemplate $template = null;

    public string $name = '';

    public string $key = '';

    public string $message = '';

    public string $description = '';

    public bool $is_active = true;

    public bool $isEditing = false;

    public function mount(?int $templateId = null): void
    {
        Gate::authorize('create', SmsTemplate::class);

        if ($templateId) {
            $this->template = SmsTemplate::findOrFail($templateId);
            $this->isEditing = true;
            $this->name = $this->template->name;
            $this->key = $this->template->key;
            $this->message = $this->template->message;
            $this->description = $this->template->description ?? '';
            $this->is_active = $this->template->is_active;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:sms_templates,key,' . ($this->template?->id ?? 'NULL'),
            'message' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($this->isEditing) {
            $this->template->update($validated);
        } else {
            SmsTemplate::create($validated);
        }

        session()->flash('success', $this->isEditing ? 'Template updated successfully.' : 'Template created successfully.');

        $this->redirect(route('admin.sms-templates.index'), navigate: true);
    }

    public function render(): View
    {
        // Extract variables from the message
        $detectedVariables = [];
        if ($this->message) {
            preg_match_all('/\{\{(\w+)\}\}/', $this->message, $matches);
            $detectedVariables = $matches[1] ?? [];
        }

        return view('livewire.admin.sms-templates.form', [
            'detectedVariables' => $detectedVariables,
        ]);
    }
}
