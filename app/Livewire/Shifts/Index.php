<?php

declare(strict_types=1);

namespace App\Livewire\Shifts;

use App\Models\Shift;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Shifts')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function render(): View
    {
        $shifts = Shift::query()
            ->with(['openedBy', 'closedBy', 'sales'])
            ->when($this->search, fn($query) => $query->where('shift_name', 'like', "%{$this->search}%")
                ->orWhereHas('openedBy', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->latest('started_at')
            ->paginate(15);

        $activeShift = Shift::where('opened_by', auth()->id())
            ->where('status', 'open')
            ->first();

        return view('livewire.shifts.index', [
            'shifts' => $shifts,
            'activeShift' => $activeShift,
        ]);
    }
}
