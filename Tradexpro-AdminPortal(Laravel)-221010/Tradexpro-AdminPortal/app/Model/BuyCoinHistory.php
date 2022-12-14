<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class BuyCoinHistory extends Model
{
    protected $fillable = [
        'confirmations',
        'status',
        'coin_type',
        'phase_id',
        'referral_level',
        'fees',
        'bonus',
        'requested_amount',
        'referral_bonus',
        'stripe_token',
        'address',
        'type',
        'user_id',
        'coin',
        'btc',
        'doller',
        'transaction_id',
        'admin_confirmation',
        'bank_sleep',
        'bank_id',
        '',
        '',
    ];

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }
}
