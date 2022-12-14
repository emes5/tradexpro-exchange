<?php

namespace Database\Seeders;

use App\Model\CountryList;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (countrylistOld() as $key => $value) {
            CountryList::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
