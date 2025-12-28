<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class CommandPalette extends Component
{
    public bool $isOpen = false;

    public string $query = '';

    public array $commands = [];

    public int $selectedIndex = 0;

    public function mount(): void
    {
        $this->loadCommands();
    }

    #[On('toggle-command-palette')]
    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
        $this->query = '';
        $this->selectedIndex = 0;

        if ($this->isOpen) {
            $this->loadCommands();
        }
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
        $this->selectedIndex = 0;
    }

    public function updatedQuery(): void
    {
        $this->selectedIndex = 0;
        $this->loadCommands();
    }

    public function selectNext(): void
    {
        $filtered = $this->getFilteredCommands();
        $this->selectedIndex = ($this->selectedIndex + 1) % count($filtered);
    }

    public function selectPrevious(): void
    {
        $filtered = $this->getFilteredCommands();
        $this->selectedIndex = ($this->selectedIndex - 1 + count($filtered)) % count($filtered);
    }

    public function executeSelected(): void
    {
        $filtered = $this->getFilteredCommands();

        if (isset($filtered[$this->selectedIndex])) {
            $command = $filtered[$this->selectedIndex];
            $this->execute($command);
        }
    }

    public function executeByIndex(int $index): void
    {
        $filtered = $this->getFilteredCommands();

        if (isset($filtered[$index])) {
            $this->execute($filtered[$index]);
        }
    }

    private function execute(array $command): void
    {
        $this->close();

        if (isset($command['url'])) {
            $this->redirect($command['url'], navigate: true);
        } elseif (isset($command['action'])) {
            $this->dispatch($command['action']);
        }
    }

    private function loadCommands(): void
    {
        $user = auth()->user();

        $this->commands = [
            // Navigation
            [
                'title' => 'Dashboard',
                'description' => 'Go to dashboard',
                'icon' => 'home',
                'url' => route('dashboard'),
                'keywords' => ['dashboard', 'home', 'overview'],
            ],
            [
                'title' => 'Customers',
                'description' => 'View all customers',
                'icon' => 'user-group',
                'url' => route('customers.index'),
                'keywords' => ['customers', 'clients', 'users'],
            ],
            [
                'title' => 'Devices',
                'description' => 'View all devices',
                'icon' => 'device-phone-mobile',
                'url' => route('devices.index'),
                'keywords' => ['devices', 'phones', 'tablets'],
            ],
            [
                'title' => 'Tickets',
                'description' => 'View all repair tickets',
                'icon' => 'wrench-screwdriver',
                'url' => route('tickets.index'),
                'keywords' => ['tickets', 'repairs', 'jobs'],
            ],
            [
                'title' => 'Inventory',
                'description' => 'View inventory items',
                'icon' => 'cube',
                'url' => route('inventory.index'),
                'keywords' => ['inventory', 'stock', 'parts', 'products'],
            ],
            [
                'title' => 'Invoices',
                'description' => 'View all invoices',
                'icon' => 'document-text',
                'url' => route('invoices.index'),
                'keywords' => ['invoices', 'bills', 'payments'],
            ],
            [
                'title' => 'Point of Sale',
                'description' => 'Go to POS',
                'icon' => 'shopping-cart',
                'url' => route('pos.index'),
                'keywords' => ['pos', 'sales', 'checkout', 'sell'],
            ],

            // Create actions
            [
                'title' => 'New Customer',
                'description' => 'Create a new customer',
                'icon' => 'plus',
                'url' => route('customers.create'),
                'keywords' => ['create', 'new', 'customer', 'add'],
            ],
            [
                'title' => 'New Device',
                'description' => 'Register a new device',
                'icon' => 'plus',
                'url' => route('devices.create'),
                'keywords' => ['create', 'new', 'device', 'register'],
            ],
            [
                'title' => 'New Ticket',
                'description' => 'Create a repair ticket',
                'icon' => 'plus',
                'url' => route('tickets.create'),
                'keywords' => ['create', 'new', 'ticket', 'repair'],
            ],
            [
                'title' => 'New Invoice',
                'description' => 'Create an invoice',
                'icon' => 'plus',
                'url' => route('invoices.create'),
                'keywords' => ['create', 'new', 'invoice', 'bill'],
            ],
            [
                'title' => 'New Sale',
                'description' => 'Start a new sale',
                'icon' => 'plus',
                'url' => route('pos.create'),
                'keywords' => ['create', 'new', 'sale', 'pos', 'checkout'],
            ],
            [
                'title' => 'Add Inventory Item',
                'description' => 'Add a new inventory item',
                'icon' => 'plus',
                'url' => route('inventory.create'),
                'keywords' => ['create', 'new', 'inventory', 'stock', 'add'],
            ],
        ];

        // Add admin-only commands
        if ($user->can('viewReports', \App\Models\User::class)) {
            $this->commands[] = [
                'title' => 'Reports',
                'description' => 'View reports and analytics',
                'icon' => 'chart-bar',
                'url' => route('reports.index'),
                'keywords' => ['reports', 'analytics', 'stats'],
            ];
        }

        if ($user->can('viewAny', \App\Models\User::class)) {
            $this->commands[] = [
                'title' => 'Users',
                'description' => 'Manage users',
                'icon' => 'users',
                'url' => route('users.index'),
                'keywords' => ['users', 'staff', 'team'],
            ];

            $this->commands[] = [
                'title' => 'New User',
                'description' => 'Create a new user',
                'icon' => 'plus',
                'url' => route('users.create'),
                'keywords' => ['create', 'new', 'user', 'staff'],
            ];
        }
    }

    private function getFilteredCommands(): array
    {
        if ($this->query === '' || $this->query === '0') {
            return $this->commands;
        }

        $query = mb_strtolower($this->query);

        return array_filter($this->commands, function (array $command) use ($query): bool {
            // Search in title
            if (str_contains(mb_strtolower($command['title']), $query)) {
                return true;
            }

            // Search in description
            if (str_contains(mb_strtolower($command['description']), $query)) {
                return true;
            }

            // Search in keywords
            foreach ($command['keywords'] as $keyword) {
                if (str_contains(mb_strtolower($keyword), $query)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.command-palette', [
            'filteredCommands' => $this->getFilteredCommands(),
        ]);
    }
}
