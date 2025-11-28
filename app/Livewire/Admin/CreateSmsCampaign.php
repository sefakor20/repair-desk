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

    public int $minTickets = 1;

    public float $minSpent = 0.0;

    public array $selectedContactIds = [];

    public bool $scheduleForLater = false;

    public string $scheduledDate = '';

    public string $scheduledTime = '';

    public ?int $estimatedRecipients = null;

    public ?float $estimatedCost = null;

    public bool $showPreview = false;

    public string $testPhoneNumber = '';

    public bool $showTestSend = false;

    public string $previewMessage = '';

    public string $selectedTemplate = '';

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
            'segmentType' => ['required', Rule::in(['all', 'recent', 'active', 'contacts', 'high_value', 'frequent_customers'])],
            'recentDays' => ['required_if:segmentType,recent', 'integer', 'min:1', 'max:365'],
            'minTickets' => ['required_if:segmentType,frequent_customers', 'integer', 'min:1', 'max:100'],
            'minSpent' => ['required_if:segmentType,high_value', 'numeric', 'min:0'],
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
        if (in_array($propertyName, ['segmentType', 'recentDays', 'message', 'minTickets', 'minSpent'])) {
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
                'high_value' => $query->whereIn('id', function ($subquery) {
                    $subquery->select('customer_id')
                        ->from('invoices')
                        ->groupBy('customer_id')
                        ->havingRaw('SUM(total) >= ?', [$this->minSpent]);
                }),
                'frequent_customers' => $query->whereIn('id', function ($subquery) {
                    $subquery->select('customer_id')
                        ->from('tickets')
                        ->where('branch_id', session('current_branch_id'))
                        ->groupBy('customer_id')
                        ->havingRaw('COUNT(*) >= ?', [$this->minTickets]);
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

    public function showPreviewModal(): void
    {
        if (!$this->message) {
            return;
        }

        $this->generatePreviewMessage();
        $this->showPreview = true;
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->previewMessage = '';
    }

    public function showTestSendModal(): void
    {
        if (!$this->message) {
            return;
        }

        $this->generatePreviewMessage();
        $this->showTestSend = true;
    }

    public function closeTestSend(): void
    {
        $this->showTestSend = false;
        $this->testPhoneNumber = '';
        $this->previewMessage = '';
    }

    public function sendTest(): void
    {
        $this->validate([
            'testPhoneNumber' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'message' => ['required', 'string'],
        ]);

        $smsService = new SmsService();
        $success = $smsService->send(
            $this->testPhoneNumber,
            $this->previewMessage,
            null,
            'test_campaign',
        );

        if ($success) {
            session()->flash('success', 'Test message sent successfully!');
        } else {
            session()->flash('error', 'Failed to send test message. Please check your configuration.');
        }

        $this->closeTestSend();
    }

    private function generatePreviewMessage(): void
    {
        // For now, just use the message as-is
        // In the future, this could include variable replacement
        $this->previewMessage = $this->message;
    }

    public function getAvailableTemplatesProperty(): array
    {
        return [
            '' => 'No template (custom message)',
            'appointment_reminder' => 'Appointment Reminder - Hi {customer_name}, this is a reminder for your repair appointment on {date}. Please arrive 10 minutes early.',
            'repair_completed' => 'Repair Completed - Good news {customer_name}! Your {device} repair is complete. You can pick it up anytime during business hours.',
            'payment_reminder' => 'Payment Due - Hi {customer_name}, your repair bill of {amount} is ready for payment. Please visit us or pay online.',
            'status_update' => 'Status Update - Hi {customer_name}, your repair status has been updated to: {status}. We\'ll keep you posted on progress.',
            'promotional' => 'Special Offer - Enjoy 20% off your next repair service! Valid until {expiry_date}. Book now to secure your discount.',
            'pickup_ready' => 'Ready for Pickup - Hi {customer_name}, your repaired {device} is ready for collection. Store hours: Mon-Fri 9AM-6PM.',
        ];
    }

    public function selectTemplate(): void
    {
        if ($this->selectedTemplate && isset($this->availableTemplates[$this->selectedTemplate])) {
            $this->message = $this->availableTemplates[$this->selectedTemplate];
            $this->calculateEstimate();
        }
    }

    public function clearTemplate(): void
    {
        $this->selectedTemplate = '';
        $this->message = '';
        $this->calculateEstimate();
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
                'high_value' => ['type' => 'high_value', 'min_spent' => $this->minSpent],
                'frequent_customers' => ['type' => 'frequent_customers', 'min_tickets' => $this->minTickets],
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
