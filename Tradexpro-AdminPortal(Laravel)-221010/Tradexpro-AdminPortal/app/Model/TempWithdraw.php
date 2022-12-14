<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TempWithdraw extends Model
{
    protected $fillable = ['user_id', 'wallet_id', 'amount', 'address', 'message', 'withdraw_id', 'status'];

    public function wallet() {
        return $this->belongsTo(Wallet::class);
    }

    public function user_approvals() {
        return $this->hasMany(CoWalletWithdrawApproval::class);
    }
}
