<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CoinRequest extends Model
{
    protected $fillable = [
        'amount',
        'sender_user_id',
        'receiver_user_id',
        'sender_wallet_id',
        'receiver_wallet_id',
        'status'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class,'sender_user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class,'receiver_user_id');
    }
    public function sender_wallet()
    {
        return $this->belongsTo(Wallet::class,'sender_wallet_id');
    }

    public function receiver_wallet()
    {
        return $this->belongsTo(Wallet::class,'receiver_wallet_id');
    }
}
