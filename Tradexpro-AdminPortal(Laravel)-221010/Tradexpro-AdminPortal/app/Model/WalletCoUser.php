<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class WalletCoUser extends Model
{
    protected $fillable = ['wallet_id', 'user_id', 'status'];

    public function wallet() {
        return $this->belongsTo(Wallet::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}
