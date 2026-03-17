<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    // Explicitly bind to User model
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        
        $firstNames = ['Juan', 'Maria', 'Pedro', 'Ana', 'Carlo', 'Rosa', 'Jose', 'Liza', 'Mark', 'Celine',
                       'Andres', 'Gabriela', 'Ramon', 'Cristina', 'Emilio', 'Ligaya', 'Dante', 'Maricel', 'Rico', 'Jessa'];
        $lastNames  = ['delacruz', 'santos', 'reyes', 'gonzales', 'mendoza', 'garcia', 'torres', 'flores',
                       'bautista', 'aquino', 'ramos', 'villanueva', 'castillo', 'padilla', 'soriano', 'lim', 'tan', 'uy'];

        $displayLastNames = ['dela Cruz', 'Santos', 'Reyes', 'Gonzales', 'Mendoza', 'Garcia', 'Torres', 'Flores',
                             'Bautista', 'Aquino', 'Ramos', 'Villanueva', 'Castillo', 'Padilla', 'Soriano', 'Lim', 'Tan', 'Uy'];

        $index      = $this->faker->numberBetween(0, count($lastNames) - 1);
        $firstName  = $this->faker->randomElement($firstNames);
        $lastName   = $displayLastNames[$index];
        $emailLast  = $lastNames[$index];
        $emailFirst = strtolower($firstName);
        $suffix     = $this->faker->unique()->numerify('####');

        $prefixes  = ['0917', '0918', '0919', '0920', '0921', '0927', '0928', '0929',
                      '0939', '0947', '0949', '0955', '0961', '0977', '0998', '0999'];
        $contactNo = $this->faker->randomElement($prefixes) . $this->faker->numerify('#######');

        return [
            'name'              => "{$firstName} {$lastName}",
            'email'             => "{$emailFirst}.{$emailLast}{$suffix}@cashbytes.ph",
            'contact_no'        => $contactNo,
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password123'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}