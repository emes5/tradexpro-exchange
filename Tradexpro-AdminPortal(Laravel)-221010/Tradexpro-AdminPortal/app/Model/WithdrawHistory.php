<?php

namespace App\Model;

use App\Model\Wallet;
use App\User;
use Illuminate\Database\Eloquent\Model;

class WithdrawHistory extends Model
{
    protected $fillable = [
        'receiver_wallet_id',
        'user_id',
        'wallet_id',
        'confirmations',
        'status',
        'address',
        'address_type',
        'amount',
        'fees',
        'transaction_hash',
        'message',
        'btc',
        'doller',
        'coin_type',
        'used_gas',
        'network_type'
    ];
    public function senderWallet(){
        return $this->belongsTo(Wallet::class,'wallet_id','id');
    }
    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_type', 'coin_type');
    }
    public function receiverWallet(){
        return $this->belongsTo(Wallet::class,'receiver_wallet_id','id');
    }
    public function wallet()
    {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }
    public function users(){
        return $this->belongsTo(User::class,'wallet_id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
