<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BankWebhookProcessorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MutasiWebhookController extends Controller
{
    public function __construct(
        protected BankWebhookProcessorService $webhookService
    ) {}

    /**
     * Boilerplate webhook receiver for integrating automated bank statement parsing (API Mutasi).
     */
    public function handle(Request $request): JsonResponse
    {
        // Get target user (e.g. system default user or user identified by webhook query/header)
        $userId = $request->query('user_id') ?? $request->input('user_id');
        $user = $userId ? User::find($userId) : User::first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No target user found to attribute bank statement transaction',
            ], 404);
        }

        $result = $this->webhookService->handleWebhook($request, $user);

        $status = $result['success'] ? 200 : 400;

        return response()->json($result, $status);
    }
}
