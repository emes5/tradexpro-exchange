<?php

namespace App\Console\Commands;

use App\Model\Coin;
use App\Model\CoinSetting;
use App\Model\Wallet;
use Illuminate\Console\Command;

class addCoinType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:coinType';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $wallets = Wallet::where(['coin_type' => ''])->get();
            if (isset($wallets[0])) {
                foreach ($wallets as $wallet) {
                    $coin = Coin::find($wallet->coin_id);
                    if(empty($wallet->coin_type)) {
                        $wallet->update(['coin_type' => $coin->coin_type]);
                    }
                }
            }

            $coinSettings = CoinSetting::where(['check_encrypt' => STATUS_PENDING])
                ->where(function ($q) {
                    $q->where('bitgo_wallet','<>','')
                        ->orWhere('coin_api_pass','<>', '')
                        ->orWhere('wallet_key','<>','');
                })->get();
            if (isset($coinSettings[0])) {
                foreach ($coinSettings as $coinSetting) {
                    if (!empty($coinSetting->bitgo_wallet)) {
                        $coinSetting->update(['bitgo_wallet' => encrypt($coinSetting->bitgo_wallet), 'check_encrypt' => STATUS_SUCCESS]);
                    }
                    if (!empty($coinSetting->coin_api_pass)) {
                        $coinSetting->update(['coin_api_pass' => encrypt($coinSetting->coin_api_pass), 'check_encrypt' => STATUS_SUCCESS]);
                    }
                    if (!empty($coinSetting->wallet_key)) {
                        $coinSetting->update(['wallet_key' => encrypt($coinSetting->wallet_key), 'check_encrypt' => STATUS_SUCCESS]);
                    }
                }
            }
        } catch (\Exception $e) {
            storeException('addCoinType',$e->getMessage());
        }

    }
}
