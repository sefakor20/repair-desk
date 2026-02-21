<?php

declare(strict_types=1);

namespace App\Livewire\Assessments;

use App\Enums\AssessmentType;
use App\Models\Device;
use App\Models\DeviceAssessment;
use App\Models\Ticket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Manage extends Component
{
    use AuthorizesRequests;

    public ?Device $device = null;

    public ?Ticket $ticket = null;

    public AssessmentType $type = AssessmentType::CheckIn;

    public ?DeviceAssessment $existingAssessment = null;

    public bool $showForm = false;

    public function mount(?string $deviceId = null, ?string $ticketId = null, ?string $assessmentType = null, ?int $assessmentId = null): void
    {
        if ($deviceId) {
            $this->device = Device::findOrFail($deviceId);
        }

        if ($ticketId) {
            $this->ticket = Ticket::findOrFail($ticketId);
            $this->device = $this->ticket->device;
        }

        if ($assessmentType) {
            $this->type = AssessmentType::from($assessmentType);
        }

        if ($assessmentId) {
            $this->existingAssessment = DeviceAssessment::findOrFail($assessmentId);
            $this->device = $this->existingAssessment->device;
            $this->ticket = $this->existingAssessment->ticket;
            $this->type = $this->existingAssessment->type;
        }

        if ($this->device || $this->ticket) {
            $this->showForm = true;
        }
    }

    #[Layout('components.layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        $assessments = null;

        if ($this->device) {
            $assessments = $this->device->assessments()
                ->with(['assessedBy', 'ticket'])
                ->latest('assessed_at')
                ->get();
        }

        return view('livewire.assessments.manage', [
            'assessments' => $assessments,
        ]);
    }
}
