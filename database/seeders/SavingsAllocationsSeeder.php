<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SavingsAllocationsSeeder extends Seeder
{
    public function run(): void
    {
        //  saving_goals — matches your actual migration table name
        $goals   = DB::table('saving_goals')->where('status', 'active')->get();
        $wallets = DB::table('wallets')->get()->keyBy('user_id');

        if ($goals->isEmpty()) {
            $this->command->warn('No active goals found.');
            return;
        }

        foreach ($goals as $goal) {
            $wallet = $wallets->get($goal->user_id);

            if (!$wallet || $wallet->balance <= 0) {
                continue;
            }

            // Contribute 10–30% of target, capped at 50% of wallet balance
            $contribution = round($goal->target_amount * (rand(10, 30) / 100), 2);
            $contribution = min($contribution, $wallet->balance * 0.5);

            if ($contribution < 1) continue;

            try {
                DB::statement('CALL add_to_savings(?, ?, ?)', [
                    $wallet->id,
                    $goal->id,
                    $contribution,
                ]);
            } catch (\Exception $e) {
                $this->command->warn("Savings allocation skipped: {$e->getMessage()}");
            }
        }

        $this->command->info('Savings allocations seeded.');
    }
}