<?php

use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\CustomPageSeeder;
use Database\Seeders\LangSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(AdminSettingTableSeeder::class);
        $this->call(CoinSeeder::class);
        $this->call(CoinPairSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(CustomPageSeeder::class);
        $this->call(LangSeeder::class);
    }
}
