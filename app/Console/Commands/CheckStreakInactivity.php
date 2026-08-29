<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StreakPenaltyService;
use Illuminate\Console\Command;

class CheckStreakInactivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-streak-inactivity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user financial activity streaks and enforce penalty lock for inactive users';

    /**
     * Execute the console command.
     */
    public function handle(StreakPenaltyService $streakPenaltyService): int
    {
        $this->info('Evaluating user streak inactivity penalties...');

        $penalized = $streakPenaltyService->evaluateInactivityPenalties();

        $this->info("Streak inactivity check completed. Total users penalized: {$penalized}");

        return Command::SUCCESS;
    }
}
