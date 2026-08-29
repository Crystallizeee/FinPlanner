<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\StreakPenaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreakPenaltyInactivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_loses_streak_and_gets_penalized_locking_analytics_route(): void
    {
        $user = User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'current_streak' => 5,
            'is_penalized' => false,
            'last_activity_at' => now()->subHours(50), // Inactive for > 24h
        ]);

        /** @var StreakPenaltyService $streakService */
        $streakService = app(StreakPenaltyService::class);
        $streakService->evaluateInactivityPenalties();

        $user->refresh();

        $this->assertEquals(0, $user->current_streak);
        $this->assertTrue($user->is_penalized);

        // Verify Middleware locks analytics page for penalized user
        $response = $this->actingAs($user)->get('/analytics');
        $response->assertStatus(403);
        $response->assertSee('ACCESS LOCKED BY FINANCIAL DISCIPLINE SYSTEM');
    }
}
