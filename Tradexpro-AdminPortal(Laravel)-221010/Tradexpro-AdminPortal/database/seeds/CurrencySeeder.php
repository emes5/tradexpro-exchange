<?php

namespace Database\Seeders;

use App\Model\CurrencyList;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CurrencyList::firstOrCreate(['code' => 'USD'], [
            'name' => 'United state dollar',
            'symbol' => '$',
            'rate' => 1,
            'is_primary' => 1,
        ]);
        CurrencyList::firstOrCreate(['code' => 'EUR'], [
            'name' => 'EURO',
            'symbol' => '€',
            'rate' => 1,
        ]);
    }
}
