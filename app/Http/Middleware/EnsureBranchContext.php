<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\BranchContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establishes branch context for the current request.
 * Ensures all operations are scoped to the user's assigned branch.
 */
class EnsureBranchContext
{
    public function __construct(private readonly BranchContextService $branchContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated, set their branch context
        if ($request->user()) {
            $branch = $request->user()->branch;

            if ($branch) {
                $this->branchContext->setCurrentBranch($branch);
            }
        }

        return $next($request);
    }
}
