<div>
    <x-layouts.portal-content :customer="$customer" title="My Devices">
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl" class="mb-2">My Devices</flux:heading>
                    <flux:text>Manage and view your registered devices</flux:text>
                </div>
            </div>

            {{-- Search --}}
            <div class="flex gap-4">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search devices..." type="search" />
                </div>

                @if ($search)
                    <flux:button wire:click="clearSearch" variant="ghost">
                        Clear Search
                    </flux:button>
                @endif
            </div>

            {{-- Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm mb-1">Total Devices</p>
                            <p class="text-3xl font-bold">{{ $customer->devices()->count() }}</p>
                        </div>
                        <flux:icon.device-phone-mobile class="w-12 h-12 text-purple-200 opacity-50" />
                    </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-indigo-100 text-sm mb-1">Total Repairs</p>
                            <p class="text-3xl font-bold">{{ $customer->tickets()->count() }}</p>
                        </div>
                        <flux:icon.wrench-screwdriver class="w-12 h-12 text-indigo-200 opacity-50" />
                    </div>
                </div>
            </div>

            {{-- Devices Grid --}}
            @if ($devices->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($devices as $device)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition">
                            <div class="p-6">
                                {{-- Device Icon --}}
                                <div
                                    class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg mb-4">
                                    <flux:icon.device-phone-mobile class="w-8 h-8 text-white" />
                                </div>

                                {{-- Device Details --}}
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                    {{ $device->brand }} {{ $device->model }}
                                </h3>

                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $device->type }}</p>

                                {{-- Device Info --}}
                                <div class="space-y-2 mb-4">
                                    @if ($device->serial_number)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Serial:</span>
                                            <span
                                                class="font-mono text-gray-900 dark:text-white">{{ $device->serial_number }}</span>
                                        </div>
                                    @endif

                                    @if ($device->imei)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">IMEI:</span>
                                            <span
                                                class="font-mono text-gray-900 dark:text-white">{{ $device->imei }}</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Repairs:</span>
                                        <flux:badge variant="{{ $device->tickets_count > 0 ? 'info' : 'secondary' }}">
                                            {{ $device->tickets_count }}
                                        </flux:badge>
                                    </div>

                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Registered:</span>
                                        <span
                                            class="text-gray-600 dark:text-gray-300">{{ $device->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>

                                {{-- Action Button --}}
                                <flux:button
                                    href="{{ route('portal.devices.show', ['customer' => $customer->id, 'token' => $customer->portal_access_token, 'device' => $device->id]) }}"
                                    variant="outline" class="w-full">
                                    View Details
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $devices->links() }}
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                    <flux:icon.device-phone-mobile class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No devices found</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        @if ($search)
                            Try adjusting your search to find what you're looking for.
                        @else
                            You don't have any registered devices yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </x-layouts.portal-content>
</div>
