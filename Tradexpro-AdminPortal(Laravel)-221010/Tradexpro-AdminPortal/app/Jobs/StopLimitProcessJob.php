<?php

namespace App\Jobs;

use App\Http\Services\StopLimitService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StopLimitProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $coinPair;

    /**
     * Create a new job instance.
     *
     * @param $coinPair
     */
    public function __construct($coinPair)
    {
        $this->coinPair = $coinPair;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new StopLimitService();
        $service->process($this->coinPair);
    }
}
