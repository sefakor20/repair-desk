<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Contacts;

use App\Models\Contact;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public Contact $contact;
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $company = '';
    public string $position = '';
    public bool $is_active = true;

    public function mount(Contact $contact): void
    {
        $this->authorize('update', $contact);

        $this->contact = $contact;
        $this->first_name = $contact->first_name;
        $this->last_name = $contact->last_name ?? '';
        $this->email = $contact->email ?? '';
        $this->phone = $contact->phone ?? '';
        $this->company = $contact->company ?? '';
        $this->position = $contact->position ?? '';
        $this->is_active = $contact->is_active;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:contacts,email,' . $this->contact->id,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->contact->update($validated);

        session()->flash('success', 'Contact updated successfully.');

        $this->redirectRoute('admin.contacts.index');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.contacts.edit');
    }
}
