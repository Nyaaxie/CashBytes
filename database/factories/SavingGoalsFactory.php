<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavingGoalsFactory extends Factory
{
    public function definition(): array
    {
        $goals = [
            'Travel'   => ['Boracay Trip', 'Baguio Getaway', 'Palawan Vacation', 'Siargao Adventure', 'Japan Trip 2027'],
            'Tuition'  => ['Tuition Fee 1st Sem', 'Tuition Fee 2nd Sem', 'Review Center Fund', 'Masteral Tuition'],
            'Shopping' => ['Christmas Gifts', 'Gadget Fund', 'Clothes Budget', 'Back-to-School Supplies', 'Lazada Wishlist'],
            'Custom'   => ['Emergency Fund', 'House Repair', 'Wedding Fund', 'Car Down Payment', 'New Laptop'],
        ];

        $category  = $this->faker->randomElement(array_keys($goals));
        $goalName  = $this->faker->randomElement($goals[$category]);
        $target    = $this->faker->randomFloat(2, 5000, 100000);
        $current   = $this->faker->randomFloat(2, 0, $target * 0.9);
        $status    = $current >= $target ? 'completed' : 'active';

        return [
            'user_id'        => User::factory(),
            'name'           => $goalName,
            'category'       => $category,
            'target_amount'  => $target,
            'current_amount' => $current,
            'target_date'    => $this->faker->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
            'status'         => $status,
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $target = $attributes['target_amount'] ?? 10000;
            return [
                'current_amount' => $target,
                'status'         => 'completed',
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}