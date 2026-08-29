<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankWebhookLog extends Model
{
    use HasFactory;

    protected $table = 'bank_webhooks_log';

    protected $fillable = [
        'bank_name',
        'reference_id',
        'payload',
        'signature',
        'status',
        'error_message',
    ];
}
