<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class StreakPenaltyService
{
    /**
     * Record daily activity when user uploads receipt or receives bank webhook.
     */
    public function recordUserActivity(User $user): void
    {
        $now = now();
        $lastActivity = $user->last_activity_at;

        if (! $lastActivity) {
            $user->current_streak = 1;
        } elseif ($lastActivity->isYesterday()) {
            $user->current_streak += 1;
        } elseif ($lastActivity->diffInHours($now) > 36) {
            // Streak was broken due to inactivity, start new streak
            $user->current_streak = 1;
        }

        if ($user->current_streak > $user->longest_streak) {
            $user->longest_streak = $user->current_streak;
        }

        $user->last_activity_at = $now;
        $user->is_penalized = false; // Lift penalty on activity
        $user->save();
    }

    /**
     * Check all users for inactivity penalties (called by scheduled task).
     */
    public function evaluateInactivityPenalties(): int
    {
        $penalizedCount = 0;
        $threshold = now()->subHours(24);

        User::query()->chunk(100, function ($users) use ($threshold, &$penalizedCount) {
            /** @var User $user */
            foreach ($users as $user) {
                if (! $user->last_activity_at || $user->last_activity_at->lt($threshold)) {
                    if (! $user->is_penalized) {
                        $user->is_penalized = true;
                        $user->current_streak = 0;
                        $user->save();
                        $penalizedCount++;
                    }
                }
            }
        });

        return $penalizedCount;
    }
}
