<?php

namespace App\Jobs;

use App\Http\Repositories\UserWalletRepository;
use App\Http\Services\TradingViewChartService;
use App\Model\Buy;
use App\Model\Sell;
use App\Model\UserWallet;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TradingViewChartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param $buy
     * @param $sell
     * @param $transaction
     */
    public $transaction;
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new TradingViewChartService();
        $service->updateCandleData($this->transaction);
    }
}
