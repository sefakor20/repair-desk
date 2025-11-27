<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStaffPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  $permission  The permission to check
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is authenticated
        if (! $request->user()) {
            abort(403, 'You must be logged in to access this resource.');
        }

        $user = $request->user();

        // Admin and Manager users bypass staff permission checks (legacy behavior)
        if (in_array($user->role, [UserRole::Admin, UserRole::Manager])) {
            return $next($request);
        }

        // Check if user has the required staff permission
        if (! $user->hasStaffPermission($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
