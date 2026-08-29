<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ExpenseTransaction;
use App\Models\RecurringExpense;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessDueSubscriptions extends Command
{
    protected $signature = 'subscriptions:process-due';
    protected $description = 'Process automatic payment deduction for due recurring subscriptions and bills';

    public function handle(): int
    {
        $todayDay = (int) now()->format('j'); // 1-31
        $this->info("Scanning due recurring subscriptions for day: {$todayDay}...");

        $dueSubscriptions = RecurringExpense::where('is_active', true)
            ->where('due_day', '<=', $todayDay)
            ->where(function ($q) {
                $q->whereNull('last_paid_at')
                  ->orWhere('last_paid_at', '<', now()->startOfMonth());
            })
            ->get();

        if ($dueSubscriptions->isEmpty()) {
            $this->info("No due subscriptions to process today.");
            return Command::SUCCESS;
        }

        $processedCount = 0;

        foreach ($dueSubscriptions as $bill) {
            DB::transaction(function () use ($bill, &$processedCount) {
                $user = $bill->user;
                if (!$user) {
                    return;
                }

                $account = $bill->account ?? $user->accounts()->first();
                if ($account) {
                    $account->deductBalance((float) $bill->amount);
                }

                $activeCycle = $user->getActiveBudgetCycle() ?? $user->budgetCycles()->first() ?? $user->budgetCycles()->create([
                    'name' => 'Siklus Anggaran Utama',
                    'period_type' => 'monthly',
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'planned_budget' => 5000000,
                    'spent_amount' => 0,
                    'hp_level' => 100,
                    'status' => 'active',
                ]);

                ExpenseTransaction::create([
                    'user_id' => $user->id,
                    'budget_cycle_id' => $activeCycle->id,
                    'account_id' => $account?->id,
                    'source' => 'manual',
                    'merchant' => $bill->title,
                    'amount' => $bill->amount,
                    'category' => $bill->category,
                    'description' => "Auto-Debit Tagihan Rutin: {$bill->title}",
                    'transaction_date' => now(),
                    'is_verified' => true,
                ]);

                $bill->update(['last_paid_at' => now()]);
                $processedCount++;
            });
        }

        $this->info("Successfully processed {$processedCount} due subscription bills!");
        return Command::SUCCESS;
    }
}
