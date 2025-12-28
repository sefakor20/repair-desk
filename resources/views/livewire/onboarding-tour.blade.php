<div>
    @if ($showTour && $this->currentTourStep)
        <!-- Tour Spotlight Overlay -->
        <div class="tour-spotlight-overlay fixed inset-0 z-40" wire:key="tour-overlay"></div>

        <!-- Tour Modal -->
        <div class="tour-modal fixed z-50" wire:key="tour-modal-{{ $currentStep }}" style="top: 20px; right: 20px;">
            <div
                class="w-80 bg-white rounded-xl shadow-2xl border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <!-- Progress Bar -->
                <div class="absolute top-0 left-0 h-1 bg-blue-600 rounded-t-xl transition-all duration-300"
                    style="width: {{ $this->progressPercentage }}%"></div>

                <!-- Tour Content -->
                <div class="p-5 pt-6">
                    <!-- Step Counter -->
                    <div class="flex items-center justify-between mb-4">
                        <span
                            class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                            {{ $currentStep + 1 }} / {{ count($tourSteps) }}
                        </span>
                        <flux:button wire:click="skipTour" variant="ghost" size="sm"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            ✕
                        </flux:button>
                    </div>

                    <!-- Tour Step Content -->
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white leading-tight">
                            {{ $this->currentTourStep['title'] }}
                        </h3>

                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            {{ $this->currentTourStep['content'] }}
                        </p>

                        <!-- Highlight Target Element -->
                        @if (isset($this->currentTourStep['target']) && $this->currentTourStep['target'])
                            <div
                                class="p-3 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800">
                                <p class="text-xs text-blue-800 dark:text-blue-200 flex items-center gap-1">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                                    Focus on the highlighted area
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Tour Navigation -->
                    <div
                        class="flex items-center justify-between pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                        <flux:button wire:click="previousStep" variant="outline" :disabled="$this->isFirstStep"
                            size="sm" class="px-3 py-1.5 text-xs" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="previousStep">← Back</span>
                            <span wire:loading wire:target="previousStep">...</span>
                        </flux:button>

                        <div class="flex items-center space-x-2">
                            @if ($this->isLastStep)
                                <flux:button wire:click="completeTour" variant="primary" size="sm"
                                    class="px-4 py-1.5 text-xs" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="completeTour">Finish</span>
                                    <span wire:loading wire:target="completeTour">...</span>
                                </flux:button>
                            @else
                                <flux:button wire:click="skipTour" variant="ghost" size="sm"
                                    class="px-3 py-1.5 text-xs text-gray-500" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="skipTour">Skip</span>
                                    <span wire:loading wire:target="skipTour">...</span>
                                </flux:button>
                                <flux:button wire:click="nextStep" variant="primary" size="sm"
                                    class="px-4 py-1.5 text-xs" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="nextStep">Next →</span>
                                    <span wire:loading wire:target="nextStep">...</span>
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tour Highlight Script -->
        <script wire:ignore>
            document.addEventListener('livewire:init', function() {
                let currentHighlight = null;
                let spotlightOverlay = null;

                // Function to create spotlight effect
                function createSpotlight(element) {
                    const overlay = document.querySelector('.tour-spotlight-overlay');
                    if (!overlay) return;

                    if (element) {
                        const rect = element.getBoundingClientRect();
                        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

                        // Calculate spotlight dimensions with padding
                        const padding = 16;
                        const top = rect.top + scrollTop - padding;
                        const left = rect.left + scrollLeft - padding;
                        const width = rect.width + (padding * 2);
                        const height = rect.height + (padding * 2);

                        overlay.style.background = `
                            radial-gradient(circle at ${left + width/2}px ${top + height/2}px,
                            transparent ${Math.max(width, height)/2 + 10}px,
                            rgba(0, 0, 0, 0.75) ${Math.max(width, height)/2 + 20}px)
                        `;
                    } else {
                        overlay.style.background = 'rgba(0, 0, 0, 0.4)';
                    }
                }

                // Function to highlight target elements
                function highlightElement(selector) {

                    // Remove previous highlight
                    if (currentHighlight) {
                        currentHighlight.classList.remove('tour-highlight');
                        currentHighlight = null;
                    }

                    if (!selector) {
                        createSpotlight(null);
                        return;
                    }

                    // Add small delay to ensure DOM is ready
                    setTimeout(() => {
                        // Try multiple selector strategies
                        let element = document.querySelector(selector);

                        // If not found, try finding parent elements that might contain our target
                        if (!element && selector.includes('[data-tour')) {
                            const tourValue = selector.match(/data-tour="([^"]+)"/)?.[1];

                            if (tourValue) {
                                element = document.querySelector(`[data-tour="${tourValue}"]`);

                                // If still not found, look for elements with the tour attribute anywhere
                                if (!element) {
                                    element = document.querySelector(`*[data-tour="${tourValue}"]`);
                                }

                                // Try to find the element manually
                                if (!element) {
                                    const allTourElements = document.querySelectorAll('[data-tour]');
                                    for (const el of allTourElements) {
                                        if (el.getAttribute('data-tour') === tourValue) {
                                            element = el;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        if (element) {

                            // For Flux UI components, we might need to highlight the actual clickable element
                            const clickableElement = element.querySelector('a, button') || element;

                            clickableElement.classList.add('tour-highlight');
                            currentHighlight = clickableElement;

                            // Create spotlight effect
                            createSpotlight(clickableElement);

                            // Scroll into view with more padding
                            clickableElement.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center',
                                inline: 'center'
                            });

                            // Position modal to avoid blocking the highlight
                            const modal = document.querySelector('.tour-modal');
                            if (modal) {
                                const rect = clickableElement.getBoundingClientRect();
                                const modalRect = modal.getBoundingClientRect();
                                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                                const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

                                // Reset any previous positioning
                                modal.style.left = 'auto';
                                modal.style.right = 'auto';
                                modal.style.top = 'auto';
                                modal.style.bottom = 'auto';

                                // Calculate available space
                                const spaceRight = window.innerWidth - rect.right;
                                const spaceLeft = rect.left;
                                const spaceTop = rect.top;
                                const spaceBottom = window.innerHeight - rect.bottom;

                                // Try to position modal to the right first
                                if (spaceRight > modalRect.width + 40) {
                                    modal.style.left = (rect.right + scrollLeft + 20) + 'px';
                                    modal.style.top = Math.max(20, rect.top + scrollTop - 20) + 'px';
                                }
                                // Try positioning to the left
                                else if (spaceLeft > modalRect.width + 40) {
                                    modal.style.left = (rect.left + scrollLeft - modalRect.width - 20) + 'px';
                                    modal.style.top = Math.max(20, rect.top + scrollTop - 20) + 'px';
                                }
                                // Try positioning below
                                else if (spaceBottom > modalRect.height + 40) {
                                    modal.style.left = Math.max(20, Math.min(rect.left + scrollLeft, window
                                        .innerWidth - modalRect.width - 20)) + 'px';
                                    modal.style.top = (rect.bottom + scrollTop + 20) + 'px';
                                }
                                // Try positioning above
                                else if (spaceTop > modalRect.height + 40) {
                                    modal.style.left = Math.max(20, Math.min(rect.left + scrollLeft, window
                                        .innerWidth - modalRect.width - 20)) + 'px';
                                    modal.style.top = (rect.top + scrollTop - modalRect.height - 20) + 'px';
                                }
                                // Fallback to top-right corner
                                else {
                                    modal.style.top = '20px';
                                    modal.style.right = '20px';
                                }
                            }
                        } else {
                            createSpotlight(null);
                        }
                    }, 100);
                }

                // Listen for Livewire updates to trigger highlighting
                Livewire.on('tour-step-updated', (event) => {
                    const target = event[0]?.target || event.target || null;
                    // Use longer timeout to ensure DOM is fully updated
                    setTimeout(() => highlightElement(target), 400);
                });

                // Update spotlight on window resize
                window.addEventListener('resize', () => {
                    if (currentHighlight) {
                        createSpotlight(currentHighlight);
                    }
                });

                // Highlight current step target when page loads
                @if (isset($this->currentTourStep['target']) && $this->currentTourStep['target'])
                    // Wait for page to be fully loaded
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', () => {
                            setTimeout(() => highlightElement({!! json_encode($this->currentTourStep['target']) !!}), 300);
                        });
                    } else {
                        setTimeout(() => highlightElement({!! json_encode($this->currentTourStep['target']) !!}), 300);
                    }
                @endif

                // Listen for tour step changes
                Livewire.hook('message.processed', (message, component) => {
                    if (component.name === 'onboarding-tour') {
                        const stepData = component.get('currentTourStep');
                        if (stepData && stepData.target) {
                            setTimeout(() => highlightElement(stepData.target), 200);
                        }
                    }
                });

                // Clean up on tour close
                Livewire.on('tour-closed', () => {
                    if (currentHighlight) {
                        currentHighlight.classList.remove('tour-highlight');
                        currentHighlight = null;
                    }
                });
            });
        </script>

        <!-- Tour Styles -->
        <style>
            .tour-spotlight-overlay {
                background: rgba(0, 0, 0, 0.4);
                transition: background 0.3s ease;
                pointer-events: none;
            }

            .tour-modal {
                animation: tourModalSlideIn 0.3s ease-out;
                max-height: calc(100vh - 40px);
                overflow-y: auto;
            }

            @keyframes tourModalSlideIn {
                from {
                    opacity: 0;
                    transform: translateX(20px);
                }

                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            .tour-highlight {
                position: relative;
                z-index: 41;
                border-radius: 8px;
                transition: all 0.3s ease;
                box-shadow:
                    0 0 0 3px rgba(59, 130, 246, 0.8),
                    0 0 0 6px rgba(59, 130, 246, 0.4),
                    0 8px 32px rgba(59, 130, 246, 0.3);
            }

            .tour-highlight::before {
                content: '';
                position: absolute;
                top: -6px;
                left: -6px;
                right: -6px;
                bottom: -6px;
                border: 2px solid #3b82f6;
                border-radius: 12px;
                animation: tour-pulse 2s infinite;
                pointer-events: none;
            }

            @keyframes tour-pulse {

                0%,
                100% {
                    opacity: 1;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.7;
                    transform: scale(1.02);
                }
            }

            /* Dark mode adjustments */
            @media (prefers-color-scheme: dark) {
                .tour-highlight {
                    box-shadow:
                        0 0 0 3px rgba(96, 165, 250, 0.8),
                        0 0 0 6px rgba(96, 165, 250, 0.4),
                        0 8px 32px rgba(96, 165, 250, 0.3);
                }

                .tour-highlight::before {
                    border-color: #60a5fa;
                }
            }

            /* Mobile responsive modal */
            @media (max-width: 640px) {
                .tour-modal {
                    top: 20px !important;
                    left: 20px !important;
                    right: 20px !important;
                    width: auto !important;
                }
            }
        </style>
    @endif
</div>
