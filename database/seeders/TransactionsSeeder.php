<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionsSeeder extends Seeder
{
    public function run(): void
    {
        // This seeder adds extra historical "completed" transactions
        // for display purposes (e.g. transaction history page)
        $wallets = Wallet::pluck('id')->toArray();

        if (empty($wallets)) {
            $this->command->warn('No wallets found.');
            return;
        }

        $extraTransactions = [
            ['type' => 'load',         'direction' => 'debit',  'amount' => 99.00,   'desc' => 'Globe Load - 09171234567',     'ref_prefix' => 'CB-LOAD'],
            ['type' => 'bill_payment', 'direction' => 'debit',  'amount' => 1200.00, 'desc' => 'Bill Payment - Meralco',       'ref_prefix' => 'CB-BILL'],
            ['type' => 'transfer',     'direction' => 'credit', 'amount' => 500.00,  'desc' => 'Received from wallet',         'ref_prefix' => 'CB-TRF'],
            ['type' => 'save',         'direction' => 'debit',  'amount' => 2000.00, 'desc' => 'Savings - Boracay Trip',       'ref_prefix' => 'CB-SAV'],
            ['type' => 'bill_payment', 'direction' => 'debit',  'amount' => 1499.00, 'desc' => 'Bill Payment - PLDT',          'ref_prefix' => 'CB-BILL'],
            ['type' => 'load',         'direction' => 'debit',  'amount' => 55.00,   'desc' => 'TNT Load - 09392211445',       'ref_prefix' => 'CB-LOAD'],
            ['type' => 'transfer',     'direction' => 'debit',  'amount' => 1000.00, 'desc' => 'Transfer to wallet',           'ref_prefix' => 'CB-TRF'],
            ['type' => 'save',         'direction' => 'debit',  'amount' => 5000.00, 'desc' => 'Savings - Emergency Fund',     'ref_prefix' => 'CB-SAV'],
            ['type' => 'bill_payment', 'direction' => 'debit',  'amount' => 400.00,  'desc' => 'Bill Payment - PhilHealth',    'ref_prefix' => 'CB-BILL'],
            ['type' => 'transfer',     'direction' => 'credit', 'amount' => 3000.00, 'desc' => 'Received from wallet',         'ref_prefix' => 'CB-TRF'],
        ];

        foreach ($extraTransactions as $txn) {
            $walletId = collect($wallets)->random();
            $refNo    = $txn['ref_prefix'] . '-' . now()->format('Ymd') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('transactions')->insert([
                'wallet_id'    => $walletId,
                'type'         => $txn['type'],
                'direction'    => $txn['direction'],
                'amount'       => $txn['amount'],
                'reference_no' => $refNo,
                'description'  => $txn['desc'],
                'status'       => 'completed',
                'created_at'   => now()->subDays(rand(1, 60)),
                'updated_at'   => now(),
            ]);
        }

        $this->command->info('Extra transaction history seeded.');
    }
}