<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BankWebhookLog;
use App\Models\BudgetCycle;
use App\Models\ExpenseTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankWebhookProcessorService
{
    public function __construct(
        protected HpBudgetingService $hpService,
        protected StreakPenaltyService $streakService
    ) {}

    /**
     * Handle incoming API Mutasi bank webhook request.
     *
     * @return array{success: bool, message: string, transaction_id?: int}
     */
    public function handleWebhook(Request $request, User $user): array
    {
        $signature = $request->header('X-Api-Mutasi-Signature') ?? $request->header('X-Webhook-Token');
        $expectedSecret = config('services.api_mutasi.secret', 'financial_planner_secret_token');

        $rawPayload = $request->getContent();
        $payload = $request->all();

        // 1. Verify Secret / Signature
        if ($signature && ! hash_equals((string) $expectedSecret, (string) $signature)) {
            BankWebhookLog::create([
                'bank_name' => $payload['bank'] ?? 'API_MUTASI',
                'reference_id' => $payload['reference_id'] ?? null,
                'payload' => $rawPayload,
                'signature' => $signature,
                'status' => 'failed',
                'error_message' => 'Invalid signature / secret token verification',
            ]);

            return ['success' => false, 'message' => 'Unauthorized signature'];
        }

        $refId = $payload['reference_id'] ?? 'REF-' . time() . '-' . rand(1000, 9999);

        // 2. Check Idempotency (prevent duplicate transaction deduction)
        $existingLog = BankWebhookLog::where('reference_id', $refId)
            ->where('status', 'processed')
            ->first();

        if ($existingLog) {
            return ['success' => true, 'message' => 'Duplicate webhook request ignored (Idempotent)'];
        }

        $log = BankWebhookLog::create([
            'bank_name' => $payload['bank'] ?? 'BCA',
            'reference_id' => $refId,
            'payload' => json_encode($payload),
            'signature' => $signature,
            'status' => 'received',
        ]);

        $type = strtolower($payload['type'] ?? 'db'); // 'db' = debit (expense), 'cr' = credit
        $amount = (float) ($payload['amount'] ?? 0.00);

        if ($type !== 'db' || $amount <= 0) {
            $log->update(['status' => 'processed']);

            return ['success' => true, 'message' => 'Non-debit transaction logged, no budget deduction required'];
        }

        $budgetCycle = $user->getActiveBudgetCycle();
        if (! $budgetCycle) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'No active budget cycle found for target user',
            ]);

            return ['success' => false, 'message' => 'No active budget cycle for user'];
        }

        // 3. Create ExpenseTransaction & Recalculate HP inside Transaction
        $transaction = DB::transaction(function () use ($user, $budgetCycle, $payload, $refId, $amount, $log) {
            $merchant = $payload['merchant'] ?? $payload['description'] ?? 'Bank Debit Transfer';

            $trx = ExpenseTransaction::create([
                'user_id' => $user->id,
                'budget_cycle_id' => $budgetCycle->id,
                'source' => 'bank_webhook',
                'external_reference_id' => $refId,
                'merchant' => $merchant,
                'amount' => $amount,
                'description' => "Bank Auto Sync ({$payload['bank']}) - {$merchant}",
                'transaction_date' => isset($payload['transaction_date']) ? Carbon::parse($payload['transaction_date']) : now(),
                'is_verified' => true,
            ]);

            // Recalculate HP
            $this->hpService->recalculateCycleHp($budgetCycle);

            // Record user streak activity
            $this->streakService->recordUserActivity($user);

            $log->update(['status' => 'processed']);

            return $trx;
        });

        return [
            'success' => true,
            'message' => 'Bank statement webhook parsed and expense deducted successfully',
            'transaction_id' => $transaction->id,
        ];
    }
}
