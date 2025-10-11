<?php

declare(strict_types=1);

namespace App\Livewire\CashDrawer;

use App\Models\CashDrawerSession;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Cash Drawer')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function render(): View
    {
        $sessions = CashDrawerSession::query()
            ->with(['openedBy', 'closedBy', 'transactions'])
            ->when($this->search, fn($query) => $query->where('id', 'like', "%{$this->search}%")
                ->orWhereHas('openedBy', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->latest('opened_at')
            ->paginate(15);

        $activeSession = CashDrawerSession::where('status', 'open')->first();

        return view('livewire.cash-drawer.index', [
            'sessions' => $sessions,
            'activeSession' => $activeSession,
        ]);
    }
}
