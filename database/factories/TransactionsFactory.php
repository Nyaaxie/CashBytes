<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionsFactory extends Factory
{
    public function definition(): array
    {
        $type      = $this->faker->randomElement(['transfer', 'save', 'load', 'bill_payment']);
        $direction = in_array($type, ['transfer']) 
                        ? $this->faker->randomElement(['debit', 'credit']) 
                        : 'debit';

        $typePrefix = match($type) {
            'transfer'     => 'CB-TRF',
            'save'         => 'CB-SAV',
            'load'         => 'CB-LOAD',
            'bill_payment' => 'CB-BILL',
        };

        $descriptions = [
            'transfer'     => ['Transfer to wallet', 'Received from wallet', 'Send money'],
            'save'         => ['Savings - Boracay Trip', 'Savings - Tuition Fee', 'Savings - Christmas Fund'],
            'load'         => ['Globe Load - 09171234567', 'Smart Load - 09981234567', 'DITO Load - 09514321000'],
            'bill_payment' => ['Bill Payment - Meralco', 'Bill Payment - PLDT', 'Bill Payment - Maynilad'],
        ];

        return [
            'wallet_id'   => Wallet::factory(),
            'type'        => $type,
            'direction'   => $direction,
            'amount'      => $this->faker->randomFloat(2, 50, 15000),
            'reference_no'=> $typePrefix . '-' . now()->format('Ymd') . '-' . $this->faker->numerify('######'),
            'description' => $this->faker->randomElement($descriptions[$type]),
            'status'      => $this->faker->randomElement(['completed', 'completed', 'completed', 'pending', 'failed']),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'completed']);
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'pending']);
    }

    public function failed(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'failed']);
    }
}