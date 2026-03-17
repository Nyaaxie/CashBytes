<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoadPurchasesFactory extends Factory
{
    public function definition(): array
    {
        $networks = ['Globe', 'Smart', 'DITO', 'TNT', 'Sun'];

        $promoCodes = [
            'Globe' => ['GO50', 'GO90', 'GOUNLI99', 'GoSURF50', 'GoWATCH99'],
            'Smart' => ['GIGA50', 'GIGA99', 'UNLISURF99', 'BIG500', 'ALLOUT199'],
            'DITO'  => ['DITO99', 'DITO199', 'DITOSURF50'],
            'TNT'   => ['TNT10', 'TNTGIGA55', 'TNTUNLI20'],
            'Sun'   => ['SUNSULIT50', 'SUNCALL20', 'SUNDATA99'],
        ];

        $network   = $this->faker->randomElement($networks);
        $promoCode = $this->faker->randomElement($promoCodes[$network]);

        // Extract load amount from promo code number suffix
        preg_match('/(\d+)$/', $promoCode, $matches);
        $amount = isset($matches[1]) ? (float) $matches[1] : 50.00;

        $prefixes = ['0917', '0918', '0919', '0920', '0927', '0939', '0949', '0955', '0961', '0977', '0998', '0999'];

        return [
            'wallet_id'     => Wallet::factory(),
            'mobile_number' => $this->faker->randomElement($prefixes) . $this->faker->numerify('#######'),
            'network'       => $network,
            'promo_code'    => $promoCode,
            'amount'        => $amount,
        ];
    }
}