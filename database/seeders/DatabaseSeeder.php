<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Create test user with wallet ──────────────────────────
        $testUser = User::factory()->create([
            'name'       => 'Test User',
            'email'      => 'test@cashbytes.ph',
            'password'   => bcrypt('password123'),
            'contact_no' => '09170000001',
        ]);

        $testWallet = Wallet::create([
            'user_id'  => $testUser->id,
            'balance'  => 100000.00,   // ← start with enough to run transactions
            'currency' => 'PHP',
        ]);

        // ── Seed billers + other users first ─────────────────────
        $this->call([
            BillersSeeder::class,
            CreateWalletsTableSeeder::class,
            SavingGoalsSeeder::class,
        ]);

        // ── Seed saving goals for test user ───────────────────────
        $goals = [
            ['name' => 'Boracay Trip',    'category' => 'Travel',   'target' => 15000.00],
            ['name' => 'Tuition Fee',     'category' => 'Tuition',  'target' => 30000.00],
            ['name' => 'New Laptop',      'category' => 'Shopping', 'target' => 50000.00],
        ];

        foreach ($goals as $goal) {
            DB::table('saving_goals')->insert([
                'user_id'        => $testUser->id,
                'name'           => $goal['name'],
                'category'       => $goal['category'],
                'target_amount'  => $goal['target'],
                'current_amount' => 0,
                'status'         => 'active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // ── Run transactions against test user wallet ─────────────

        // Get another wallet to transfer to/from
        $otherWallet = DB::table('wallets')
            ->where('id', '!=', $testWallet->id)
            ->orderByDesc('balance')
            ->first();

        // 1. Receive money first (so test user has incoming transactions too)
        if ($otherWallet) {
            try {
                DB::statement('CALL transfer_funds(?, ?, ?, ?)', [
                    $otherWallet->id,
                    $testWallet->id,
                    5000.00,
                    'Padala ni kuya',
                ]);
                DB::statement('CALL transfer_funds(?, ?, ?, ?)', [
                    $otherWallet->id,
                    $testWallet->id,
                    3000.00,
                    'Bayad sa utang',
                ]);
            } catch (\Exception $e) {
                $this->command->warn("⚠️  Incoming transfer skipped: {$e->getMessage()}");
            }
        }

        // 2. Send money out
        if ($otherWallet) {
            try {
                DB::statement('CALL transfer_funds(?, ?, ?, ?)', [
                    $testWallet->id,
                    $otherWallet->id,
                    1500.00,
                    'Utang ko sayo',
                ]);
                DB::statement('CALL transfer_funds(?, ?, ?, ?)', [
                    $testWallet->id,
                    $otherWallet->id,
                    750.00,
                    'Bayad sa pagkain',
                ]);
            } catch (\Exception $e) {
                $this->command->warn("⚠️  Outgoing transfer skipped: {$e->getMessage()}");
            }
        }

        // 3. Buy load
        $loads = [
            ['09171234567', 'Globe', 'GO50',    50.00],
            ['09181234567', 'Smart', 'GIGA99',  99.00],
            ['09991234567', 'DITO',  'DITO99',  99.00],
        ];

        foreach ($loads as $load) {
            try {
                DB::statement('CALL buy_load(?, ?, ?, ?, ?)', [
                    $testWallet->id,
                    $load[0],
                    $load[1],
                    $load[2],
                    $load[3],
                ]);
            } catch (\Exception $e) {
                $this->command->warn("⚠️  Load skipped: {$e->getMessage()}");
            }
        }

        // 4. Pay bills
        $billers = DB::table('billers')->where('is_active', true)->limit(4)->get();

        $billAmounts = [1850.00, 2200.00, 999.00, 450.00];

        foreach ($billers as $i => $biller) {
            try {
                DB::statement('CALL pay_bill(?, ?, ?, ?)', [
                    $testWallet->id,
                    $biller->id,
                    '123456789' . $i,
                    $billAmounts[$i] ?? 500.00,
                ]);
            } catch (\Exception $e) {
                $this->command->warn("⚠️  Bill payment skipped: {$e->getMessage()}");
            }
        }

        // 5. Add to savings goals
        $testGoals = DB::table('saving_goals')
            ->where('user_id', $testUser->id)
            ->get();

        foreach ($testGoals as $goal) {
            try {
                DB::statement('CALL add_to_savings(?, ?, ?)', [
                    $testWallet->id,
                    $goal->id,
                    500.00,
                ]);
            } catch (\Exception $e) {
                $this->command->warn("⚠️  Savings skipped: {$e->getMessage()}");
            }
        }

        // ── Seed remaining transaction data for other users ───────
        $this->call([
            FundsTransfersSeeder::class,
            LoadPurchasesSeeder::class,
            BillsPaymentsSeeder::class,
            SavingsAllocationsSeeder::class,
            TransactionsSeeder::class,
        ]);

        $this->command->info('✅ Test user seeded with full transaction history.');
        $this->command->info('   Email:    test@cashbytes.ph');
        $this->command->info('   Password: password123');

        // Show final balance
        $finalWallet = DB::table('wallets')->where('id', $testWallet->id)->first();
        $this->command->info('   Balance:  ₱' . number_format($finalWallet->balance, 2));
    }
}