<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BillersFactory extends Factory
{
    public function definition(): array
    {
        $billers = [
            ['name' => 'Meralco',           'category' => 'Electric'],
            ['name' => 'Visayan Electric',  'category' => 'Electric'],
            ['name' => 'Davao Light',       'category' => 'Electric'],
            ['name' => 'Maynilad',          'category' => 'Water'],
            ['name' => 'Manila Water',      'category' => 'Water'],
            ['name' => 'PLDT',              'category' => 'Internet'],
            ['name' => 'Globe Telecom',     'category' => 'Internet'],
            ['name' => 'Converge ICT',      'category' => 'Internet'],
            ['name' => 'Sky Cable',         'category' => 'Internet'],
            ['name' => 'SSS',               'category' => 'Government'],
            ['name' => 'PhilHealth',        'category' => 'Government'],
            ['name' => 'Pag-IBIG',          'category' => 'Government'],
            ['name' => 'BIR',               'category' => 'Government'],
            ['name' => 'BPI Credit Card',   'category' => 'Credit Card'],
            ['name' => 'BDO Credit Card',   'category' => 'Credit Card'],
            ['name' => 'Metrobank Card',    'category' => 'Credit Card'],
        ];

        $biller = $this->faker->randomElement($billers);

        return [
            'name'      => $biller['name'],
            'category'  => $biller['category'],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}