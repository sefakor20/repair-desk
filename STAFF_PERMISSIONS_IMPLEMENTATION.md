# Staff Permissions Implementation Summary

## Overview

Implemented comprehensive staff-based permissions system throughout the application, including service layer, middleware, Blade directives, route protection, and UI conditional rendering.

## What Was Implemented

### 1. Core Permission System ✅

-   **StaffPermissionService** - Centralized permission checking with 10-minute caching
    -   18 system permissions defined
    -   Super admin bypass
    -   Branch-scoped permission checks
    -   Cache key format: `staff_assignment_{user_id}_{branch_id}`

### 2. User Model Helpers ✅

Added 4 convenience methods to User model:

-   `hasStaffPermission(string $permission): bool`
-   `hasAnyStaffPermission(array $permissions): bool`
-   `hasAllStaffPermissions(array $permissions): bool`
-   `activeStaffAssignment(): ?Staff`

### 3. Blade Directives ✅

Registered 4 custom Blade directives for view-level permission checks:

-   `@canStaff('permission')...@endcanStaff`
-   `@hasStaffPermission('permission')...@endhasStaffPermission`
-   `@hasAnyStaffPermission(['perm1', 'perm2'])...@endhasAnyStaffPermission`
-   `@hasAllStaffPermissions(['perm1', 'perm2'])...@endhasAllStaffPermissions`

### 4. Middleware Protection ✅

-   **CheckStaffPermission** middleware created
-   Registered as 'staff.permission' alias
-   Applied to 30+ routes across:
    -   Customers
    -   Tickets
    -   Inventory
    -   POS
    -   Invoices
    -   Cash Drawer
    -   Reports
    -   Settings
    -   Staff Management

### 5. Navigation Filtering ✅

Updated sidebar navigation (`resources/views/components/layouts/app/sidebar.blade.php`) with permission-based visibility:

-   **Customers** - requires `manage_customers`
-   **Tickets** - requires `manage_tickets` OR `view_assigned_tickets`
-   **Inventory** - requires `manage_inventory`, `view_inventory`, OR `use_inventory`
-   **Invoices** - requires `create_invoices` OR `view_sales`
-   **POS** - requires `create_sales`
-   **Returns** - requires `create_sales`
-   **Cash Drawer** - requires `manage_cash_drawer` OR `process_payments`
-   **Branches** - uses existing Policy (Admin only)
-   **Dashboard, Shifts, Analytics** - visible to all authenticated users
-   **Reports, Users** - uses existing Policies

### 6. View Conditional Rendering ✅

Updated key views to hide/show actions based on staff permissions:

#### Inventory Index (`resources/views/livewire/inventory/index.blade.php`)

-   "Add Item" button - requires `manage_inventory`
-   Edit/Delete actions - requires `manage_inventory` + Policy authorization

#### Customers Index (`resources/views/livewire/customers/index.blade.php`)

-   "New Customer" button - requires `manage_customers` + Policy authorization

#### Tickets Index (`resources/views/livewire/tickets/index.blade.php`)

-   "New Ticket" button - requires `manage_tickets` OR `create_tickets` + Policy authorization

#### POS Index (`resources/views/livewire/pos/index.blade.php`)

-   "New Sale" button - requires `create_sales` + Policy authorization

### 7. Test Suite ✅

-   Created comprehensive test suite with 22 tests (62 assertions)
-   Created test helpers: `createAdmin()`, `createManager()`, `createUserWithPermissions()`
-   Updated 28+ test files to use new helpers
-   All 1,125 tests passing (2,750 assertions)

## System Permissions

### 18 Defined Permissions

1. `manage_staff` - Create, update, delete staff assignments
2. `manage_tickets` - Full ticket management
3. `manage_inventory` - Full inventory management
4. `manage_customers` - Full customer management
5. `view_reports` - Access reports and analytics
6. `manage_settings` - Modify system settings
7. `process_payments` - Process payment transactions
8. `view_assigned_tickets` - View tickets assigned to user
9. `update_ticket_status` - Update status of tickets
10. `use_inventory` - Use inventory items for repairs
11. `create_sales` - Create POS sales
12. `create_tickets` - Create new tickets
13. `schedule_appointments` - Schedule customer appointments
14. `view_inventory` - View inventory items (read-only)
15. `create_inventory_adjustments` - Adjust inventory quantities
16. `view_sales` - View sales records
17. `create_invoices` - Create invoices
18. `manage_cash_drawer` - Manage cash drawer operations

## Role Permissions Mapping

### Branch Manager (8 permissions)

-   manage_staff
-   manage_tickets
-   manage_inventory
-   manage_customers
-   view_reports
-   manage_settings
-   process_payments
-   manage_cash_drawer

