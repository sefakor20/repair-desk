<?php

declare(strict_types=1);

namespace App\Livewire\Branches;

use Livewire\Component;
use App\Models\Branch;
use Illuminate\View\View;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'Branch Details'])]
class Show extends Component
{
    public Branch $branch;

    public function mount(Branch $branch): void
    {
        $this->branch = $branch->loadCount(['users', 'tickets', 'inventoryItems', 'posSales']);
    }

    public function render(): View
    {
        return view('livewire.branches.show', [
            'branch' => $this->branch,
        ]);
    }
}
