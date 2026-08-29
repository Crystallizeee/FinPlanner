<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'lender',
        'total_amount',
        'remaining_amount',
        'interest_rate',
        'minimum_monthly_payment',
        'due_day',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'minimum_monthly_payment' => 'decimal:2',
            'due_day' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
