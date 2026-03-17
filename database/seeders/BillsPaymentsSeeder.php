<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillsPaymentsSeeder extends Seeder
{
    public function run(): void
    {
        // Pick wallets with enough balance for bills (at least 6000)
        $wallets = DB::table('wallets')->where('balance', '>=', 6000)->pluck('id')->toArray();

        if (empty($wallets)) {
            // Fallback: top 5 wallets by balance
            $wallets = DB::table('wallets')->orderByDesc('balance')->limit(5)->pluck('id')->toArray();
        }

        if (empty($wallets)) {
            $this->command->warn('No wallets found. Run CreateWalletsTableSeeder first.');
            return;
        }

        $bills = [
            ['biller' => 'Meralco',              'account' => 'MECO-123456789',    'amount' => 2850.75],
            ['biller' => 'Meralco',              'account' => 'MECO-987654321',    'amount' => 1540.00],
            ['biller' => 'Maynilad',             'account' => 'MWC-55544433',      'amount' => 480.50],
            ['biller' => 'Manila Water',         'account' => 'MWC-11122233',      'amount' => 320.00],
            ['biller' => 'PLDT',                 'account' => 'PLDT-987654321',    'amount' => 1499.00],
            ['biller' => 'Globe Telecom',        'account' => 'GLOBE-112233445',   'amount' => 1299.00],
            ['biller' => 'Converge ICT',         'account' => 'CVG-556677889',     'amount' => 1500.00],
            ['biller' => 'SSS',                  'account' => 'SSS-34-5678901-2',  'amount' => 1125.00],
            ['biller' => 'PhilHealth',           'account' => 'PH-20044556677',    'amount' => 400.00],
            ['biller' => 'Pag-IBIG Fund',        'account' => 'PAGIBIG-0012345678','amount' => 200.00],
            ['biller' => 'BPI Credit Card',      'account' => 'BPI-4111222233334', 'amount' => 5000.00],
            ['biller' => 'BDO Credit Card',      'account' => 'BDO-5500123456789', 'amount' => 3200.00],
        ];

        foreach ($bills as $bill) {
            $billerId = DB::table('billers')->where('name', $bill['biller'])->value('id');
            $walletId = collect($wallets)->random();

            if (!$billerId) {
                $this->command->warn("⚠️  Biller not found: {$bill['biller']}");
                continue;
            }

            try {
                DB::statement('CALL pay_bill(?, ?, ?, ?)', [
                    $walletId,
                    $billerId,
                    $bill['account'],
                    $bill['amount'],
                ]);
            } catch (\Exception $e) {
                $this->command->warn("Bill payment skipped: {$e->getMessage()}");
            }
        }

        $this->command->info('Bill payments seeded.');
    }
}