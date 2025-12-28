<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTour extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'tour_name',
        'is_completed',
        'is_skipped',
        'completed_steps',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'is_skipped' => 'boolean',
            'completed_steps' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markStepCompleted(string $stepId): void
    {
        $completedSteps = $this->completed_steps ?? [];

        if (!in_array($stepId, $completedSteps)) {
            $completedSteps[] = $stepId;
            $this->update(['completed_steps' => $completedSteps]);
        }

        if ($this->started_at === null) {
            $this->update(['started_at' => now()]);
        }
    }

    public function hasCompletedStep(string $stepId): bool
    {
        $completedSteps = $this->completed_steps ?? [];
        return in_array($stepId, $completedSteps);
    }

    public function markCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function markSkipped(): void
    {
        $this->update([
            'is_skipped' => true,
            'completed_at' => now(),
        ]);
    }

    public function getProgressPercentage(int $totalSteps): int
    {
        if ($totalSteps === 0) {
            return 0;
        }

        $completedCount = count($this->completed_steps ?? []);
        return (int) round(($completedCount / $totalSteps) * 100);
    }
}
