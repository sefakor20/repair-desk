<div>
    <div class="space-y-6">
        <!-- Overall Condition Summary (if ratings exist) -->
        @if ($overallCondition)
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <flux:icon.information-circle class="text-blue-600 dark:text-blue-400" />
                    <div>
                        <flux:heading size="sm">Overall Condition</flux:heading>
                        <flux:text>{{ $overallCondition }}</flux:text>
                    </div>
                </div>
            </div>
        @endif

        <!-- Assessment Categories -->
        @foreach ($categories as $categoryKey => $categoryLabel)
            <div wire:key="category-{{ $categoryKey }}"
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="md">{{ $categoryLabel }}</flux:heading>

                    @if (isset($assessmentData[$categoryKey]['rating']) && $assessmentData[$categoryKey]['rating'])
                        <flux:badge variant="primary" size="sm">
                            {{ $assessmentData[$categoryKey]['rating'] }}/5
                        </flux:badge>
                    @endif
                </div>

                @if (!$readOnly)
                    <!-- Rating Stars -->
                    <div>
                        <flux:text variant="muted" class="mb-2">Condition Rating</flux:text>
                        <div class="flex gap-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    wire:click="$set('assessmentData.{{ $categoryKey }}.rating', {{ $i }})"
                                    class="transition-colors hover:scale-110 transform"
                                    @if ($readOnly) disabled @endif>
                                    <svg class="w-8 h-8 {{ isset($assessmentData[$categoryKey]['rating']) && $assessmentData[$categoryKey]['rating'] >= $i ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300 dark:text-gray-600' }}"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </button>
                            @endfor
                        </div>
                        <flux:description>1 = Very Poor, 5 = Excellent</flux:description>
                    </div>

                    <!-- Notes -->
                    <div>
                        <flux:textarea wire:model="assessmentData.{{ $categoryKey }}.notes" label="Notes"
                            placeholder="Describe any issues, damage, or notable conditions..."
                            rows="3" />
                    </div>

                    <!-- Photo Upload -->
                    <div>
                        <flux:text variant="muted" class="mb-2">Photos</flux:text>

                        <!-- Existing Photos -->
                        @if (!empty($assessmentData[$categoryKey]['photos']))
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-4">
                                @foreach ($assessmentData[$categoryKey]['photos'] as $index => $photo)
                                    <div class="relative group">
                                        <img src="{{ Storage::url($photo) }}" alt="Assessment photo"
                                            class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                        <button type="button"
                                            wire:click="removePhoto('{{ $categoryKey }}', {{ $index }})"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Upload Button -->
                        <div class="flex items-center gap-2">
                            <input type="file" wire:model="tempPhotos.{{ $categoryKey }}" multiple
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/20 dark:file:text-blue-400" />
                        </div>

                        @error('tempPhotos.' . $categoryKey)
                            <flux:error>{{ $message }}</flux:error>
                        @enderror

                        <flux:description>Upload images showing the condition of this component</flux:description>

                        <!-- Loading indicator for photo uploads -->
                        <div wire:loading wire:target="tempPhotos.{{ $categoryKey }}"
                            class="mt-2 text-sm text-blue-600 dark:text-blue-400">
                            Uploading photos...
                        </div>
                    </div>
                @else
                    <!-- Read-only view -->
                    <div class="space-y-3">
                        @if (isset($assessmentData[$categoryKey]['notes']) && $assessmentData[$categoryKey]['notes'])
                            <div>
                                <flux:text variant="muted" class="text-sm">Notes:</flux:text>
                                <flux:text>{{ $assessmentData[$categoryKey]['notes'] }}</flux:text>
                            </div>
                        @endif

                        @if (!empty($assessmentData[$categoryKey]['photos']))
                            <div>
                                <flux:text variant="muted" class="text-sm mb-2">Photos:</flux:text>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                    @foreach ($assessmentData[$categoryKey]['photos'] as $photo)
                                        <img src="{{ Storage::url($photo) }}" alt="Assessment photo"
                                            class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

        <!-- Action Buttons (if not read-only) -->
        @if (!$readOnly)
            <div class="flex justify-end gap-3 pt-4">
                <flux:button type="button" variant="ghost" wire:click="$dispatch('cancel-assessment')">
                    Cancel
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="save">
                    Save Assessment
                </flux:button>
            </div>
        @endif
    </div>
</div>
