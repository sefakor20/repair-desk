<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithToast
{
    /**
     * Display a success toast notification.
     */
    protected function toastSuccess(string $message, int $duration = 5000): void
    {
        $this->dispatch('toast', message: $message, type: 'success', duration: $duration);
    }

    /**
     * Display an error toast notification.
     */
    protected function toastError(string $message, int $duration = 5000): void
    {
        $this->dispatch('toast', message: $message, type: 'error', duration: $duration);
    }

    /**
     * Display a warning toast notification.
     */
    protected function toastWarning(string $message, int $duration = 5000): void
    {
        $this->dispatch('toast', message: $message, type: 'warning', duration: $duration);
    }

    /**
     * Display an info toast notification.
     */
    protected function toastInfo(string $message, int $duration = 5000): void
    {
        $this->dispatch('toast', message: $message, type: 'info', duration: $duration);
    }

    /**
     * Display a toast notification with custom type.
     */
    protected function toast(string $message, string $type = 'success', int $duration = 5000): void
    {
        $this->dispatch('toast', message: $message, type: $type, duration: $duration);
    }
}
