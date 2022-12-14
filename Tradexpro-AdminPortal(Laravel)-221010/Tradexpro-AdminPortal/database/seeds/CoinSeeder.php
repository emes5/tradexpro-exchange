<?php

use App\Model\Coin;
use App\Model\Wallet;
use App\User;
use Illuminate\Database\Seeder;

class CoinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Coin::firstOrCreate(['coin_type' => 'BTC'],['name' => 'Bitcoin']);
        Coin::firstOrCreate(['coin_type' => 'USDT'],['name' => 'Tether USD']);

        $users = User::select('*')->get();
        if (isset($users[0])) {
            foreach ($users as $user) {
                $coins = Coin::select('*')->get();
                $count = $coins->count();
                for($i=0; $count > $i; $i++) {
                    Wallet::firstOrCreate(['user_id' => $user->id, 'coin_id' => $coins[$i]->id],
                        ['name' =>  $coins[$i]->coin_type.' Wallet', 'coin_type' => $coins[$i]->coin_type]);
                }
            }
        }
    }
}
