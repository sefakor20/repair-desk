<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CashTransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashDrawerTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\CashDrawerTransactionFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'cash_drawer_session_id',
        'user_id',
        'pos_sale_id',
        'type',
        'amount',
        'reason',
        'notes',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'type' => CashTransactionType::class,
            'transaction_date' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CashDrawerSession::class, 'cash_drawer_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class);
    }
}
