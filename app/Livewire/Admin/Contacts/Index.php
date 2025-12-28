<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Contacts;

use App\Models\Contact;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Contact::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $contactId): void
    {
        $contact = Contact::findOrFail($contactId);
        $this->authorize('delete', $contact);

        $contact->delete();

        session()->flash('success', 'Contact deleted successfully.');
    }

    public function getContactsProperty()
    {
        return Contact::query()
            ->when($this->search, function ($query): void {
                $query->where(function ($q): void {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('company', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(15);
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.admin.contacts.index');
    }
}
