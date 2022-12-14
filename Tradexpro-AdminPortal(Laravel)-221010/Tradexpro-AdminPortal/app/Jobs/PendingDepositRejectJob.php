<?php

namespace App\Jobs;

use App\Services\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PendingDepositRejectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 0;
    private $transaction;
    private $userId;
    public function __construct($transaction,$userId)
    {
        $this->transaction = $transaction;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            storeException('PendingDepositRejectJob', 'called');
            $deposit = $this->transaction;
            if ($deposit->status == STATUS_PENDING) {
                $deposit->update(['status' => STATUS_REJECTED, 'updated_by' => $this->userId]);
                storeException('PendingDepositRejectJob', 'deposit rejected successfully . deposit id = '.$deposit->id);
            } else {
                storeException('PendingDepositRejectJob', 'deposit status not pending . deposit id = '.$deposit->id);
            }
        } catch (\Exception $e) {
            storeException('PendingDepositRejectJob', $e->getMessage());
        }
    }
}
