<?php

declare(strict_types=1);

namespace App\Livewire\Pos;

use App\Models\{PosSale, ShopSettings};
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.print')]
class Receipt extends Component
{
    public PosSale $sale;

    public function mount(PosSale $sale): void
    {
        $this->sale = $sale->load(['customer', 'soldBy', 'items.inventoryItem']);
    }

    public function render(): View
    {
        $settings = ShopSettings::getInstance();

        return view('livewire.pos.receipt', [
            'settings' => $settings,
        ]);
    }
}
