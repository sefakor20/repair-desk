# Production Setup Guide

This guide explains the different approaches for setting up essential default data in your Repair Desk application for production deployment.

## Available Options

### 1. Migration Approach (Recommended) ⭐

**File**: `database/migrations/2025_12_28_100732_create_production_default_data.php`

**When to use**: During initial deployment or when you want data seeded automatically during `php artisan migrate`.

```bash
php artisan migrate
```

**Advantages**:

-   ✅ Runs automatically during migration
-   ✅ Safe for production (part of migration workflow)
-   ✅ Version controlled with your database schema
-   ✅ Won't duplicate data if run multiple times

### 2. Artisan Command Approach ⭐

**File**: `app/Console/Commands/SetupProductionDefaults.php`

**When to use**: When you want manual control over when default data is created.

```bash
# Basic setup (skips existing data)
php artisan app:setup-production

# Force overwrite existing data
php artisan app:setup-production --force
```

**Advantages**:

-   ✅ Manual control over execution
-   ✅ Clear feedback about what was created
-   ✅ Can force updates with --force flag
-   ✅ Safe to run multiple times

### 3. Production Seeder Approach

**File**: `database/seeders/ProductionEssentialsSeeder.php`

**When to use**: When you want to use Laravel's seeder system but with production-safe data.

```bash
# Run only production essentials
php artisan db:seed --class=ProductionEssentialsSeeder

# Or add to DatabaseSeeder if desired
```

**Advantages**:

-   ✅ Uses familiar seeder pattern
-   ✅ Safe to run multiple times (uses updateOrCreate)
-   ✅ Can be integrated into existing seeder workflows

## What Gets Created

All approaches create the same essential data:

### 1. Main Branch

-   **Name**: "Main Branch"
-   **Code**: "MAIN"
-   **Default contact info** (should be updated with real business details)
-   **Set as main branch** (`is_main = true`)

### 2. Shop Settings

-   **Shop name**: "Repair Desk"
-   **Default business information** (should be updated)
-   **Tax rate**: 0.00% (should be updated)
-   **Currency**: USD (should be updated)

### 3. Essential SMS Templates

-   **Repair Complete**: Notification when repair is finished
-   **Status Update**: General status change notifications
-   **Payment Reminder**: Invoice payment reminders
-   **Payment Received**: Payment confirmation

### 4. System Administrator

-   **Email**: admin@yourcompany.com
-   **Password**: change-me-in-production
-   **Role**: Admin
-   **Assigned to**: Main branch

## Post-Setup Tasks 🔒

After running any of these setups, you **MUST**:

1. **Change the admin password immediately**:

    ```bash
    # Option 1: Use tinker
    php artisan tinker
    >>> $admin = App\Models\User::where('email', 'admin@yourcompany.com')->first();
    >>> $admin->password = Hash::make('your-secure-password');
    >>> $admin->save();

    # Option 2: Create your own admin user and delete the default
    ```

2. **Update shop settings** with your actual business information
3. **Update the main branch** with real address and contact details
4. **Review and customize SMS templates** as needed
5. **Create additional branches** if you have multiple locations

## Deployment Recommendations

### For New Deployments

Use the **migration approach** - it will run automatically during your deployment process:

```bash
git clone your-repo
composer install --no-dev
php artisan migrate --force  # Includes default data
php artisan config:cache
php artisan route:cache
```

### For Existing Deployments

Use the **Artisan command approach** for more control:

```bash
php artisan app:setup-production
# Review output and update credentials immediately
```

### For Development/Testing

You can continue using the full development seeders:

```bash
php artisan db:seed  # Includes test data, multiple users, etc.
```

## Safety Features

All approaches include safety features to prevent data duplication:

-   ✅ **updateOrCreate** used where possible
-   ✅ **Existence checks** before creating users
-   ✅ **Idempotent operations** (safe to run multiple times)
-   ✅ **Clear status messages** showing what was created vs skipped

## Environment Considerations

These setups are designed for production but work in any environment:

-   **Development**: Use for basic setup, then run full seeders for test data
-   **Staging**: Perfect for staging environment setup
-   **Production**: Safe for production deployment

## Rollback

Since these create data (not schema), there's no automatic rollback. If needed:

1. Delete the created records manually
2. Use the migration's `down()` method if implemented
3. Reset the database and re-migrate if starting fresh

---

Choose the approach that best fits your deployment workflow. The migration approach is recommended for most use cases as it integrates seamlessly with Laravel's migration system.
