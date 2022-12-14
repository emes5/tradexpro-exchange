<?php

namespace Database\Seeders;

use App\Model\CustomPage;
use Illuminate\Database\Seeder;

class CustomPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CustomPage::firstOrCreate(['key' => 'privacy-policy'],[
            'title' => 'Privacy Policy',
            'description' => "It's the privacy policy page, you can add here your policy"
        ]);
        CustomPage::firstOrCreate(['key' => 'terms-and-condition'],[
            'title' => 'Terms and Condition',
            'description' => "It's the terms and condition page, you can add here your terms and condition"
        ]);
    }
}
