<?php

namespace Database\Seeders;

use App\Model\LangName;
use Illuminate\Database\Seeder;

class LangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (langNameOld() as $key => $value) {
            LangName::firstOrCreate(['key' => $key], ['name' => $value]);
        }
    }
}
