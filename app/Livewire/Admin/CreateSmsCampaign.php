<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Jobs\ProcessSmsCampaign;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\SmsCampaign;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CreateSmsCampaign extends Component
{
    public string $name = '';

    public string $message = '';

    public string $segmentType = 'all';

    public int $recentDays = 30;

    public array $selectedContactIds = [];

    public bool $scheduleForLater = false;

    public string $scheduledDate = '';

    public string $scheduledTime = '';

    public ?int $estimatedRecipients = null;

    public ?float $estimatedCost = null;

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
            'segmentType' => ['required', Rule::in(['all', 'recent', 'active', 'contacts'])],
            'recentDays' => ['required_if:segmentType,recent', 'integer', 'min:1', 'max:365'],
            'scheduleForLater' => ['boolean'],
            'scheduledDate' => ['required_if:scheduleForLater,true', 'date', 'after_or_equal:today'],
            'scheduledTime' => ['required_if:scheduleForLater,true'],
        ];

        // Only add contact validation if segment type is contacts
        if ($this->segmentType === 'contacts') {
            $rules['selectedContactIds'] = ['required', 'array', 'min:1'];
            $rules['selectedContactIds.*'] = ['exists:contacts,id'];
        }

        return $rules;
    }

    public function mount(): void
    {
        Gate::authorize('create', SmsCampaign::class);
        $this->calculateEstimate();
    }

    public function updatedSelectedContactIds(): void
    {
        // Immediate recalculation for contact selection
        if ($this->segmentType === 'contacts') {
            $this->calculateEstimate();
        }
    }

    public function updated($propertyName): void
    {
        // Skip validation and recalculation for computed properties
        if (in_array($propertyName, ['estimatedRecipients', 'estimatedCost'])) {
            return;
        }

        // Handle specific property updates
        if ($propertyName === 'selectedContactIds') {
            // Validate only if we're in contacts mode
            if ($this->segmentType === 'contacts') {
                $this->validateOnly($propertyName);
            }
            $this->calculateEstimate();
            return;
        }

        // Validate the property
        $this->validateOnly($propertyName);

        // Recalculate estimate for relevant properties
        if (in_array($propertyName, ['segmentType', 'recentDays', 'message'])) {
            $this->calculateEstimate();
        }
    }

    public function calculateEstimate(): void
    {
        // Reset to null first for immediate UI feedback
        $this->estimatedRecipients = null;
        $this->estimatedCost = null;

        // Calculate recipients based on segment type
        if ($this->segmentType === 'contacts') {
            $this->estimatedRecipients = count($this->selectedContactIds);
        } else {
            $query = Customer::whereNotNull('phone');

            match ($this->segmentType) {
                'recent' => $query->where('created_at', '>=', now()->subDays($this->recentDays)),
                'active' => $query->whereHas('tickets', function ($q) {
                    $q->where('created_at', '>=', now()->subMonths(3));
                }),
                default => null,
            };

            $this->estimatedRecipients = $query->count();
        }

        // Calculate estimated cost
        if ($this->message && $this->estimatedRecipients > 0) {
            $smsService = new SmsService();
            $segments = $smsService->calculateSegments($this->message);
            $costPerSegment = config('services.texttango.cost_per_segment', 0.12);
            $this->estimatedCost = $this->estimatedRecipients * $segments * $costPerSegment;
        } else {
            $this->estimatedCost = null;
        }

        // Force UI update
        $this->dispatch('estimate-updated');
    }

    public function getSegmentCount(): int
    {
        if (! $this->message) {
            return 1;
        }

        $smsService = new SmsService();
        return $smsService->calculateSegments($this->message);
    }

    public function getAvailableContactsProperty()
    {
        return Contact::active()
            ->withPhone()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->full_contact_info,
                    'phone' => $contact->phone,
                ];
            });
    }

    public function create(): void
    {
        Gate::authorize('create', SmsCampaign::class);

        $this->validate();

        // Determine recipient type and prepare data
        $recipientType = $this->segmentType === 'contacts' ? 'contacts' : 'customers';
        $segmentRules = null;
        $contactIds = null;

        if ($this->segmentType === 'contacts') {
            $contactIds = $this->selectedContactIds;
        } else {
            $segmentRules = match ($this->segmentType) {
                'all' => ['type' => 'all'],
                'recent' => ['type' => 'recent', 'days' => $this->recentDays],
                'active' => ['type' => 'active'],
            };
        }

        $scheduledAt = null;
        if ($this->scheduleForLater && $this->scheduledDate && $this->scheduledTime) {
            $scheduledAt = \Carbon\Carbon::parse($this->scheduledDate . ' ' . $this->scheduledTime);
        }

        $campaign = SmsCampaign::create([
            'name' => $this->name,
            'message' => $this->message,
            'status' => $scheduledAt ? 'scheduled' : 'draft',
            'recipient_type' => $recipientType,
            'segment_rules' => $segmentRules,
            'contact_ids' => $contactIds,
            'scheduled_at' => $scheduledAt,
            'total_recipients' => $this->estimatedRecipients,
            'estimated_cost' => $this->estimatedCost,
            'created_by' => Auth::id(),
        ]);

        // Dispatch job immediately if not scheduled
        if (! $scheduledAt) {
            ProcessSmsCampaign::dispatch($campaign);
        }

        session()->flash('success', 'Campaign ' . ($scheduledAt ? 'scheduled' : 'started') . ' successfully!');

        $this->redirect(route('admin.sms-campaigns'));
    }

    #[Layout('components.layouts.app')]
    public function render(): View
    {
        return view('livewire.admin.create-sms-campaign');
    }
}
