<div>
    @if (session('success'))
        <flux:callout variant="success" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <div>
            <flux:heading size="lg">Device Photos</flux:heading>
            <flux:text class="mt-1 text-sm">Upload and manage photos for condition assessment</flux:text>
        </div>
        @can('update', $device)
            <flux:button wire:click="openUploadModal" icon="camera" variant="primary">
                Upload Photos
            </flux:button>
        @endcan
    </div>

    @if ($devicePhotos->isEmpty())
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <flux:icon.camera class="mx-auto size-12 text-zinc-400" />
            <flux:heading size="lg" class="mt-4">No photos yet</flux:heading>
            <flux:text class="mt-2">Upload photos to document device condition</flux:text>
            @can('update', $device)
                <flux:button wire:click="openUploadModal" variant="primary" class="mt-4">
                    Upload Photos
                </flux:button>
            @endcan
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($devicePhotos as $photo)
                <div class="group relative overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="aspect-square">
                        <img src="{{ $photo->photo_url }}" alt="{{ $photo->type }}"
                            class="size-full object-cover transition-transform group-hover:scale-105" />
                    </div>
                    <div class="bg-white p-3 dark:bg-zinc-800">
                        <div class="mb-2 flex items-center justify-between">
                            <flux:badge
                                variant="{{ $photo->type === 'damage' ? 'danger' : ($photo->type === 'condition' ? 'primary' : 'success') }}">
                                {{ ucfirst($photo->type) }}
                            </flux:badge>
                            @can('update', $device)
                                <flux:button wire:click="confirmDelete('{{ $photo->id }}')" variant="ghost"
                                    size="xs" icon="trash" class="text-red-600 hover:text-red-700">
                                </flux:button>
                            @endcan
                        </div>
                        @if ($photo->description)
                            <flux:text class="text-sm">{{ $photo->description }}</flux:text>
                        @endif
                        <div class="mt-2 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                            @if ($photo->uploadedBy)
                                <span>{{ $photo->uploadedBy->name }}</span>
                                <span>•</span>
                            @endif
                            <span>{{ $photo->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Upload Modal --}}
    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="closeUploadModal">
                </div>

                <div
                    class="relative w-full max-w-2xl rounded-lg border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-6 flex items-center justify-between">
                        <flux:heading size="lg">Upload Device Photos</flux:heading>
                        <flux:button wire:click="closeUploadModal" variant="ghost" size="sm" icon="x-mark">
                        </flux:button>
                    </div>

                    <form wire:submit="uploadPhotos">
                        <div class="space-y-6">
                            <flux:field>
                                <flux:label>Photos *</flux:label>
                                <input type="file" wire:model="photos" multiple accept="image/*"
                                    class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:text-zinc-400 dark:file:bg-blue-900 dark:file:text-blue-300 dark:hover:file:bg-blue-800" />
                                <flux:error name="photos" />
                                <flux:error name="photos.*" />
                                <flux:text class="mt-1 text-xs">Maximum 5 photos, 5MB each. JPG, PNG, or WebP
                                </flux:text>

                                @if ($photos)
                                    <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                        @foreach ($photos as $photo)
                                            <div class="relative aspect-square overflow-hidden rounded-lg border">
                                                <img src="{{ $photo->temporaryUrl() }}" alt="Preview"
                                                    class="size-full object-cover" />
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </flux:field>

                            <flux:field>
                                <flux:label>Photo Type *</flux:label>
                                <flux:select wire:model="photoType" :invalid="$errors->has('photoType')">
                                    <option value="condition">Condition - General device condition</option>
                                    <option value="damage">Damage - Specific damage or issues</option>
                                    <option value="before">Before - Before repair</option>
                                    <option value="after">After - After repair</option>
                                </flux:select>
                                <flux:error name="photoType" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Description</flux:label>
                                <flux:textarea wire:model="photoDescription" rows="3"
                                    placeholder="Optional: Describe what these photos show..."
                                    :invalid="$errors->has('photoDescription')" />
                                <flux:error name="photoDescription" />
                            </flux:field>
                        </div>

                        <div class="mt-6 flex gap-2">
                            <flux:button type="submit" variant="primary" wire:loading.attr="disabled"
                                wire:target="photos, uploadPhotos">
                                <span wire:loading.remove wire:target="uploadPhotos">Upload Photos</span>
                                <span wire:loading wire:target="uploadPhotos">Uploading...</span>
                            </flux:button>
                            <flux:button type="button" wire:click="closeUploadModal" variant="ghost">
                                Cancel
                            </flux:button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($photoToDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="cancelDelete"></div>

                <div
                    class="relative w-full max-w-md rounded-lg border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-4">Delete Photo</flux:heading>
                    <flux:text class="mb-6">Are you sure you want to delete this photo? This action cannot be undone.
                    </flux:text>

                    <div class="flex gap-2">
                        <flux:button wire:click="deletePhoto" variant="danger" wire:loading.attr="disabled"
                            wire:target="deletePhoto">
                            <span wire:loading.remove wire:target="deletePhoto">Delete</span>
                            <span wire:loading wire:target="deletePhoto">Deleting...</span>
                        </flux:button>
                        <flux:button wire:click="cancelDelete" variant="ghost">
                            Cancel
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
