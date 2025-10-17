<?php

namespace App\Livewire\Branches;

use Livewire\Component;


use App\Models\Branch;
use Illuminate\View\View;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'Edit Branch'])]
class Edit extends Component
{
    public Branch $branch;
    public array $form = [];

    public function mount(Branch $branch): void
    {
        $this->branch = $branch;
        $this->form = $branch->only([
            'name',
            'code',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'phone',
            'email',
            'is_active',
            'is_main',
            'notes'
        ]);
    }

    public function rules(): array
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.code' => 'required|string|max:10|unique:branches,code,' . $this->branch->id . ',id',
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
        $this->authorize('update', $this->branch);

        $validated = $this->validate();

        $this->branch->update($validated['form']);

        session()->flash('success', 'Branch updated successfully.');

        $this->redirect(route('branches.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.branches.edit');
    }
}
