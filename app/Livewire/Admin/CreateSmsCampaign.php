<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Jobs\ProcessSmsCampaign;
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

    public bool $scheduleForLater = false;

    public string $scheduledDate = '';

    public string $scheduledTime = '';

    public ?int $estimatedRecipients = null;

    public ?float $estimatedCost = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
            'segmentType' => ['required', Rule::in(['all', 'recent', 'active'])],
            'recentDays' => ['required_if:segmentType,recent', 'integer', 'min:1', 'max:365'],
            'scheduleForLater' => ['boolean'],
            'scheduledDate' => ['required_if:scheduleForLater,true', 'date', 'after_or_equal:today'],
            'scheduledTime' => ['required_if:scheduleForLater,true'],
        ];
    }

    public function mount(): void
    {
        Gate::authorize('create', SmsCampaign::class);
        $this->calculateEstimate();
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);

        if (in_array($propertyName, ['segmentType', 'recentDays', 'message'])) {
            $this->calculateEstimate();
        }
    }

    public function calculateEstimate(): void
    {
        $query = Customer::whereNotNull('phone');

        match ($this->segmentType) {
            'recent' => $query->where('created_at', '>=', now()->subDays($this->recentDays)),
            'active' => $query->whereHas('tickets', function ($q) {
                $q->where('created_at', '>=', now()->subMonths(3));
            }),
            default => null,
        };

        $this->estimatedRecipients = $query->count();

        // Calculate estimated cost
        if ($this->message && $this->estimatedRecipients > 0) {
            $smsService = new SmsService();
            $segments = $smsService->calculateSegments($this->message);
            $costPerSegment = config('services.texttango.cost_per_segment', 0.0075);
            $this->estimatedCost = $this->estimatedRecipients * $segments * $costPerSegment;
        } else {
            $this->estimatedCost = null;
        }
    }

    public function getSegmentCount(): int
    {
        if (! $this->message) {
            return 1;
        }

        $smsService = new SmsService();
        return $smsService->calculateSegments($this->message);
    }

    public function create(): void
    {
        Gate::authorize('create', SmsCampaign::class);

        $this->validate();

        $segmentRules = match ($this->segmentType) {
            'all' => ['type' => 'all'],
            'recent' => ['type' => 'recent', 'days' => $this->recentDays],
            'active' => ['type' => 'active'],
        };

        $scheduledAt = null;
        if ($this->scheduleForLater && $this->scheduledDate && $this->scheduledTime) {
            $scheduledAt = \Carbon\Carbon::parse($this->scheduledDate . ' ' . $this->scheduledTime);
        }

        $campaign = SmsCampaign::create([
            'name' => $this->name,
            'message' => $this->message,
            'status' => $scheduledAt ? 'scheduled' : 'draft',
            'segment_rules' => $segmentRules,
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
