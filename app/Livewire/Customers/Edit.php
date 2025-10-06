<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Edit Customer'])]
class Edit extends Component
{
    public Customer $customer;

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

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
        $this->authorize('update', $this->customer);

        $this->form = [
            'first_name' => $this->customer->first_name,
            'last_name' => $this->customer->last_name,
            'email' => $this->customer->email,
            'phone' => $this->customer->phone,
            'address' => $this->customer->address,
            'notes' => $this->customer->notes,
            'tags' => $this->customer->tags ?? [],
        ];
    }

    public function rules(): array
    {
        return [
            'form.first_name' => 'required|string|max:255',
            'form.last_name' => 'required|string|max:255',
            'form.email' => 'required|email|max:255|unique:customers,email,' . $this->customer->id,
            'form.phone' => 'required|string|max:255',
            'form.address' => 'nullable|string',
            'form.notes' => 'nullable|string',
            'form.tags' => 'nullable|array',
        ];
    }

    public function addTag(): void
    {
        $tag = trim($this->tagsInput);

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
        $this->authorize('update', $this->customer);

        $validated = $this->validate();

        $this->customer->update($validated['form']);

        session()->flash('success', 'Customer updated successfully.');

        $this->redirect(route('customers.show', $this->customer), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.customers.edit', [
            'customer' => $this->customer,
        ]);
    }
}
