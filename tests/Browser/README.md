# Browser Testing with Pest v4

This directory contains browser tests using Pest v4's browser testing capabilities powered by Playwright.

## Setup

Browser testing is already configured for this project. The following has been set up:

-   **Pest Browser Plugin**: Installed via `pestphp/pest-plugin-browser`
-   **Playwright**: Installed with all browsers (Chrome, Firefox, Safari)
-   **Database**: Tests use `RefreshDatabase` trait for clean state
-   **Screenshots**: Configured to save to `tests/Browser/Screenshots` (gitignored)

## Running Browser Tests

### Run All Browser Tests

```bash
php artisan test --group=browser
```

### Run Specific Test File

```bash
php artisan test tests/Browser/SmokeTest.php
```

### Run in Parallel (Faster)

```bash
php artisan test --group=browser --parallel
```

### Debug Mode (Opens Browser Window)

```bash
vendor/bin/pest --group=browser --debug
```

### Headed Mode (See Browser While Running)

```bash
vendor/bin/pest --group=browser --headed
```

### Use Different Browser

```bash
php artisan test --group=browser --browser=firefox
php artisan test --group=browser --browser=safari
```

## Test Structure

### Current Browser Tests

1. **SmokeTest.php** ✅ (3 tests passing)

    - Loads main pages without JavaScript errors
    - Validates dashboard loads correctly
    - Validates analytics page loads correctly

2. **PosFlowTest.php** (Ready to implement)

    - Complete POS transaction flow
    - Discount application
    - Payment validation
    - Cart management

3. **ReturnProcessingTest.php** (Ready to implement)

    - Return creation
    - Return approval with inventory restoration
    - Return rejection
    - Status filtering

4. **AnalyticsDashboardTest.php** (Ready to implement)

    - Metrics display
    - Period filtering
    - Chart rendering
    - Revenue growth indicators

5. **InventoryManagementTest.php** (Ready to implement)
    - Item CRUD operations
    - Quantity adjustments
    - Low stock filtering
    - Search and sort

## Writing Browser Tests

### Basic Pattern

```php
it('does something in browser', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = visit('/some-page');

    $page->assertSee('Expected Text')
        ->assertNoJavaScriptErrors();
});
```

### Authentication

Always authenticate before visiting protected pages:

```php
$user = User::factory()->create();
$this->actingAs($user);
$page = visit('/dashboard');
```

### Multiple Pages (Smoke Testing)

```php
$pages = visit(['/dashboard', '/analytics', '/inventory']);
$pages->assertNoSmoke(); // Checks for JS errors and console logs
```

### Common Assertions

-   `assertSee('text')` - Text is visible on page
-   `assertDontSee('text')` - Text is not visible
-   `assertNoJavaScriptErrors()` - No JS errors in console
-   `assertNoConsoleLogs()` - No console logs
-   `assertNoSmoke()` - Combines both above checks
-   `assertUrlIs('/path')` - Current URL matches
-   `assertVisible('@selector')` - Element is visible
-   `assertMissing('@selector')` - Element is not present

### Element Interactions

```php
$page->click('Button Text')
    ->type('@input-field', 'value')
    ->select('@dropdown', 'option-value')
    ->check('@checkbox')
    ->waitFor('@element')
    ->screenshot('debug-screenshot');
```

### Using Data Attributes for Selectors

Prefer using data attributes for stable selectors:

```blade
<button data-test="submit-button">Submit</button>
```

```php
$page->click('@submit-button');
```

## Best Practices

1. **Use RefreshDatabase**: All browser tests should use database refresh for isolation
2. **Wait for Elements**: Use `waitFor()` when elements load asynchronously
3. **Descriptive Test Names**: Use clear, action-oriented test names
4. **Group Related Tests**: Use groups to organize tests (`@group browser, smoke`)
5. **Test Critical Paths**: Focus on user workflows that generate revenue or process data
6. **Keep Tests Fast**: Avoid unnecessary waits or operations
7. **Handle Failures**: Take screenshots on failure for debugging
8. **Parallel Execution**: Design tests to run independently

## CI Integration

Browser tests are configured for CI in `.github/workflows/tests.yml`:

```yaml
- uses: actions/setup-node@v4
  with:
      node-version: lts/*

- name: Install dependencies
  run: npm ci

- name: Install Playwright Browsers
  run: npx playwright install --with-deps

- name: Run Browser Tests
  run: ./vendor/bin/pest --group=browser --parallel --ci
```

## Troubleshooting

### Browser Not Found

```bash
npx playwright install
```

### Tests Timing Out

-   Increase timeout in `tests/Pest.php`:
    ```php
    pest()->browser()->timeout(10000); // 10 seconds
    ```

### Database Connection Issues

-   Ensure `RefreshDatabase` trait is used
-   Check database configuration in `phpunit.xml`

### Screenshots Not Saving

-   Ensure `tests/Browser/Screenshots` directory exists
-   Check permissions on the directory

## Resources

-   [Pest Browser Testing Docs](https://pestphp.com/docs/browser-testing)
-   [Playwright Documentation](https://playwright.dev/docs/intro)
-   [Laravel Testing Docs](https://laravel.com/docs/testing)
