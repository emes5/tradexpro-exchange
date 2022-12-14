<?php

namespace App\Jobs;

use App\Model\Wallet;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class NewCoinCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $coin;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($coin)
    {
        $this->coin = $coin;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('new coin create job : start');
        try {
            $coinData = $this->coin;
            $users = User::select('*')->get();
            if (isset($users[0])) {
                foreach ($users as $user) {
                    Wallet::firstOrCreate(['user_id' => $user->id, 'coin_id' => $coinData->id],
                        ['name' =>  $coinData->coin_type.' Wallet', 'coin_type' => $coinData->coin_type]);
                }
                Log::info('new coin create job  : coin wallet created successfully');
            } else {
                Log::info('new coin create job  : user not found');
            }
        } catch (\Exception $e) {
            Log::info('new coin create job exception : '.$e->getMessage());
        }
    }
}
