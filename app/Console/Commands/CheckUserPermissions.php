<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class CheckUserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:check-permissions {user_id?} {--make-admin} {--list}';

    /**
     * The console command description.
     */
    protected $description = 'Check and manage user permissions for SMS settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('list')) {
            $this->listUsers();
            return 0;
        }

        $userId = $this->argument('user_id');

        if (!$userId) {
            $this->error('Please provide a user ID or use --list to see all users');
            $this->info('Usage: php artisan user:check-permissions {user_id} [--make-admin]');
            return 1;
        }

        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }

        $this->info("User: {$user->name} ({$user->email})");
        $this->info("Current Role: " . ($user->role ? $user->role->value : 'No role set'));

        // Check permissions
        $hasManageSettings = $user->hasStaffPermission('manage_settings');
        $this->info("Has manage_settings permission: " . ($hasManageSettings ? 'Yes' : 'No'));
        $this->info("Can access settings: " . ($user->role === UserRole::Admin || $hasManageSettings ? 'Yes' : 'No'));

        if ($this->option('make-admin')) {
            $user->role = UserRole::Admin;
            $user->save();
            $this->info("✅ User role updated to Admin");
        } elseif ($user->role !== UserRole::Admin) {
            $this->info("💡 To make this user an admin, run:");
            $this->info("php artisan user:check-permissions {$userId} --make-admin");
        }

        return 0;
    }

    protected function listUsers(): void
    {
        $users = User::orderBy('name')->get();

        $this->info("All Users:");
        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Has manage_settings'],
            $users->map(function (User $user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role ? $user->role->value : 'No role',
                    $user->hasStaffPermission('manage_settings') ? 'Yes' : 'No',
                ];
            }),
        );
    }
}
