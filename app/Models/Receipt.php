<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'receipt_number',
        'merchant_name',
        'total_amount',
        'transaction_date',
        'image_path',
        'ocr_raw_text',
        'ocr_status',
        'confidence_score',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'transaction_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function expenseTransaction(): HasMany
    {
        return $this->hasMany(ExpenseTransaction::class);
    }
}
