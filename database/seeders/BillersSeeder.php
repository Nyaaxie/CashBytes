<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
           $billers = [
            // Electric
            ['name' => 'Meralco',              'category' => 'Electric',     'is_active' => true],
            ['name' => 'Visayan Electric',     'category' => 'Electric',     'is_active' => true],
            ['name' => 'Davao Light',          'category' => 'Electric',     'is_active' => true],
            ['name' => 'Cotabato Light',       'category' => 'Electric',     'is_active' => true],

            // Water
            ['name' => 'Maynilad',             'category' => 'Water',        'is_active' => true],
            ['name' => 'Manila Water',         'category' => 'Water',        'is_active' => true],

            // Internet & Telco
            ['name' => 'PLDT',                 'category' => 'Internet',     'is_active' => true],
            ['name' => 'Globe Telecom',        'category' => 'Internet',     'is_active' => true],
            ['name' => 'Converge ICT',         'category' => 'Internet',     'is_active' => true],
            ['name' => 'Sky Cable',            'category' => 'Internet',     'is_active' => true],
            ['name' => 'DITO Telecommunity',   'category' => 'Internet',     'is_active' => true],

            // Government
            ['name' => 'SSS',                  'category' => 'Government',   'is_active' => true],
            ['name' => 'PhilHealth',           'category' => 'Government',   'is_active' => true],
            ['name' => 'Pag-IBIG Fund',        'category' => 'Government',   'is_active' => true],
            ['name' => 'BIR',                  'category' => 'Government',   'is_active' => true],
            ['name' => 'LTO',                  'category' => 'Government',   'is_active' => true],

            // Credit Cards
            ['name' => 'BPI Credit Card',      'category' => 'Credit Card',  'is_active' => true],
            ['name' => 'BDO Credit Card',      'category' => 'Credit Card',  'is_active' => true],
            ['name' => 'Metrobank Card',       'category' => 'Credit Card',  'is_active' => true],
            ['name' => 'Security Bank Card',   'category' => 'Credit Card',  'is_active' => true],
            ['name' => 'UnionBank Card',       'category' => 'Credit Card',  'is_active' => true],

            // Insurance
            ['name' => 'Manulife Philippines', 'category' => 'Insurance',    'is_active' => true],
            ['name' => 'Sun Life',             'category' => 'Insurance',    'is_active' => true],
            ['name' => 'AXA Philippines',      'category' => 'Insurance',    'is_active' => true],
        ];

        foreach ($billers as $biller) {
            DB::table('billers')->insertOrIgnore(array_merge($biller, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

        }
    }
}
