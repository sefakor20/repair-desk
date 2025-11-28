<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;

class Contact extends Model
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory;
    use HasUlids;
    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'position',
        'address',
        'notes',
        'tags',
        'is_active',
        'last_contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_active' => 'boolean',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function getNameAttribute(): string
    {
        return mb_trim("{$this->first_name} {$this->last_name}");
    }

    public function getFullContactInfoAttribute(): string
    {
        $info = $this->name;

        if ($this->company) {
            $info .= " ({$this->company}";
            if ($this->position) {
                $info .= " - {$this->position}";
            }
            $info .= ")";
        }

        return $info;
    }

    public function smsDeliveryLogs(): MorphMany
    {
        return $this->morphMany(SmsDeliveryLog::class, 'notifiable');
    }

    public function canReceiveSms(): bool
    {
        return $this->is_active && !empty($this->phone);
    }

    public function markAsContacted(): void
    {
        $this->update(['last_contacted_at' => now()]);
    }

    /**
     * Scope to get only active contacts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get contacts with phone numbers
     */
    public function scopeWithPhone($query)
    {
        return $query->whereNotNull('phone');
    }
}