### Technician (4 permissions)

-   view_assigned_tickets
-   update_ticket_status
-   use_inventory
-   create_sales

### Receptionist (4 permissions)

-   create_tickets
-   schedule_appointments
-   manage_customers
-   view_inventory

### Inventory (3 permissions)

-   view_inventory
-   use_inventory
-   create_inventory_adjustments

### Cashier (5 permissions)

-   create_sales
-   process_payments
-   view_sales
-   create_invoices
-   manage_cash_drawer

## Legacy Compatibility

### Admin/Manager Bypass

Admin and Manager users (UserRole enum) automatically bypass staff permission checks for backward compatibility. This ensures existing admin functionality continues to work without requiring staff assignments.

```php
// In CheckStaffPermission middleware
if (in_array($user->role, [UserRole::Admin, UserRole::Manager])) {
    return $next($request);
}
```

## Usage Examples

### In Routes

```php
Route::get('/customers', Index::class)
    ->name('customers.index')
    ->middleware('staff.permission:manage_customers');
```

### In Controllers/Livewire

```php
if (auth()->user()->hasStaffPermission('manage_inventory')) {
    // User can manage inventory
}

if (auth()->user()->hasAnyStaffPermission(['manage_tickets', 'view_assigned_tickets'])) {
    // User can access tickets section
}
```

### In Blade Views

```blade
@canStaff('manage_customers')
    <button wire:click="deleteCustomer">Delete</button>
@endcanStaff

@hasAnyStaffPermission(['manage_tickets', 'create_tickets'])
    <a href="{{ route('tickets.create') }}">New Ticket</a>
@endhasAnyStaffPermission
```

### In Tests

```php
test('staff with manage_inventory can create items', function () {
    $user = createUserWithPermissions(['manage_inventory']);

    $this->actingAs($user)
        ->get(route('inventory.create'))
        ->assertSuccessful();
});
```

## Performance Optimizations

### Caching Strategy

-   Staff assignments cached for 10 minutes per user/branch
-   Cache automatically cleared when staff record updated
-   Prevents N+1 queries on every permission check
-   Cache key: `staff_assignment_{user_id}_{branch_id}`

## Testing Results

```
Tests:    4 skipped, 1125 passed (2750 assertions)
Duration: 52.72s
```

All tests passing including:

-   Permission enforcement tests (22 tests)
-   Route protection tests
-   Middleware tests
-   Service layer tests
-   Integration tests across all modules

## Files Modified

### Created

1. `app/Services/StaffPermissionService.php`
2. `app/Http/Middleware/CheckStaffPermission.php`
3. `tests/Feature/Permissions/StaffPermissionEnforcementTest.php`
4. `tests/Pest.php` - Added helper functions

### Updated

1. `app/Models/User.php` - Added permission helper methods
2. `app/Models/Staff.php` - Fixed hasPermission() to check is_active
3. `app/Enums/StaffRole.php` - Updated permissions() method
4. `app/Policies/UserPolicy.php` - Integrated staff permissions
5. `app/Providers/AppServiceProvider.php` - Registered Blade directives
6. `bootstrap/app.php` - Registered middleware
7. `routes/web.php` - Protected routes
8. `resources/views/components/layouts/app/sidebar.blade.php` - Navigation filtering
9. `resources/views/livewire/inventory/index.blade.php` - Conditional rendering
10. `resources/views/livewire/customers/index.blade.php` - Conditional rendering
11. `resources/views/livewire/tickets/index.blade.php` - Conditional rendering
12. `resources/views/livewire/pos/index.blade.php` - Conditional rendering
13. 28+ test files - Updated to use test helpers

## Next Steps (Optional Future Enhancements)

1. **Audit Logging** - Log permission checks for security auditing
2. **Permission Groups** - Group related permissions for easier management
3. **Dynamic Permissions** - Allow admins to create custom permissions via UI
4. **Time-based Permissions** - Restrict permissions to specific time windows
5. **IP-based Restrictions** - Add IP whitelisting for sensitive permissions
6. **Two-Factor for Critical Actions** - Require 2FA for manage_settings, manage_cash_drawer
7. **Permission Analytics** - Track which permissions are most used
8. **Permission Requests** - Allow staff to request additional permissions

## Conclusion

The staff permissions system is fully operational with comprehensive coverage across:

-   ✅ Service layer with caching
-   ✅ Middleware protection on routes
-   ✅ UI conditional rendering
-   ✅ Navigation filtering
-   ✅ Comprehensive test coverage
-   ✅ Legacy Admin/Manager compatibility

All 1,125 tests passing, system is ready for production use.
