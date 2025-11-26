# Branch-Level Multi-Tenancy & Data Isolation

## Overview

This documentation outlines the complete implementation of branch-level multi-tenancy and data isolation in the Repair Desk application. This system ensures that:

1. **Data Isolation**: Users can only access data belonging to their assigned branch
2. **Authorization Control**: Branch-level permissions are enforced at the application level
3. **Automatic Scoping**: Queries are automatically filtered by branch without manual intervention
4. **Super Admin Access**: Super admins (admins without branch_id) can access data across all branches
5. **Secure by Default**: Security is enforced at the model level, preventing accidental data leakage

## Architecture

### Core Components

#### 1. **BranchScoped Global Scope** (`app/Traits/BranchScoped.php`)

Implements `\Illuminate\Database\Eloquent\Scope` to automatically filter queries by the user's branch.

```php
// Applied globally via model's boot method
static::addGlobalScope(new BranchScoped());
```

**Behavior:**
- If user is not authenticated: No scoping applied
- If user is super admin (role=admin, branch_id=null): No scoping applied
- Otherwise: Automatically filters queries where `branch_id = user.branch_id`

**Models with BranchScoped:**
- `App\Models\Ticket`
- `App\Models\InventoryItem`
- `App\Models\PosSale`
- `App\Models\Invoice`
- `App\Models\Payment`

#### 2. **BranchContextService** (`app/Services/BranchContextService.php`)

Centralized service for managing branch context throughout the application.

**Key Methods:**
```php
// Get current branch for authenticated user
$branch = $branchContext->getCurrentBranch();

// Get current branch ID
$branchId = $branchContext->getCurrentBranchId();

// Check access permissions
$canAccess = $branchContext->canAccessBranch($branch);

// Get all accessible branches
$branches = $branchContext->getAccessibleBranches();

// Check if branch is active
$isActive = $branchContext->isBranchActive($branchId);

// Assert access, throw if denied
$branchContext->assertCanAccessBranch($branch);

// Clear cache
$branchContext->clearCache($branchId);
```

**Caching:**
- Branches are cached for 1 hour (3600 seconds)
- Cache key format: `branch_context_{branch_id}`
- Call `clearCache()` when branch data changes

#### 3. **EnsureBranchContext Middleware** (`app/Http/Middleware/EnsureBranchContext.php`)

Executed on every request to establish the branch context.

**Flow:**
1. Checks if user is authenticated
2. Retrieves user's assigned branch relationship
3. Sets branch context if branch exists

**Registration:**
```php
// bootstrap/app.php
$middleware->web(append: [
    \App\Http\Middleware\EnsureBranchContext::class,
]);
```

#### 4. **BranchScopedPolicy** (`app/Policies/BranchScopedPolicy.php`)

Base policy class providing common authorization logic for branch-scoped resources.

**Methods:**
```php
// Check view permission
$this->canViewBranch($user, $branch);

// Check create permission
$this->canCreateInBranch($user, $branch);

// Check update permission
$this->canUpdateInBranch($user, $branch);

// Check delete permission
$this->canDeleteInBranch($user, $branch);
```

### User Model Extensions

#### Super Admin Detection
```php
// Check if user is a super admin (admin with no branch)
$user->isSuperAdmin(); // Returns: bool

// Check if user can manage a specific branch
$user->canManageBranch($branch); // Returns: bool
```

## Usage Patterns

### Automatic Query Scoping

```php
// All queries are automatically scoped to user's branch
$tickets = Ticket::all(); // Only returns tickets from user's branch

// Even with explicit conditions, scoping is applied
$tickets = Ticket::where('status', 'open')->get(); // Still scoped to branch

// Super admins see all data
// (when authenticated as super admin)
$allTickets = Ticket::all(); // Returns tickets from all branches
```

### Bypassing Global Scope

In rare cases where you need to bypass the global scope (e.g., reporting):

```php
// Remove the branch scope temporarily
$allTickets = Ticket::withoutGlobalScope(BranchScoped::class)->get();

// Or remove all scopes
$allTickets = Ticket::withoutGlobalScopes()->get();
```

### Accessing Branch Context

```php
// In controllers/services
$branchContext = app(BranchContextService::class);

$currentBranch = $branchContext->getCurrentBranch();
$branchId = $branchContext->getCurrentBranchId();

if ($branchContext->canAccessBranch($someBranch)) {
    // Proceed with operation
}
```

### Authorization with Policies

