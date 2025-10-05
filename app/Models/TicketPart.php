<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketPart extends Model
{
    protected $fillable = [
        'ticket_id',
        'inventory_item_id',
        'part_name',
        'quantity',
        'cost_price',
        'selling_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getTotalAttribute(): float
    {
        return (float) ($this->quantity * $this->selling_price);
    }
}
