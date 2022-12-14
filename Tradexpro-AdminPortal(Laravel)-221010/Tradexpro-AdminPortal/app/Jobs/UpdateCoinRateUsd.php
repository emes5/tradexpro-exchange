<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCoinRateUsd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $coins;
    public function __construct($coins)
    {
        $this->coins = $coins;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $coins = $this->coins;
        if(isset($coins)) {
            $coin = $coins->pluck('coin_type')->toArray();
            $coin = array_values($coin);
            $coin_string = implode(",", $coin);
            $data = convert_currency_rate_all($coin_string,'USD');
            if ($data) {
                foreach ($coins as $item) {
                    if (isset($data[$item->coin_type])) {
                        $item->coin_price = $data[$item->coin_type]['USD'];
                        $item->save();
                    }
                }
            }
//            foreach ($this->coins as $coin) {
//                $rate = convert_currency_rate(1,'USD',$coin->coin_type);
//                if ($rate > 0) {
//                    $coin->update(['coin_price' => $rate]);
//                }
//            }
        }
    }
}
