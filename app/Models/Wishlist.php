<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_name',
        'price',
        'category',
        'cooling_off_days',
        'unlock_at',
        'is_purchased',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cooling_off_days' => 'integer',
        'unlock_at' => 'datetime',
        'is_purchased' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUnlocked(): bool
    {
        return now()->greaterThanOrEqualTo($this->unlock_at);
    }

    public function getDaysRemaining(): int
    {
        if ($this->isUnlocked()) {
            return 0;
        }

        return (int) now()->diffInDays($this->unlock_at, false) + 1;
    }
}
