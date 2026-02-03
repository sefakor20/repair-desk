<?php

declare(strict_types=1);

namespace App\Livewire\CashDrawer;

use Livewire\Component;
use App\Enums\CashDrawerStatus;
use Livewire\Attributes\Layout;
use App\Models\CashDrawerSession;
use Livewire\Attributes\Validate;
use App\Enums\CashTransactionType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('components.layouts.app')]
class OpenDrawer extends Component
{
    use AuthorizesRequests;

    #[Validate('required|numeric|min:0')]
    public string $opening_balance = '0.00';

    #[Validate('nullable|string|max:500')]
    public string $opening_notes = '';

    public function mount(): void
    {
        $this->authorize('open', CashDrawerSession::class);
    }

    public function open(): void
    {
        $this->authorize('open', CashDrawerSession::class);

        $this->validate();

        $session = CashDrawerSession::create([
            'opened_by' => auth()->id(),
            'opening_balance' => $this->opening_balance,
            'cash_sales' => 0,
            'cash_in' => 0,
            'cash_out' => 0,
            'status' => CashDrawerStatus::Open,
            'opening_notes' => $this->opening_notes ?: null,
            'opened_at' => now(),
        ]);

        // Record opening transaction
        $session->transactions()->create([
            'user_id' => auth()->id(),
            'type' => CashTransactionType::Opening,
            'amount' => $this->opening_balance,
            'reason' => 'Cash drawer opened',
            'notes' => $this->opening_notes ?: null,
            'transaction_date' => now(),
        ]);

        $this->dispatch('cash-drawer-opened');
        $this->dispatch('notify', message: 'Cash drawer opened successfully', type: 'success');

        $this->redirect(route('cash-drawer.index'), navigate: true);
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.cash-drawer.open-drawer');
    }
}
