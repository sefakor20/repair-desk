<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class KeyboardShortcutsHelp extends Component
{
    public bool $isOpen = false;

    #[On('toggle-shortcuts-help')]
    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function render()
    {
        $shortcuts = [
            'General' => [
                ['keys' => ['Ctrl', 'K'], 'description' => 'Open command palette'],
                ['keys' => ['/'], 'description' => 'Focus search'],
                ['keys' => ['?'], 'description' => 'Show keyboard shortcuts'],
                ['keys' => ['Esc'], 'description' => 'Close modals/palettes'],
            ],
            'Navigation' => [
                ['keys' => ['G', 'D'], 'description' => 'Go to Dashboard'],
                ['keys' => ['G', 'C'], 'description' => 'Go to Customers'],
                ['keys' => ['G', 'T'], 'description' => 'Go to Tickets'],
                ['keys' => ['G', 'I'], 'description' => 'Go to Inventory'],
                ['keys' => ['G', 'V'], 'description' => 'Go to Invoices'],
                ['keys' => ['G', 'P'], 'description' => 'Go to POS'],
            ],
            'Actions' => [
                ['keys' => ['N'], 'description' => 'Create new item (context-aware)'],
                ['keys' => ['E'], 'description' => 'Edit current item'],
            ],
        ];

        return view('livewire.keyboard-shortcuts-help', [
            'shortcuts' => $shortcuts,
        ]);
    }
}
