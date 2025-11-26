<?php

declare(strict_types=1);

namespace App\Livewire\Branches;

use Livewire\Component;
use App\Models\Branch;
use Illuminate\View\View;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'New Branch'])]
class Create extends Component
{
    public array $form = [
        'name' => '',
        'code' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'country' => '',
        'phone' => '',
        'email' => '',
        'is_active' => true,
        'is_main' => false,
        'notes' => '',
    ];

    public function rules(): array
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.code' => 'required|string|max:10|unique:branches,code',
            'form.address' => 'nullable|string',
            'form.city' => 'nullable|string|max:255',
            'form.state' => 'nullable|string|max:255',
            'form.zip' => 'nullable|string|max:20',
            'form.country' => 'nullable|string|max:255',
            'form.phone' => 'nullable|string|max:255',
            'form.email' => 'nullable|email|max:255',
            'form.is_active' => 'boolean',
            'form.is_main' => 'boolean',
            'form.notes' => 'nullable|string',
        ];
    }

    public function save(): void
    {
        $this->authorize('create', Branch::class);

        $validated = $this->validate();

        Branch::create($validated['form']);

        session()->flash('success', 'Branch created successfully.');

        $this->redirect(route('branches.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.branches.create');
    }
}
