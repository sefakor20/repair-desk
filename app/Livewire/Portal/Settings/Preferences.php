<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Settings;

use App\Models\{Customer, CustomerPreference};
use Illuminate\View\View;
use Livewire\{Attributes\Layout, Component};

#[Layout('components.layouts.portal-fullpage')]
class Preferences extends Component
{
    public Customer $customer;

    public CustomerPreference $preferences;

    public bool $notify_points_earned = true;

    public bool $notify_reward_available = true;

    public bool $notify_tier_upgrade = true;

    public bool $notify_points_expiring = true;

    public bool $notify_referral_success = true;

    public bool $marketing_emails = false;

    public bool $newsletter = false;

    // SMS Preferences
    public bool $sms_enabled = true;

    public bool $sms_ticket_updates = true;

    public bool $sms_repair_completed = true;

    public bool $sms_invoice_reminders = true;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        // Get or create preferences
        $this->preferences = $customer->preferences()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'notify_points_earned' => true,
                'notify_reward_available' => true,
                'notify_tier_upgrade' => true,
                'notify_points_expiring' => true,
                'notify_referral_success' => true,
                'marketing_emails' => false,
                'newsletter' => false,
                'sms_enabled' => true,
                'sms_ticket_updates' => true,
                'sms_repair_completed' => true,
                'sms_invoice_reminders' => true,
            ],
        );

        // Populate form
        $this->notify_points_earned = $this->preferences->notify_points_earned;
        $this->notify_reward_available = $this->preferences->notify_reward_available;
        $this->notify_tier_upgrade = $this->preferences->notify_tier_upgrade;
        $this->notify_points_expiring = $this->preferences->notify_points_expiring;
        $this->notify_referral_success = $this->preferences->notify_referral_success;
        $this->marketing_emails = $this->preferences->marketing_emails;
        $this->newsletter = $this->preferences->newsletter;
        $this->sms_enabled = $this->preferences->sms_enabled;
        $this->sms_ticket_updates = $this->preferences->sms_ticket_updates;
        $this->sms_repair_completed = $this->preferences->sms_repair_completed;
        $this->sms_invoice_reminders = $this->preferences->sms_invoice_reminders;
    }

    public function save(): void
    {
        $this->preferences->update([
            'notify_points_earned' => $this->notify_points_earned,
            'notify_reward_available' => $this->notify_reward_available,
            'notify_tier_upgrade' => $this->notify_tier_upgrade,
            'notify_points_expiring' => $this->notify_points_expiring,
            'notify_referral_success' => $this->notify_referral_success,
            'marketing_emails' => $this->marketing_emails,
            'newsletter' => $this->newsletter,
            'sms_enabled' => $this->sms_enabled,
            'sms_ticket_updates' => $this->sms_ticket_updates,
            'sms_repair_completed' => $this->sms_repair_completed,
            'sms_invoice_reminders' => $this->sms_invoice_reminders,
        ]);

        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Preferences updated successfully!',
        );
    }

    public function render(): View
    {
        return view('livewire.portal.settings.preferences');
    }
}
