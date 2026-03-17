<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class CreateWalletsTableSeeder extends Seeder
{
    public function run(): void
    {
        // 3 high balance users
        User::factory(3)->create()->each(function ($user) {
            Wallet::factory()->highBalance()->create(['user_id' => $user->id]);
        });

        // 5 normal balance users
        User::factory(5)->create()->each(function ($user) {
            Wallet::factory()->create(['user_id' => $user->id]);
        });

        // 2 low balance users
        User::factory(2)->create()->each(function ($user) {
            Wallet::factory()->lowBalance()->create(['user_id' => $user->id]);
        });

        $this->command->info('10 users with wallets seeded.');
    }
}