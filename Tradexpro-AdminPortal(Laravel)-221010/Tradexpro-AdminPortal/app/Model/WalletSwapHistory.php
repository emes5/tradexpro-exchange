<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WalletSwapHistory extends Model
{
    protected $fillable = [
        'user_id',
        'from_wallet_id',
        'to_wallet_id',
        'from_coin_type',
        'to_coin_type',
        'requested_amount',
        'converted_amount',
        'status',
        'rate',
    ];

    public function fromWallet()
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }
    public function toWallet()
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }
}
