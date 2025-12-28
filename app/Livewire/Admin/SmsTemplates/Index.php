<?php

declare(strict_types=1);

namespace App\Livewire\Admin\SmsTemplates;

use App\Models\SmsTemplate;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', SmsTemplate::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $template = SmsTemplate::findOrFail($id);
        $template->is_active = ! $template->is_active;
        $template->save();

        $this->dispatch('template-updated');
    }

    public function delete(int $id): void
    {
        $template = SmsTemplate::findOrFail($id);
        $template->delete();

        $this->dispatch('template-deleted');
    }

    public function render(): View
    {
        $templates = SmsTemplate::query()
            ->when($this->search, function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('key', 'like', "%{$this->search}%")
                    ->orWhere('message', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(20);

        return view('livewire.admin.sms-templates.index', [
            'templates' => $templates,
        ]);
    }
}
