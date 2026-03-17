<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    // Explicitly bind to Wallet model
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'balance'  => $this->faker->randomFloat(2, 5000, 50000),
            'currency' => 'PHP',
        ];
    }

    // Normal balance: 5k - 50k
    public function normalBalance(): static
    {
        return $this->state(fn(array $attributes) => [
            'balance' => $this->faker->randomFloat(2, 5000, 50000),
        ]);
    }

    // Low balance: 500 - 1500
    public function lowBalance(): static
    {
        return $this->state(fn(array $attributes) => [
            'balance' => $this->faker->randomFloat(2, 500, 1500),
        ]);
    }

    // High balance: 50k - 500k
    public function highBalance(): static
    {
        return $this->state(fn(array $attributes) => [
            'balance' => $this->faker->randomFloat(2, 50000, 500000),
        ]);
    }
}