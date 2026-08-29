<?php

namespace Database\Seeders;

use App\Models\QuestPool;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestPoolSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        $pools = [
            [
                'name' => 'IDX Stock Averaging Down (BBCA/BMRI)',
                'slug' => 'idx-stock-averaging-down',
                'category' => 'investment',
                'target_amount' => 15000000.00,
                'current_amount' => 3500000.00,
                'allocated_ap' => 350,
            ],
            [
                'name' => 'CB150R Major Service & Gear Set',
                'slug' => 'cb150r-maintenance-gear',
                'category' => 'vehicle',
                'target_amount' => 2500000.00,
                'current_amount' => 1200000.00,
                'allocated_ap' => 120,
            ],
            [
                'name' => 'Emergency Gold Vault Reserve',
                'slug' => 'emergency-gold-vault',
                'category' => 'emergency',
                'target_amount' => 20000000.00,
                'current_amount' => 5000000.00,
                'allocated_ap' => 500,
            ],
        ];

        foreach ($pools as $poolData) {
            QuestPool::firstOrCreate(
                ['user_id' => $user->id, 'slug' => $poolData['slug']],
                array_merge($poolData, ['user_id' => $user->id])
            );
        }
    }
}
