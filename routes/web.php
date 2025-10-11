<?php

declare(strict_types=1);

use App\Http\Controllers\Pos\PaystackCallbackController;
use App\Livewire\Customers\Create as CustomersCreate;
use App\Livewire\Customers\Edit as CustomersEdit;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Customers\Show as CustomersShow;
use App\Livewire\Dashboard;
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
use App\Livewire\Pos\Receipt;
use App\Livewire\Pos\Show as PosShow;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\CashDrawer\Index as CashDrawerIndex;
use App\Livewire\CashDrawer\OpenDrawer;
use App\Livewire\CashDrawer\CloseDrawer;
use App\Livewire\Shifts\Index as ShiftsIndex;
use App\Livewire\Shifts\OpenShift;
use App\Livewire\Shifts\CloseShift;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\LoyaltyRewards;
use App\Livewire\Settings\LoyaltyTiers;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\ReturnPolicies;
use App\Livewire\Settings\Shop as SettingsShop;
use App\Livewire\Settings\TwoFactor;
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
    Route::get('customers', CustomersIndex::class)->name('customers.index');
    Route::get('customers/create', CustomersCreate::class)->name('customers.create');
    Route::get('customers/{customer}', CustomersShow::class)->name('customers.show');
    Route::get('customers/{customer}/edit', CustomersEdit::class)->name('customers.edit');

    // Ticket routes
    Route::get('tickets', TicketsIndex::class)->name('tickets.index');
    Route::get('tickets/create', TicketsCreate::class)->name('tickets.create');
    Route::get('tickets/{ticket}', TicketsShow::class)->name('tickets.show');
    Route::get('tickets/{ticket}/edit', TicketsEdit::class)->name('tickets.edit');

    // Inventory routes
    Route::get('inventory', InventoryIndex::class)->name('inventory.index');
    Route::get('inventory/create', InventoryCreate::class)->name('inventory.create');
    Route::get('inventory/{item}', InventoryShow::class)->name('inventory.show');
    Route::get('inventory/{item}/edit', InventoryEdit::class)->name('inventory.edit');

    // Invoice routes
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index');
    Route::get('invoices/create', InvoicesCreate::class)->name('invoices.create');
    Route::get('invoices/{invoice}', InvoicesShow::class)->name('invoices.show');
    Route::get('invoices/{invoice}/edit', InvoicesEdit::class)->name('invoices.edit');

    // POS routes
    Route::get('pos', PosIndex::class)->name('pos.index');
    Route::get('pos/create', PosCreate::class)->name('pos.create');
    Route::get('pos/{sale}', PosShow::class)->name('pos.show');
    Route::get('pos/{sale}/receipt', Receipt::class)->name('pos.receipt');
    Route::get('pos/{sale}/paystack', PaystackPayment::class)->name('pos.paystack');
    Route::get('pos/{sale}/paystack/callback', PaystackCallbackController::class)->name('pos.paystack.callback');

    // Reports routes
    Route::get('reports', ReportsIndex::class)->name('reports.index');

    // Cash Drawer routes
    Route::get('cash-drawer', CashDrawerIndex::class)->name('cash-drawer.index');
    Route::get('cash-drawer/open', OpenDrawer::class)->name('cash-drawer.open');
    Route::get('cash-drawer/close', CloseDrawer::class)->name('cash-drawer.close');

    // Shifts routes
    Route::get('shifts', ShiftsIndex::class)->name('shifts.index');
    Route::get('shifts/open', OpenShift::class)->name('shifts.open');
    Route::get('shifts/close', CloseShift::class)->name('shifts.close');

    // User management routes
    Route::get('users', UsersIndex::class)->name('users.index');
    Route::get('users/create', UsersCreate::class)->name('users.create');
    Route::get('users/{user}/edit', UsersEdit::class)->name('users.edit');

    // Settings Routes
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('settings/shop', SettingsShop::class)->name('settings.shop');
    Route::get('settings/return-policies', ReturnPolicies::class)->name('settings.return-policies');
    Route::get('settings/loyalty-tiers', LoyaltyTiers::class)->name('settings.loyalty-tiers');
    Route::get('settings/loyalty-rewards', LoyaltyRewards::class)->name('settings.loyalty-rewards');

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

require __DIR__ . '/auth.php';
