<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SavingGoalsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Run CreateWalletsTableSeeder first.');
            return;
        }

        $goalTemplates = [
            ['name' => 'Boracay Trip',              'category' => 'Travel',   'target' => 20000.00, 'current' => 5000.00,  'date' => '2026-12-01'],
            ['name' => 'Japan Trip 2027',           'category' => 'Travel',   'target' => 80000.00, 'current' => 15000.00, 'date' => '2027-03-15'],
            ['name' => 'Palawan Vacation',          'category' => 'Travel',   'target' => 25000.00, 'current' => 25000.00, 'date' => '2026-06-30', 'status' => 'completed'],
            ['name' => 'Tuition Fee 1st Sem',       'category' => 'Tuition',  'target' => 35000.00, 'current' => 10000.00, 'date' => '2026-08-15'],
            ['name' => 'Tuition Fee 2nd Sem',       'category' => 'Tuition',  'target' => 35000.00, 'current' => 0.00,     'date' => '2027-01-10'],
            ['name' => 'Review Center Fund',        'category' => 'Tuition',  'target' => 15000.00, 'current' => 7500.00,  'date' => '2026-09-01'],
            ['name' => 'Christmas Gifts 2026',      'category' => 'Shopping', 'target' => 10000.00, 'current' => 2000.00,  'date' => '2026-12-20'],
            ['name' => 'New Laptop Fund',           'category' => 'Shopping', 'target' => 45000.00, 'current' => 12000.00, 'date' => '2026-11-11'],
            ['name' => 'Back-to-School Supplies',   'category' => 'Shopping', 'target' => 5000.00,  'current' => 5000.00,  'date' => '2026-06-01', 'status' => 'completed'],
            ['name' => 'Emergency Fund',            'category' => 'Custom',   'target' => 50000.00, 'current' => 20000.00, 'date' => '2027-06-01'],
            ['name' => 'House Repair',              'category' => 'Custom',   'target' => 30000.00, 'current' => 5000.00,  'date' => '2026-10-01'],
            ['name' => 'Wedding Fund',              'category' => 'Custom',   'target' => 200000.00,'current' => 45000.00, 'date' => '2027-12-01'],
        ];

        // Distribute goals across users
        foreach ($goalTemplates as $index => $goal) {
            $user = $users[$index % $users->count()];

            DB::table('saving_goals')->insert([
                'user_id'        => $user->id,
                'name'           => $goal['name'],
                'category'       => $goal['category'],
                'target_amount'  => $goal['target'],
                'current_amount' => $goal['current'],
                'target_date'    => $goal['date'],
                'status'         => $goal['status'] ?? 'active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        $this->command->info('Savings goals seeded.');
    }
}