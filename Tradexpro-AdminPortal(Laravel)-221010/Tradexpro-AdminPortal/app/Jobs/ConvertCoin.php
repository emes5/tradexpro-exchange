<?php

namespace App\Jobs;

use App\Model\WalletSwapHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConvertCoin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $input;
    private $fromWallet;
    private $toWallet;
    public function __construct($input,$fromWallet,$toWallet)
    {
        $this->input = $input;
        $this->fromWallet = $fromWallet;
        $this->toWallet = $toWallet;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            WalletSwapHistory::create($this->input);

            $this->fromWallet->decrement('balance',$this->input['requested_amount']);
            $this->toWallet->increment('balance',$this->input['converted_amount']);

            Log::info('Coin Converted successful');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('coin convert job exception : '.$e->getMessage());
        }
    }
}
