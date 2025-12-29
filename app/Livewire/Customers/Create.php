<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'New Customer'])]
class Create extends Component
{
    public array $form = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'notes' => '',
        'tags' => [],
    ];

    public string $tagsInput = '';

    public function rules(): array
    {
        return [
            'form.first_name' => 'required|string|max:255',
            'form.last_name' => 'required|string|max:255',
            'form.email' => 'required|email|max:255|unique:customers,email',
            'form.phone' => 'required|string|max:255',
            'form.address' => 'nullable|string',
            'form.notes' => 'nullable|string',
            'form.tags' => 'nullable|array',
        ];
    }

    public function addTag(): void
    {
        $tag = mb_trim($this->tagsInput);

        if ($tag && !in_array($tag, $this->form['tags'])) {
            $this->form['tags'][] = $tag;
            $this->tagsInput = '';
        }
    }

    public function removeTag(int $index): void
    {
        array_splice($this->form['tags'], $index, 1);
    }

    public function save(): void
    {
        $this->authorize('create', Customer::class);

        $validated = $this->validate();

        // Automatically set the branch_id to the current user's branch
        $validated['form']['branch_id'] = auth()->user()->branch_id;

        Customer::create($validated['form']);

        session()->flash('success', 'Customer created successfully.');

        $this->redirect(route('customers.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.customers.create');
    }
}
