@props(['action' => 'Create'])

<form wire:submit="save" class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <!-- First Name -->
        <div>
            <label for="first_name" class="block text-sm font-medium text-zinc-900 dark:text-white">
                First Name <span class="text-red-500">*</span>
            </label>
            <input type="text" id="first_name" wire:model="form.first_name" required
                class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white"
                placeholder="John">
            @error('form.first_name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Last Name -->
        <div>
            <label for="last_name" class="block text-sm font-medium text-zinc-900 dark:text-white">
                Last Name <span class="text-red-500">*</span>
            </label>
            <input type="text" id="last_name" wire:model="form.last_name" required
                class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white"
                placeholder="Doe">
            @error('form.last_name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-zinc-900 dark:text-white">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" id="email" wire:model="form.email" required
                class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white"
                placeholder="john@example.com">
            @error('form.email')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Phone -->
        <div>
            <label for="phone" class="block text-sm font-medium text-zinc-900 dark:text-white">
                Phone <span class="text-red-500">*</span>
            </label>
            <input type="tel" id="phone" wire:model="form.phone" required
                class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white"
                placeholder="+1 (555) 000-0000">
            @error('form.phone')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Address -->
    <div>
        <label for="address" class="block text-sm font-medium text-zinc-900 dark:text-white">
            Address
        </label>
        <textarea id="address" wire:model="form.address" rows="2"
            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white"
            placeholder="123 Main St, City, State, ZIP"></textarea>
        @error('form.address')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Notes -->
    <div>
        <label for="notes" class="block text-sm font-medium text-zinc-900 dark:text-white">
            Notes
        </label>
        <textarea id="notes" wire:model="form.notes" rows="3"
            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white"
            placeholder="Any additional notes about the customer..."></textarea>
        @error('form.notes')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Tags -->
    <div>
        <label for="tags_input" class="block text-sm font-medium text-zinc-900 dark:text-white">
            Tags
        </label>
        <input type="text" id="tags_input" wire:model="tagsInput" wire:keydown.enter.prevent="addTag"
            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white"
            placeholder="Type a tag and press Enter">
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Press Enter to add tags</p>

        @if (!empty($form->tags))
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($form->tags as $index => $tag)
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-3 py-1 text-sm text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                        {{ $tag }}
                        <button type="button" wire:click="removeTag({{ $index }})"
                            class="hover:text-zinc-900 dark:hover:text-white">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between border-t border-zinc-200 pt-6 dark:border-zinc-700">
        <a href="{{ route('customers.index') }}" wire:navigate
            class="text-sm font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">
            Cancel
        </a>

        <button type="submit" wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 disabled:opacity-50 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 dark:focus:ring-white">
            <span wire:loading.remove>{{ $action }} Customer</span>
            <span wire:loading>
                <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>
        </button>
    </div>
</form>
