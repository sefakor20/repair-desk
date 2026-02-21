<div>
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <flux:heading size="lg">Device Assessment</flux:heading>
                <flux:text variant="muted">
                    @if ($existingAssessment)
                        Viewing {{ $type->label() }} assessment
                    @elseif ($showForm)
                        Create {{ $type->label() }} assessment
                    @else
                        Assessment history
                    @endif
                </flux:text>
            </div>

            @if ($device && !$existingAssessment)
                <div class="flex gap-2">
                    <flux:button :href="route('devices.show', $device)" variant="ghost" icon="arrow-left" size="sm">
                        Back to Device
                    </flux:button>
                </div>
            @endif
        </div>

        @if ($device)
            <!-- Device Info Card -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <flux:text variant="muted" class="text-sm">Device</flux:text>
                        <flux:heading size="sm">{{ $device->device_name }}</flux:heading>
                    </div>
                    @if ($device->imei)
                        <div>
                            <flux:text variant="muted" class="text-sm">IMEI/Serial</flux:text>
                            <flux:text>{{ $device->imei }}</flux:text>
                        </div>
                    @endif
                    @if ($ticket)
                        <div>
                            <flux:text variant="muted" class="text-sm">Ticket</flux:text>
                            <flux:text>
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:underline">
                                    #{{ $ticket->ticket_number }}
                                </a>
                            </flux:text>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if ($showForm)
        <!-- Assessment Form -->
        <div class="max-w-4xl">
            <livewire:device-assessment-form :device="$device" :ticket="$ticket" :type="$type"
                :existingAssessment="$existingAssessment" :key="$existingAssessment?->id ?? 'new'" />
        </div>
    @else
        <!-- Assessment History -->
        <div class="max-w-4xl">
            @if ($assessments && $assessments->count() > 0)
                <div class="space-y-4">
                    @foreach ($assessments as $assessment)
                        <div
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <flux:badge
                                        :variant="$assessment->type === \App\Enums\AssessmentType::CheckIn ? 'primary' : 'success'">
                                        {{ $assessment->type->label() }}
                                    </flux:badge>
                                    <flux:text variant="muted">
                                        {{ $assessment->assessed_at->diffForHumans() }}
                                    </flux:text>
                                </div>
                                <flux:button :href="route('assessments.view', $assessment)" variant="ghost"
                                    size="sm">
                                    View Details
                                </flux:button>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <flux:text variant="muted">Assessed By</flux:text>
                                    <flux:text>{{ $assessment->assessedBy->name }}</flux:text>
                                </div>
                                @if ($assessment->ticket)
                                    <div>
                                        <flux:text variant="muted">Ticket</flux:text>
                                        <flux:text>
                                            <a href="{{ route('tickets.show', $assessment->ticket) }}"
                                                class="text-blue-600 hover:underline">
                                                #{{ $assessment->ticket->ticket_number }}
                                            </a>
                                        </flux:text>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-12">
                    <div class="text-center">
                        <flux:icon.document-magnifying-glass class="mx-auto h-12 w-12 text-gray-400" />
                        <flux:heading size="md" class="mt-4">No Assessments</flux:heading>
                        <flux:text variant="muted" class="mt-2">
                            No device assessments have been recorded yet.
                        </flux:text>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
