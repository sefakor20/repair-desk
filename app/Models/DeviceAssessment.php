<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssessmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAssessment extends Model
{
    protected $fillable = [
        'device_id',
        'ticket_id',
        'type',
        'assessment_data',
        'photos',
        'assessed_by',
        'assessed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssessmentType::class,
            'assessment_data' => 'array',
            'photos' => 'array',
            'assessed_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function scopeCheckIn($query)
    {
        return $query->where('type', AssessmentType::CheckIn->value);
    }

    public function scopeCheckOut($query)
    {
        return $query->where('type', AssessmentType::CheckOut->value);
    }

    public function getRating(string $category): ?int
    {
        return $this->assessment_data[$category]['rating'] ?? null;
    }

    public function getNotes(string $category): ?string
    {
        return $this->assessment_data[$category]['notes'] ?? null;
    }

    public function getCategoryPhotos(string $category): array
    {
        return $this->assessment_data[$category]['photos'] ?? [];
    }
}
