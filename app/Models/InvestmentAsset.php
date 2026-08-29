<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_name',
        'asset_type',
        'quantity',
        'purchase_price',
        'current_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'purchase_price' => 'decimal:2',
            'current_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalValueAttribute(): float
    {
        return (float) ($this->quantity * $this->current_price);
    }

    public function getTotalProfitLossAttribute(): float
    {
        return (float) (($this->current_price - $this->purchase_price) * $this->quantity);
    }
}
