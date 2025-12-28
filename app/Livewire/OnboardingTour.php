<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\TourService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OnboardingTour extends Component
{
    public bool $showTour = false;
    public int $currentStep = 0;
    public array $tourSteps = [];

    protected TourService $tourService;

    public function boot(TourService $tourService): void
    {
        $this->tourService = $tourService;
    }

    public function mount(): void
    {
        if ($this->shouldShowTour()) {
            $this->initializeTour();
        }
    }

    protected function shouldShowTour(): bool
    {
        return auth()->check() && $this->tourService->shouldShowTour(auth()->user());
    }

    protected function initializeTour(): void
    {
        $this->tourSteps = $this->tourService->getTourSteps(auth()->user());
        $this->showTour = true;
        $this->currentStep = 0;
    }

    #[Computed]
    public function currentTourStep(): ?array
    {
        return $this->tourSteps[$this->currentStep] ?? null;
    }

    #[Computed]
    public function isFirstStep(): bool
    {
        return $this->currentStep === 0;
    }

    #[Computed]
    public function isLastStep(): bool
    {
        return $this->currentStep === count($this->tourSteps) - 1;
    }

    #[Computed]
    public function progressPercentage(): int
    {
        if (empty($this->tourSteps)) {
            return 0;
        }

        return (int) round(($this->currentStep + 1) / count($this->tourSteps) * 100);
    }

    public function nextStep(): void
    {
        if (!$this->isLastStep) {
            // Mark current step as completed
            if (auth()->check()) {
                $this->tourService->markStepCompleted(auth()->user(), $this->currentTourStep['id']);
            }

            $this->currentStep++;
        } else {
            $this->completeTour();
        }
    }

    public function previousStep(): void
    {
        if (!$this->isFirstStep) {
            $this->currentStep--;
        }
    }

    public function skipTour(): void
    {
        if (auth()->check()) {
            $this->tourService->skipTour(auth()->user());
        }
        $this->closeTour();
    }

    public function completeTour(): void
    {
        if (auth()->check()) {
            $this->tourService->completeTour(auth()->user());
        }
        $this->closeTour();
    }

    public function closeTour(): void
    {
        $this->showTour = false;
        $this->dispatch('tour-closed');
    }

    public function render()
    {
        return view('livewire.onboarding-tour');
    }
}
