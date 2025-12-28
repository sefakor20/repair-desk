<?php

declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Models\Device;
use App\Models\DevicePhoto;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ManagePhotos extends Component
{
    use WithFileUploads;

    public Device $device;

    public array $photos = [];

    public string $photoType = '';

    public string $photoDescription = '';

    public bool $showUploadModal = false;

    public bool $showDeleteModal = false;

    public ?string $photoToDelete = null;

    public function mount(Device $device): void
    {
        $this->authorize('update', $device);

        $this->device = $device;
    }

    public function openUploadModal(): void
    {
        $this->showUploadModal = true;
        $this->photos = [];
        $this->photoType = '';
        $this->photoDescription = '';
        $this->resetValidation();
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->photos = [];
        $this->photoType = '';
        $this->photoDescription = '';
        $this->resetValidation();
    }

    public function uploadPhotos(): void
    {
        $this->authorize('update', $this->device);

        $this->validate([
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['image', 'max:5120'], // 5MB max per image
            'photoType' => ['required', 'in:condition,damage,before,after'],
            'photoDescription' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($this->photos as $photo) {
            /** @var TemporaryUploadedFile $photo */
            $path = $photo->store('device-photos', 'public');

            DevicePhoto::create([
                'device_id' => $this->device->id,
                'photo_path' => $path,
                'type' => $this->photoType,
                'description' => $this->photoDescription ?: null,
                'uploaded_by' => auth()->id(),
            ]);
        }

        $this->device->refresh();

        $this->dispatch('toast', type: 'success', message: count($this->photos) . ' photo(s) uploaded successfully.');

        $this->closeUploadModal();
    }

    public function confirmDelete(string $photoId): void
    {
        $this->photoToDelete = $photoId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->photoToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deletePhoto(): void
    {
        $this->authorize('update', $this->device);

        if (! $this->photoToDelete) {
            return;
        }

        $photo = DevicePhoto::findOrFail($this->photoToDelete);

        if ($photo->device_id !== $this->device->id) {
            abort(403);
        }

        // Delete the file from storage
        if (Storage::disk('public')->exists($photo->photo_path)) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $photo->delete();

        $this->device->refresh();

        $this->dispatch('toast', type: 'success', message: 'Photo deleted successfully.');

        $this->photoToDelete = null;
        $this->showDeleteModal = false;
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.devices.manage-photos', [
            'devicePhotos' => $this->device->photos()->with('uploadedBy')->latest()->get(),
        ]);
    }
}
