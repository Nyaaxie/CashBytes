<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FundsTransfersSeeder extends Seeder
{
    public function run(): void
    {
        $allWallets = DB::table('wallets')->pluck('id')->toArray();

        if (count($allWallets) < 2) {
            $this->command->warn('Not enough wallets. Run CreateWalletsTableSeeder first.');
            return;
        }

        // Senders must have enough balance — pick top 5 by balance
        $richWallets = DB::table('wallets')
            ->orderByDesc('balance')
            ->limit(5)
            ->pluck('id')
            ->toArray();

        $transfers = [
            ['note' => 'Utang ko sayo',   'amount' => 500.00],
            ['note' => 'Bayad sa pagkain','amount' => 250.00],
            ['note' => 'Pasalubong fund', 'amount' => 1000.00],
            ['note' => 'Para sa anak',    'amount' => 3000.00],
            ['note' => 'Tulong sa gastos','amount' => 750.00],
            ['note' => 'Bayad kuya',      'amount' => 1500.00],
            ['note' => 'Share sa bahay',  'amount' => 5000.00],
            ['note' => 'Pang-abot buwan', 'amount' => 2000.00],
        ];

        foreach ($transfers as $transfer) {
            $senderId   = collect($richWallets)->random();
            $receiverId = collect($allWallets)
                ->filter(fn($id) => $id !== $senderId)
                ->random();

            try {
                DB::statement('CALL transfer_funds(?, ?, ?, ?)', [
                    $senderId,
                    $receiverId,
                    $transfer['amount'],
                    $transfer['note'],
                ]);
            } catch (\Exception $e) {
                $this->command->warn("Transfer skipped: {$e->getMessage()}");
            }
        }

        $this->command->info('Fund transfers seeded.');
    }
}