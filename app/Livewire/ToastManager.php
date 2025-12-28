<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class ToastManager extends Component
{
    public array $toasts = [];

    #[On('toast')]
    public function addToast(string $message, string $type = 'success', int $duration = 5000): void
    {
        $id = uniqid('toast_', true);

        $this->toasts[] = [
            'id' => $id,
            'message' => $message,
            'type' => $type,
            'duration' => $duration,
        ];

        // Auto-remove toast after duration
        $this->dispatch('toast-added', id: $id, duration: $duration);
    }

    public function removeToast(string $id): void
    {
        $this->toasts = array_filter(
            $this->toasts,
            fn(array $toast): bool => $toast['id'] !== $id,
        );
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.toast-manager');
    }
}
