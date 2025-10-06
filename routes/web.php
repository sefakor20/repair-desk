<?php

declare(strict_types=1);

use App\Livewire\Customers\Create as CustomersCreate;
use App\Livewire\Customers\Edit as CustomersEdit;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Customers\Show as CustomersShow;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Tickets\Create as TicketsCreate;
use App\Livewire\Tickets\Index as TicketsIndex;
use App\Livewire\Tickets\Show as TicketsShow;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function (): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
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
    Route::get('tickets/{ticket}/edit', function () {
        return 'Edit ticket';
    })->name('tickets.edit');

    // Settings Routes
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

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
