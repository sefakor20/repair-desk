<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Referrals;

use App\Models\{Customer, Referral};
use Illuminate\View\View;
use Livewire\{Attributes\Validate, Component};
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public Customer $customer;

    public string $referralCode = '';

    #[Validate('required|email|max:255')]
    public string $friend_email = '';

    #[Validate('nullable|string|max:255')]
    public string $friend_name = '';

    public bool $showInviteModal = false;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        // Generate referral code if doesn't exist
        $this->referralCode = $customer->referral_code ?? $customer->generateReferralCode();
    }

    public function openInviteModal(): void
    {
        $this->showInviteModal = true;
        $this->friend_email = '';
        $this->friend_name = '';
        $this->resetValidation();
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
    }

    public function sendInvite(): void
    {
        $validated = $this->validate();

        // Check if already referred
        if (Referral::where('referrer_id', $this->customer->id)
            ->where('referred_email', $validated['friend_email'])
            ->exists()
        ) {
            $this->addError('friend_email', 'You have already sent an invitation to this email.');
            return;
        }

        // Create referral
        Referral::create([
            'referrer_id' => $this->customer->id,
            'referral_code' => $this->referralCode,
            'referred_email' => $validated['friend_email'],
            'referred_name' => $validated['friend_name'] ?? null,
            'status' => 'pending',
            'expires_at' => now()->addDays(30),
        ]);

        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Invitation sent successfully!',
        );

        $this->closeInviteModal();
    }

    public function copyCode(): void
    {
        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Referral code copied to clipboard!',
        );
    }

    public function render(): View
    {
        $referrals = $this->customer->referralsMade()
            ->latest()
            ->with('referred')
            ->paginate(10);

        $stats = [
            'total' => $this->customer->referralsMade()->count(),
            'completed' => $this->customer->referralsMade()->completed()->count(),
            'pending' => $this->customer->referralsMade()->pending()->count(),
            'points_earned' => $this->customer->referralsMade()->completed()->sum('points_awarded'),
        ];

        return view('livewire.portal.referrals.index', [
            'referrals' => $referrals,
            'stats' => $stats,
        ]);
    }
}
