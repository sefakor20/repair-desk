<?php

declare(strict_types=1);

namespace App\Livewire\Shifts;

use App\Models\Shift;
use Livewire\Component;
use App\Enums\ShiftStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('components.layouts.app')]
class OpenShift extends Component
{
    use AuthorizesRequests;

    #[Validate('required|string|max:255')]
    public string $shift_name = '';

    #[Validate('nullable|string|max:500')]
    public string $opening_notes = '';

    public function mount(): void
    {
        $this->authorize('open', Shift::class);

        // Suggest shift name based on time of day
        $hour = now()->hour;
        if ($hour < 12) {
            $this->shift_name = 'Morning Shift';
        } elseif ($hour < 17) {
            $this->shift_name = 'Afternoon Shift';
        } elseif ($hour < 21) {
            $this->shift_name = 'Evening Shift';
        } else {
            $this->shift_name = 'Night Shift';
        }
    }

    public function open(): void
    {
        $this->authorize('open', Shift::class);

        $this->validate();

        Shift::create([
            'shift_name' => $this->shift_name,
            'opened_by' => auth()->id(),
            'status' => ShiftStatus::Open,
            'total_sales' => 0,
            'sales_count' => 0,
            'cash_sales' => 0,
            'card_sales' => 0,
            'mobile_money_sales' => 0,
            'bank_transfer_sales' => 0,
            'opening_notes' => $this->opening_notes ?: null,
            'started_at' => now(),
        ]);

        $this->dispatch('shift-opened');
        $this->dispatch('notify', message: 'Shift opened successfully', type: 'success');

        $this->redirect(route('shifts.index'), navigate: true);
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.shifts.open-shift');
    }
}
