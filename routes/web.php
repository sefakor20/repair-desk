<?php

declare(strict_types=1);

use App\Http\Controllers\Pos\PaystackCallbackController;
use App\Livewire\Analytics\Dashboard as AnalyticsDashboard;
use App\Livewire\Customers\Create as CustomersCreate;
use App\Livewire\Customers\Edit as CustomersEdit;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Customers\Show as CustomersShow;
use App\Livewire\Dashboard;
use App\Livewire\Devices\Create as DevicesCreate;
use App\Livewire\Devices\Edit as DevicesEdit;
use App\Livewire\Devices\Index as DevicesIndex;
use App\Livewire\Devices\Show as DevicesShow;
use App\Livewire\Inventory\Create as InventoryCreate;
use App\Livewire\Inventory\Edit as InventoryEdit;
use App\Livewire\Inventory\Index as InventoryIndex;
use App\Livewire\Inventory\Show as InventoryShow;
use App\Livewire\Invoices\Create as InvoicesCreate;
use App\Livewire\Invoices\Edit as InvoicesEdit;
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoices\Show as InvoicesShow;
use App\Livewire\Pos\Create as PosCreate;
use App\Livewire\Pos\Index as PosIndex;
use App\Livewire\Pos\PaystackPayment;
use App\Livewire\Pos\ProcessReturn;
use App\Livewire\Pos\Receipt;
use App\Livewire\Pos\ReturnIndex;
use App\Livewire\Pos\Show as PosShow;
use App\Livewire\Portal\Auth\Login as PortalLogin;
use App\Livewire\Portal\Loyalty\Dashboard as LoyaltyDashboard;
use App\Livewire\Portal\Loyalty\History as LoyaltyHistory;
use App\Livewire\Portal\Loyalty\Rewards as LoyaltyRewards;
use App\Livewire\Portal\Profile\Edit as PortalProfileEdit;
use App\Livewire\Portal\Profile\TransferPoints;
use App\Livewire\Portal\Referrals\Index as PortalReferralsIndex;
use App\Livewire\Portal\Settings\Preferences as PortalPreferences;
use App\Livewire\Portal\Tickets\Index as PortalTicketsIndex;
use App\Livewire\Portal\Tickets\Show as PortalTicketsShow;
use App\Livewire\Portal\Invoices\Index as PortalInvoicesIndex;
use App\Livewire\Portal\Invoices\PayInvoice as PortalPayInvoice;
use App\Http\Controllers\Portal\InvoicePaymentCallbackController;
use App\Http\Controllers\Portal\InvoicePdfController;
use App\Http\Controllers\Portal\ReceiptPdfController;
use App\Livewire\Portal\Devices\Index as PortalDevicesIndex;
use App\Livewire\Portal\Devices\Show as PortalDevicesShow;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\CashDrawer\Index as CashDrawerIndex;
use App\Livewire\CashDrawer\OpenDrawer;
use App\Livewire\CashDrawer\CloseDrawer;
use App\Livewire\Shifts\Index as ShiftsIndex;
use App\Livewire\Shifts\OpenShift;
use App\Livewire\Shifts\CloseShift;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\LoyaltyRewards as SettingsLoyaltyRewards;
use App\Livewire\Settings\LoyaltyTiers;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\ReturnPolicies;
use App\Livewire\Settings\Shop as SettingsShop;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Staff\Index as StaffIndex;
use App\Livewire\Tickets\Create as TicketsCreate;
use App\Livewire\Tickets\Edit as TicketsEdit;
use App\Livewire\Tickets\Index as TicketsIndex;
use App\Livewire\Tickets\Show as TicketsShow;
use App\Livewire\Users\Create as UsersCreate;
use App\Livewire\Users\Edit as UsersEdit;
use App\Livewire\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function (): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View {
    return view('welcome');
})->name('home');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function (): void {
    // Customer Management Routes
    Route::get('customers', CustomersIndex::class)->name('customers.index')->middleware('staff.permission:manage_customers');
    Route::get('customers/create', CustomersCreate::class)->name('customers.create')->middleware('staff.permission:manage_customers');
    Route::get('customers/{customer}', CustomersShow::class)->name('customers.show')->middleware('staff.permission:manage_customers');
    Route::get('customers/{customer}/edit', CustomersEdit::class)->name('customers.edit')->middleware('staff.permission:manage_customers');

    // Branch Management Routes
    Route::get('branches', \App\Livewire\Branches\Index::class)->name('branches.index');
    Route::get('branches/create', \App\Livewire\Branches\Create::class)->name('branches.create');
    Route::get('branches/{branch}', \App\Livewire\Branches\Show::class)->name('branches.show');
    Route::get('branches/{branch}/edit', \App\Livewire\Branches\Edit::class)->name('branches.edit');
    // Device Management Routes
    Route::get('devices', DevicesIndex::class)->name('devices.index');
    Route::get('devices/create', DevicesCreate::class)->name('devices.create');
    Route::get('devices/{device}', DevicesShow::class)->name('devices.show');
    Route::get('devices/{device}/edit', DevicesEdit::class)->name('devices.edit');

    // Ticket routes
    Route::get('tickets', TicketsIndex::class)->name('tickets.index')->middleware('staff.permission:manage_tickets');
    Route::get('tickets/create', TicketsCreate::class)->name('tickets.create')->middleware('staff.permission:create_tickets');
    Route::get('tickets/{ticket}', TicketsShow::class)->name('tickets.show')->middleware('staff.permission:view_assigned_tickets');
    Route::get('tickets/{ticket}/edit', TicketsEdit::class)->name('tickets.edit')->middleware('staff.permission:manage_tickets');

    // Inventory routes
    Route::get('inventory', InventoryIndex::class)->name('inventory.index')->middleware('staff.permission:view_inventory');
    Route::get('inventory/create', InventoryCreate::class)->name('inventory.create')->middleware('staff.permission:manage_inventory');
    Route::get('inventory/{item}', InventoryShow::class)->name('inventory.show')->middleware('staff.permission:view_inventory');
    Route::get('inventory/{item}/edit', InventoryEdit::class)->name('inventory.edit')->middleware('staff.permission:manage_inventory');

    // Invoice routes
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index')->middleware('staff.permission:view_sales');
    Route::get('invoices/create', InvoicesCreate::class)->name('invoices.create')->middleware('staff.permission:create_invoices');
    Route::get('invoices/{invoice}', InvoicesShow::class)->name('invoices.show')->middleware('staff.permission:view_sales');
    Route::get('invoices/{invoice}/edit', InvoicesEdit::class)->name('invoices.edit')->middleware('staff.permission:create_invoices');

    // POS routes
    Route::get('pos', PosIndex::class)->name('pos.index')->middleware('staff.permission:view_sales');
    Route::get('pos/create', PosCreate::class)->name('pos.create')->middleware('staff.permission:create_sales');
    Route::get('pos/returns', ReturnIndex::class)->name('pos.returns.index')->middleware('staff.permission:process_payments');
    Route::get('pos/{sale}', PosShow::class)->name('pos.show')->middleware('staff.permission:view_sales');
    Route::get('pos/{sale}/receipt', Receipt::class)->name('pos.receipt')->middleware('staff.permission:view_sales');
    Route::get('pos/{sale}/return', ProcessReturn::class)->name('pos.returns.create')->middleware('staff.permission:process_payments');
    Route::get('pos/{sale}/paystack', PaystackPayment::class)->name('pos.paystack')->middleware('staff.permission:process_payments');
    Route::get('pos/{sale}/paystack/callback', PaystackCallbackController::class)->name('pos.paystack.callback')->middleware('staff.permission:process_payments');

    // Reports routes
    Route::get('reports', ReportsIndex::class)->name('reports.index')->middleware('staff.permission:view_reports');

    // Analytics routes
    Route::get('analytics', AnalyticsDashboard::class)->name('analytics.dashboard')->middleware('staff.permission:view_reports');

    // Cash Drawer routes
    Route::get('cash-drawer', CashDrawerIndex::class)->name('cash-drawer.index')->middleware('staff.permission:manage_cash_drawer');
    Route::get('cash-drawer/open', OpenDrawer::class)->name('cash-drawer.open')->middleware('staff.permission:manage_cash_drawer');
    Route::get('cash-drawer/close', CloseDrawer::class)->name('cash-drawer.close')->middleware('staff.permission:manage_cash_drawer');

    // Shifts routes
    Route::get('shifts', ShiftsIndex::class)->name('shifts.index')->middleware('staff.permission:view_reports');
    Route::get('shifts/open', OpenShift::class)->name('shifts.open')->middleware('staff.permission:manage_cash_drawer');
    Route::get('shifts/close', CloseShift::class)->name('shifts.close')->middleware('staff.permission:manage_cash_drawer');

    // User management routes
    Route::get('users', UsersIndex::class)->name('users.index')->middleware('staff.permission:manage_settings');
    Route::get('users/create', UsersCreate::class)->name('users.create')->middleware('staff.permission:manage_settings');
    Route::get('users/{user}/edit', UsersEdit::class)->name('users.edit')->middleware('staff.permission:manage_settings');

    // Staff management routes
    Route::get('staff', StaffIndex::class)->name('staff.index')->middleware('staff.permission:manage_staff');

    // Settings Routes
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('settings/shop', SettingsShop::class)->name('settings.shop')->middleware('staff.permission:manage_settings');
    Route::get('settings/return-policies', ReturnPolicies::class)->name('settings.return-policies')->middleware('staff.permission:manage_settings');
    Route::get('settings/loyalty-tiers', LoyaltyTiers::class)->name('settings.loyalty-tiers')->middleware('staff.permission:manage_settings');
    Route::get('settings/loyalty-rewards', SettingsLoyaltyRewards::class)->name('settings.loyalty-rewards')->middleware('staff.permission:manage_settings');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// Customer Loyalty Portal Routes (public - no auth required)
Route::prefix('portal')->name('portal.')->group(function (): void {
    // Portal login/access
    Route::get('login', PortalLogin::class)->name('login');

    // Protected portal routes with token validation
    Route::middleware('customer.portal')->group(function (): void {
        // Loyalty routes
        Route::prefix('loyalty/{customer}/{token}')->name('loyalty.')->group(function (): void {
            Route::get('/', LoyaltyDashboard::class)->name('dashboard');
            Route::get('/rewards', LoyaltyRewards::class)->name('rewards');
            Route::get('/history', LoyaltyHistory::class)->name('history');
        });

        // Profile routes
        Route::prefix('profile/{customer}/{token}')->name('profile.')->group(function (): void {
            Route::get('/edit', PortalProfileEdit::class)->name('edit');
            Route::get('/transfer-points', TransferPoints::class)->name('transfer-points');
        });

        // Settings routes
        Route::prefix('settings/{customer}/{token}')->name('settings.')->group(function (): void {
            Route::get('/preferences', PortalPreferences::class)->name('preferences');
        });

        // Notifications routes
        Route::prefix('notifications/{customer}/{token}')->name('notifications.')->group(function (): void {
            Route::get('/history', \App\Livewire\Portal\NotificationHistory::class)->name('history');
        });

        // Referral routes
        Route::prefix('referrals/{customer}/{token}')->name('referrals.')->group(function (): void {
            Route::get('/', PortalReferralsIndex::class)->name('index');
        });

        // Ticket tracking routes
        Route::prefix('tickets/{customer}/{token}')->name('tickets.')->group(function (): void {
            Route::get('/', PortalTicketsIndex::class)->name('index');
            Route::get('/{ticket}', PortalTicketsShow::class)->name('show');
        });

        // Invoice routes
        Route::prefix('invoices/{customer}/{token}')->name('invoices.')->group(function (): void {
            Route::get('/', PortalInvoicesIndex::class)->name('index');
            Route::get('/{invoice}/pay', PortalPayInvoice::class)->name('pay');
            Route::get('/{invoice}/payment/callback', InvoicePaymentCallbackController::class)->name('payment.callback');
            Route::get('/{invoice}/pdf', InvoicePdfController::class)->name('pdf');
            Route::get('/payments/{payment}/receipt', ReceiptPdfController::class)->name('receipt');
        });

        // Device routes
        Route::prefix('devices/{customer}/{token}')->name('devices.')->group(function (): void {
            Route::get('/', PortalDevicesIndex::class)->name('index');
            Route::get('/{device}', PortalDevicesShow::class)->name('show');
        });

        // Legacy route redirects (backwards compatibility)
        Route::get('{customer}', function ($customer) {
            $customer = \App\Models\Customer::findOrFail($customer);
            return redirect()->route('portal.loyalty.dashboard', [
                'customer' => $customer->id,
                'token' => $customer->portal_access_token ?? $customer->generatePortalAccessToken(),
            ]);
        });
    });
});

// Create a portal access route (allows customers to access via emailed link)
Route::get('portal/access/{customer}/{token}', function ($customer, $token) {
    $customer = \App\Models\Customer::validatePortalToken($token, $customer);

    if (! $customer) {
        return redirect()->route('portal.login')->with('error', 'Invalid or expired access link.');
    }

    return redirect()->route('portal.loyalty.dashboard', [
        'customer' => $customer->id,
        'token' => $token,
    ]);
})->name('portal.access');

require __DIR__ . '/auth.php';
