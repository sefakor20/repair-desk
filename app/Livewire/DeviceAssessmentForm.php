<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\AssessmentType;
use App\Models\Device;
use App\Models\DeviceAssessment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class DeviceAssessmentForm extends Component
{
    use WithFileUploads;

    public ?Device $device = null;

    public ?Ticket $ticket = null;

    public AssessmentType $type = AssessmentType::CheckIn;

    public array $assessmentData = [];

    public array $tempPhotos = [];

    // Assessment categories
    public array $categories = [
        'screen' => 'Screen/Display',
        'body' => 'Body/Housing',
        'buttons' => 'Buttons/Controls',
        'ports' => 'Ports/Connectors',
        'camera' => 'Camera',
        'battery' => 'Battery',
        'functionality' => 'General Functionality',
        'accessories' => 'Accessories Included',
    ];

    public bool $readOnly = false;

    public function mount(?Device $device = null, ?Ticket $ticket = null, ?AssessmentType $type = null, ?DeviceAssessment $existingAssessment = null): void
    {
        $this->device = $device;
        $this->ticket = $ticket;

        if ($type) {
            $this->type = $type;
        }

        if ($existingAssessment) {
            $this->loadExistingAssessment($existingAssessment);
            $this->readOnly = true;
        } else {
            $this->initializeAssessmentData();
        }
    }

    protected function initializeAssessmentData(): void
    {
        foreach (array_keys($this->categories) as $category) {
            $this->assessmentData[$category] = [
                'rating' => null,
                'notes' => '',
                'photos' => [],
            ];
        }
    }

    protected function loadExistingAssessment(DeviceAssessment $assessment): void
    {
        $this->assessmentData = $assessment->assessment_data ?? [];

        // Ensure all categories exist
        foreach (array_keys($this->categories) as $category) {
            if (! isset($this->assessmentData[$category])) {
                $this->assessmentData[$category] = [
                    'rating' => null,
                    'notes' => '',
                    'photos' => [],
                ];
            }
        }
    }

    public function updatedTempPhotos(mixed $value, string $key): void // @phpstan-ignore-line
    {
        // Extract category from key (e.g., "screen.0" -> "screen")
        $category = explode('.', $key)[0];

        if (isset($this->tempPhotos[$category]) && is_array($this->tempPhotos[$category])) {
            foreach ($this->tempPhotos[$category] as $photo) {
                if ($photo) {
                    $path = $photo->store('assessments', 'public');
                    $this->assessmentData[$category]['photos'][] = $path;
                }
            }
            $this->tempPhotos[$category] = [];
        }
    }

    public function removePhoto(string $category, int $index): void
    {
        if (isset($this->assessmentData[$category]['photos'][$index])) {
            $path = $this->assessmentData[$category]['photos'][$index];

            // Delete from storage
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Remove from array
            unset($this->assessmentData[$category]['photos'][$index]);
            $this->assessmentData[$category]['photos'] = array_values($this->assessmentData[$category]['photos']);
        }
    }

    public function save(): ?DeviceAssessment
    {
        $this->validate([
            'assessmentData.*.rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'assessmentData.*.notes' => ['nullable', 'string'],
        ]);

        if (! $this->device) {
            return null;
        }

        $assessment = DeviceAssessment::create([
            'device_id' => $this->device->id,
            'ticket_id' => $this->ticket?->id,
            'type' => $this->type,
            'assessment_data' => $this->assessmentData,
            'assessed_by' => auth()->id(),
            'assessed_at' => now(),
        ]);

        $this->dispatch('assessment-saved', assessmentId: $assessment->id);

        return $assessment;
    }

    public function getAssessmentData(): array
    {
        return $this->assessmentData;
    }

    public function updated($propertyName): void
    {
        // Emit assessment data changes to parent component
        if (str_starts_with($propertyName, 'assessmentData')) {
            $this->dispatch('assessment-data-updated', assessmentData: $this->assessmentData);
        }
    }

    public function getOverallConditionProperty(): ?string
    {
        $ratings = array_filter(array_column($this->assessmentData, 'rating'));

        if (empty($ratings)) {
            return null;
        }

        $average = array_sum($ratings) / count($ratings);

        return match (true) {
            $average >= 4.5 => 'Excellent',
            $average >= 3.5 => 'Good',
            $average >= 2.5 => 'Fair',
            $average >= 1.5 => 'Poor',
            default => 'Very Poor',
        };
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.device-assessment-form');
    }
}
