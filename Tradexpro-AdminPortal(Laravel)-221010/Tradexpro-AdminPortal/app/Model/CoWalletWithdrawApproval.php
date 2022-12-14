<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CoWalletWithdrawApproval extends Model
{
    protected $fillable = ['temp_withdraw_id', 'wallet_id','user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function wallet() {
        return $this->belongsTo(Wallet::class);
    }

    public function temp_withdraw() {
        return $this->belongsTo(TempWithdraw::class);
    }
}
