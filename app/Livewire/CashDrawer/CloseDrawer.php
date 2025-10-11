<?php

declare(strict_types=1);

namespace App\Livewire\CashDrawer;

use App\Enums\CashDrawerStatus;
use App\Enums\CashTransactionType;
use App\Models\CashDrawerSession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CloseDrawer extends Component
{
    use AuthorizesRequests;

    public CashDrawerSession $session;

    #[Validate('required|numeric|min:0')]
    public string $actual_balance = '0.00';

    #[Validate('nullable|string|max:500')]
    public string $closing_notes = '';

    public function mount(): void
    {
        $this->session = CashDrawerSession::where('status', 'open')->firstOrFail();
        $this->authorize('close', $this->session);

        // Pre-fill with expected balance as a suggestion
        $this->actual_balance = number_format($this->session->calculateExpectedBalance(), 2, '.', '');
    }

    public function close(): void
    {
        $this->authorize('close', $this->session);

        $this->validate();

        $expectedBalance = $this->session->calculateExpectedBalance();
        $actualBalance = (float) $this->actual_balance;
        $discrepancy = $actualBalance - $expectedBalance;

        $this->session->update([
            'closed_by' => auth()->id(),
            'expected_balance' => $expectedBalance,
            'actual_balance' => $actualBalance,
            'discrepancy' => $discrepancy,
            'status' => CashDrawerStatus::Closed,
            'closing_notes' => $this->closing_notes ?: null,
            'closed_at' => now(),
        ]);

        // Record closing transaction
        $this->session->transactions()->create([
            'user_id' => auth()->id(),
            'type' => CashTransactionType::Closing,
            'amount' => $actualBalance,
            'reason' => 'Cash drawer closed',
            'notes' => $this->closing_notes ?: null,
            'transaction_date' => now(),
        ]);

        $this->dispatch('cash-drawer-closed');
        $this->dispatch('notify', message: 'Cash drawer closed successfully', type: 'success');

        $this->redirect(route('cash-drawer.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.cash-drawer.close-drawer', [
            'expectedBalance' => $this->session->calculateExpectedBalance(),
            'discrepancy' => (float) $this->actual_balance - $this->session->calculateExpectedBalance(),
        ]);
    }
}
