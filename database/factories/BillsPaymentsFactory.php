<?php


namespace App\Database\Factories;
use App\Models\Billers;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillsPaymentsFactory extends Factory
{
    public function definition(): array
    {
        // Generate realistic account numbers per biller type
        $accountFormats = [
            'MECO-#########',
            'PLDT-##########',
            'MWC-########',
            'SSS-##-#######-#',
            'PH-###########',
            'PAGIBIG-############',
        ];
        

        return [
            'wallet_id'       => Wallet::factory(),
            'biller_id'       => Billers::factory(),
            'account_number'  => $this->faker->numerify(
                                     $this->faker->randomElement($accountFormats)
                                 ),
            'amount'          => $this->faker->randomFloat(2, 200, 10000),
            'confirmation_no' => 'CONF-' . $this->faker->numerify('#######'),
        ];
    }
}