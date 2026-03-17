<?php

namespace Database\Factories;

use App\Models\SavingGoals;
use App\Models\Transactions;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavingsAllocationsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'savings_goal_id' => SavingGoals::factory(),
            'transaction_id'  => Transactions::factory(),
            'amount'          => $this->faker->randomFloat(2, 100, 5000),
        ];
    }
}