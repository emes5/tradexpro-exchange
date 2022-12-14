<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BuyCoinReferralHistory extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'buy_id',
        'phase_id',
        'child_id',
        'level',
        'system_fees',
        'amount',
        'status'
    ];
}
