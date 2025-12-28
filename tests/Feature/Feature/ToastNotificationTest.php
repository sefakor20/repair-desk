<?php

declare(strict_types=1);

use App\Livewire\ToastManager;
use Livewire\Livewire;

test('toast manager can be rendered', function (): void {
    Livewire::test(ToastManager::class)
        ->assertOk()
        ->assertSee('pointer-events-none');
});

test('toast manager can add success toast', function (): void {
    Livewire::test(ToastManager::class)
        ->dispatch('toast', message: 'Test success message', type: 'success')
        ->assertSet('toasts.0.message', 'Test success message')
        ->assertSet('toasts.0.type', 'success');
});

test('toast manager can add error toast', function (): void {
    Livewire::test(ToastManager::class)
        ->dispatch('toast', message: 'Test error message', type: 'error')
        ->assertSet('toasts.0.message', 'Test error message')
        ->assertSet('toasts.0.type', 'error');
});

test('toast manager can add warning toast', function (): void {
    Livewire::test(ToastManager::class)
        ->dispatch('toast', message: 'Test warning message', type: 'warning')
        ->assertSet('toasts.0.message', 'Test warning message')
        ->assertSet('toasts.0.type', 'warning');
});

test('toast manager can add info toast', function (): void {
    Livewire::test(ToastManager::class)
        ->dispatch('toast', message: 'Test info message', type: 'info')
        ->assertSet('toasts.0.message', 'Test info message')
        ->assertSet('toasts.0.type', 'info');
});

test('toast manager can remove toast', function (): void {
    $component = Livewire::test(ToastManager::class)
        ->dispatch('toast', message: 'Test message', type: 'success');

    $toastId = $component->get('toasts.0.id');

    $component->call('removeToast', $toastId)
        ->assertCount('toasts', 0);
});

test('toast manager can handle multiple toasts', function (): void {
    Livewire::test(ToastManager::class)
        ->dispatch('toast', message: 'First message', type: 'success')
        ->dispatch('toast', message: 'Second message', type: 'error')
        ->dispatch('toast', message: 'Third message', type: 'warning')
        ->assertCount('toasts', 3)
        ->assertSet('toasts.0.message', 'First message')
        ->assertSet('toasts.1.message', 'Second message')
        ->assertSet('toasts.2.message', 'Third message');
});
