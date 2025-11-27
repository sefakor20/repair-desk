<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\{InventoryItem, User};
use App\Notifications\LowStockAlert;
use Illuminate\Console\Command;

class CheckLowStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check inventory for low stock items and notify admins';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for low stock items...');

        $lowStockItems = InventoryItem::whereRaw('quantity <= reorder_level')
            ->where('status', 'active')
            ->get();

        if ($lowStockItems->isEmpty()) {
            $this->info('No low stock items found.');

            return Command::SUCCESS;
        }

        $this->info('Found ' . $lowStockItems->count() . ' low stock items.');

        // Notify all admin users
        $admins = User::where('role', UserRole::Admin)->get();

        foreach ($admins as $admin) {
            $admin->notify(new LowStockAlert($lowStockItems));
            $this->info('Notified: ' . $admin->email);
        }

        $this->info('Low stock notifications sent successfully!');

        return Command::SUCCESS;
    }
}
