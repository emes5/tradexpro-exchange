<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    public $table= 'wallets';

    protected $fillable = [
        'user_id',
        'name',
        'balance',
        'referral_balance',
        'status',
        'is_primary',
        'coin_type',
        'coin_id'
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /*public function hiddenWallets()
    {
        return $this->hasMany(HiddenWallet::class, 'user_wallet_id', 'id');
    }

    public function merchantuser(){

        return $this->belongsTo(MerchantUser::class,'user_id', 'user_id');
    }*/
}
