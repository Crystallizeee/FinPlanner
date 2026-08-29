<?php

use App\Http\Controllers\Api\MutasiWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/webhooks/api-mutasi', [MutasiWebhookController::class, 'handle'])->name('api.webhooks.mutasi');
});
