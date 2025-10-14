<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Profile;

use App\Models\{Customer, PointTransfer};
use Illuminate\View\View;
use Livewire\{Attributes\Layout, Attributes\Validate, Component};
use Livewire\WithPagination;
use Exception;

#[Layout('components.layouts.portal-fullpage')]
class TransferPoints extends Component
{
    use WithPagination;

    public Customer $customer;

    #[Validate('required|email|exists:customers,email')]
    public string $recipient_email = '';

    #[Validate('required|integer|min:50|max:10000')]
    public int $points = 0;

    #[Validate('nullable|string|max:500')]
    public string $message = '';

    public bool $showTransferModal = false;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function openTransferModal(): void
    {
        $this->showTransferModal = true;
        $this->recipient_email = '';
        $this->points = 0;
        $this->message = '';
        $this->resetValidation();
    }

    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
    }

    public function transfer(): void
    {
        $validated = $this->validate();

        // Find recipient
        $recipient = Customer::where('email', $validated['recipient_email'])->first();

        // Can't transfer to self
        if ($recipient->id === $this->customer->id) {
            $this->addError('recipient_email', 'You cannot transfer points to yourself.');
            return;
        }

        // Check sufficient balance
        if ($this->customer->loyaltyAccount->total_points < $validated['points']) {
            $this->addError('points', 'Insufficient points balance.');
            return;
        }

        // Minimum transfer
        if ($validated['points'] < 50) {
            $this->addError('points', 'Minimum transfer is 50 points.');
            return;
        }

        try {
            // Create and process transfer
            $transfer = PointTransfer::create([
                'sender_id' => $this->customer->id,
                'recipient_id' => $recipient->id,
                'points' => $validated['points'],
                'message' => $validated['message'] ?? null,
                'status' => 'pending',
            ]);

            $transfer->process();

            $this->dispatch(
                'toast',
                type: 'success',
                message: "Successfully transferred {$validated['points']} points to {$recipient->full_name}!",
            );

            $this->closeTransferModal();

            // Refresh customer loyalty account
            $this->customer->refresh();
        } catch (Exception $e) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: 'Transfer failed: ' . $e->getMessage(),
            );
        }
    }

    public function render(): View
    {
        $transfers = PointTransfer::where('sender_id', $this->customer->id)
            ->orWhere('recipient_id', $this->customer->id)
            ->with(['sender', 'recipient'])
            ->latest()
            ->paginate(10);

        return view('livewire.portal.profile.transfer-points', [
            'transfers' => $transfers,
        ]);
    }
}
