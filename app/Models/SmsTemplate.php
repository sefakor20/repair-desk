<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = [
        'name',
        'key',
        'message',
        'description',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Render the template with provided variables.
     */
    public function render(array $variables = []): string
    {
        $message = $this->message;

        foreach ($variables as $key => $value) {
            $message = str_replace('{{' . $key . '}}', (string) $value, $message);
        }

        return $message;
    }

    /**
     * Get available variable placeholders from the message.
     */
    public function extractVariables(): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $this->message, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Scope to get only active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get template by key.
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->where('is_active', true)->first();
    }
}
