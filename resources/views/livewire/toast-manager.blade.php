<div class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end gap-3 p-4 sm:p-6" x-data="{
    toasts: @entangle('toasts'),
    removeToast(id) {
        $wire.removeToast(id);
    }
}"
    x-on:toast-added.window="
        setTimeout(() => {
            removeToast($event.detail.id);
        }, $event.detail.duration);
    ">
    <div class="flex w-full max-w-sm flex-col gap-3">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0"
                class="pointer-events-auto w-full overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-zinc-700"
                :class="{
                    'bg-white dark:bg-zinc-800': toast.type === 'success' || toast.type === 'info',
                    'bg-red-50 dark:bg-red-900/20': toast.type === 'error',
                    'bg-amber-50 dark:bg-amber-900/20': toast.type === 'warning'
                }">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <!-- Success Icon with Animated Checkmark -->
                            <template x-if="toast.type === 'success'">
                                <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" stroke-width="2">
                                    <circle class="checkmark-circle" cx="12" cy="12" r="9"
                                        stroke-linecap="round" />
                                    <path class="checkmark-check" stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4" />
                                </svg>
                            </template>

                            <!-- Error Icon -->
                            <template x-if="toast.type === 'error'">
                                <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>

                            <!-- Warning Icon -->
                            <template x-if="toast.type === 'warning'">
                                <svg class="h-6 w-6 text-amber-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>

                            <!-- Info Icon -->
                            <template x-if="toast.type === 'info'">
                                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>

                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white" x-text="toast.message"></p>
                        </div>

                        <div class="ml-4 flex flex-shrink-0">
                            <button type="button" @click="removeToast(toast.id)"
                                class="inline-flex rounded-md text-zinc-400 transition-colors hover:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:hover:text-zinc-300">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div class="mt-3 h-1 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                        <div class="h-full bg-zinc-400 transition-all dark:bg-zinc-600" x-data="{ width: 100 }"
                            x-init="setTimeout(() => { width = 0; }, 50)"
                            :style="`width: ${width}%; transition: width ${toast.duration}ms linear;`">
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
