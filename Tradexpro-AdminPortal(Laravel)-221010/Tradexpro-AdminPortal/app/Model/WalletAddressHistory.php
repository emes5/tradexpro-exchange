<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WalletAddressHistory extends Model
{
    protected $fillable = ['wallet_id', 'address', 'coin_type', 'wallet_key'];

    public function wallet(){
        return $this->hasOne(Wallet::class,'id','wallet_id');
    }
}