```php
// In controllers
$this->authorize('view', $ticket);
$this->authorize('update', $ticket);
$this->authorize('delete', $ticket);

// Policies automatically check branch access
// via the BranchScopedPolicy helpers
```

## Database Schema

All relevant tables have `branch_id` columns (ULID, nullable):

```
users.branch_id         → Foreign key to branches.id
tickets.branch_id       → Foreign key to branches.id
inventory_items.branch_id → Foreign key to branches.id
pos_sales.branch_id     → Foreign key to branches.id
invoices.branch_id      → Foreign key to branches.id
payments.branch_id      → Foreign key to branches.id
```

**Migration:** `database/migrations/2025_10_17_094441_add_branch_id_to_related_tables.php`

## Testing

Comprehensive test coverage in `tests/Feature/BranchDataIsolationTest.php`:

```php
// Data isolation tests
- it prevents users from accessing data outside their branch
- it scopes inventory items to user branch
- it scopes POS sales to user branch
- it allows super admins without branch to see all data
- it excludes unauthenticated users from queries
- it scopes invoices to user branch
- it scopes payments to user branch

// Branch context service tests
- it provides current branch context for authenticated user
- it checks if user can access specific branch
- it returns accessible branches for super admin
- it returns only their branch for regular user

// User branch method tests
- it identifies super admins correctly
- it checks if user can manage branch
```

Run tests:
```bash
php artisan test tests/Feature/BranchDataIsolationTest.php
```

## Security Considerations

### ✅ Protected

1. **Automatic scoping** on all queries via global scopes
2. **Middleware enforcement** establishes context on every request
3. **Policy checks** in authorization gates
4. **User model validation** through `isSuperAdmin()` and `canManageBranch()`

### ⚠️ Manual Verification Needed

1. **API endpoints** - Ensure all routes check authorization
2. **Reports** - Verify cross-branch reporting uses `withoutGlobalScopes()`
3. **Migrations** - Confirm new tables include `branch_id` columns
4. **Third-party packages** - May need custom scoping

### 🚫 Common Pitfalls

1. **Querying without authentication** - Unscoped queries work but may return unexpected results
2. **Caching without branch context** - Cache should be keyed by branch
3. **Manual joins** - Direct SQL joins bypass scoping; use Eloquent relationships
4. **Bulk operations** - `update()` and `delete()` on query builders respect scopes

## Caching Strategy

### Branch Context Cache

- **TTL**: 1 hour (3600 seconds)
- **Key**: `branch_context_{branch_id}`
- **Invalidation**: Call `BranchContextService::clearCache($branchId)`

### When to Clear Cache

1. User is assigned to a new branch
2. User's branch is deleted or deactivated
3. Branch details change
4. Testing or development

```php
$branchContext = app(BranchContextService::class);
$branchContext->clearCache($branchId);
```

## Livewire Integration

The branch filter dropdown is already integrated in Livewire components:

```blade
<select wire:model.live="branchFilter">
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
    @endforeach
</select>
```

For branch-scoped users, the dropdown automatically shows only their branch. For super admins, it shows all active branches.

## Migration Guide

### Adding Multi-Tenancy to Existing Models

1. **Add `branch_id` column** to your migration:
   ```php
   $table->char('branch_id', 36)->nullable()->after('name');
   $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
   $table->index('branch_id');
   ```

2. **Add to model's fillable**:
   ```php
   protected $fillable = [..., 'branch_id'];
   ```

3. **Add branch relationship**:
   ```php
   public function branch(): BelongsTo
   {
       return $this->belongsTo(Branch::class);
   }
   ```

4. **Add global scope in boot**:
   ```php
   protected static function boot(): void
   {
       parent::boot();
       static::addGlobalScope(new BranchScoped());
   }
   ```

5. **Add to factory** (if needed):
   ```php
   'branch_id' => fake()->randomElement($branchIds),
   ```

## Future Enhancements

1. **Cross-branch reports** for super admins with explicit `withoutGlobalScopes()`
2. **Staff management** with role-based branch assignment
3. **Branch comparison** analytics
4. **Shift management** per branch
5. **POS configuration** per branch
6. **Settings** per branch

## Support

For issues or questions about branch-level multi-tenancy:

1. Check `tests/Feature/BranchDataIsolationTest.php` for examples
2. Review the relevant service/trait implementation
3. Verify middleware is registered in `bootstrap/app.php`
4. Check user's `branch_id` is set correctly
5. Ensure model has `branch_id` column and uses `BranchScoped` scope
