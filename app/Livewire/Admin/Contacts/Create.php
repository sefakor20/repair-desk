<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Contacts;

use App\Models\Contact;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $company = '';
    public string $position = '';
    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('create', Contact::class);
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:contacts,email',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        Contact::create($validated);

        session()->flash('success', 'Contact created successfully.');

        $this->redirectRoute('admin.contacts.index');
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.admin.contacts.create');
    }
}
