<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\TourService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        \App\Models\Ticket::observe(\App\Observers\TicketObserver::class);

        // Register Blade directives for staff permissions
        $this->registerBladeDirectives();

        // Automatically eager load relationships
        Model::automaticallyEagerLoadRelationships();

        // In production, merely log lazy loading violations.
        if ($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation): void {
                $class = get_class($model);

                info("Attempted to lazy load [{$relation}] on model [{$class}].");
            });
        }

        // Set default password rule
        Password::defaults(function () {
            $rule = Password::min(8);

            return $this->app
                ->isProduction()
                ? $rule->mixedCase()->letters()->numbers()->symbols()->uncompromised()
                : $rule;
        });
    }

    /**
     * Register custom Blade directives for staff permissions.
     */
    protected function registerBladeDirectives(): void
    {
        // @canStaff('permission') ... @endcanStaff
        Blade::if('canStaff', function (string $permission): bool {
            return auth()->check() && auth()->user()->hasStaffPermission($permission);
        });

        // @hasStaffPermission('permission') ... @endhasStaffPermission
        Blade::if('hasStaffPermission', function (string $permission): bool {
            return auth()->check() && auth()->user()->hasStaffPermission($permission);
        });

        // @hasAnyStaffPermission(['perm1', 'perm2']) ... @endhasAnyStaffPermission
        Blade::if('hasAnyStaffPermission', function (array $permissions): bool {
            return auth()->check() && auth()->user()->hasAnyStaffPermission($permissions);
        });

        // @hasAllStaffPermissions(['perm1', 'perm2']) ... @endhasAllStaffPermissions
        Blade::if('hasAllStaffPermissions', function (array $permissions): bool {
            return auth()->check() && auth()->user()->hasAllStaffPermissions($permissions);
        });
    }
}
