<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoadPurchasesSeeder extends Seeder
{
    public function run(): void
    {
        $wallets = Wallet::pluck('id')->toArray();

        if (empty($wallets)) {
            $this->command->warn('No wallets found. Run CreateWalletsTableSeeder first.');
            return;
        }

        $loads = [
            ['mobile' => '09171234567', 'network' => 'Globe', 'promo' => 'GO50',      'amount' => 50.00],
            ['mobile' => '09189876543', 'network' => 'Globe', 'promo' => 'GOUNLI99',  'amount' => 99.00],
            ['mobile' => '09271122334', 'network' => 'Globe', 'promo' => 'GoSURF50',  'amount' => 50.00],
            ['mobile' => '09981234567', 'network' => 'Smart', 'promo' => 'GIGA99',    'amount' => 99.00],
            ['mobile' => '09474433221', 'network' => 'Smart', 'promo' => 'BIG500',    'amount' => 500.00],
            ['mobile' => '09201112233', 'network' => 'Smart', 'promo' => 'GIGA50',    'amount' => 50.00],
            ['mobile' => '09514321000', 'network' => 'DITO',  'promo' => 'DITO99',    'amount' => 99.00],
            ['mobile' => '09392211445', 'network' => 'TNT',   'promo' => 'TNTGIGA55', 'amount' => 55.00],
            ['mobile' => '09554445566', 'network' => 'Sun',   'promo' => 'SUNDATA99', 'amount' => 99.00],
            ['mobile' => '09997778899', 'network' => 'Globe', 'promo' => 'GO90',      'amount' => 90.00],
        ];

        foreach ($loads as $load) {
            $walletId = collect($wallets)->random();

            try {
                DB::statement('CALL buy_load(?, ?, ?, ?, ?)', [
                    $walletId,
                    $load['mobile'],
                    $load['network'],
                    $load['promo'],
                    $load['amount'],
                ]);
            } catch (\Exception $e) {
                $this->command->warn("Load skipped: {$e->getMessage()}");
            }
        }

        $this->command->info('Load purchases seeded.');
    }
}