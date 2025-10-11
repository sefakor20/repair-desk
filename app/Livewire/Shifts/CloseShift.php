<?php

declare(strict_types=1);

namespace App\Livewire\Shifts;

use App\Enums\ShiftStatus;
use App\Models\Shift;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CloseShift extends Component
{
    use AuthorizesRequests;

    public Shift $shift;

    #[Validate('nullable|string|max:500')]
    public string $closing_notes = '';

    public function mount(): void
    {
        $this->shift = Shift::where('opened_by', auth()->id())
            ->where('status', 'open')
            ->firstOrFail();

        $this->authorize('close', $this->shift);
    }

    public function close(): void
    {
        $this->authorize('close', $this->shift);

        $this->validate();

        $this->shift->update([
            'closed_by' => auth()->id(),
            'status' => ShiftStatus::Closed,
            'closing_notes' => $this->closing_notes ?: null,
            'ended_at' => now(),
        ]);

        $this->dispatch('shift-closed');
        $this->dispatch('notify', message: 'Shift closed successfully', type: 'success');

        $this->redirect(route('shifts.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.shifts.close-shift', [
            'duration' => $this->shift->started_at->diffForHumans(now(), true),
            'averageSaleAmount' => $this->shift->averageSaleAmount(),
        ]);
    }
}
