<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class CurrencyDeposit extends Model
{
    use HasFactory;
    protected $fillable = [
        'unique_code',
        'user_id',
        'wallet_id',
        'payment_method_id',
        'currency',
        'currency_amount',
        'coin_amount',
        'rate',
        'status',
        'updated_by',
        'from_wallet_id',
        'bank_receipt',
        'bank_id',
        'transaction_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function wallet()
    {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class,'bank_id');
    }
    public function payment()
    {
        return $this->belongsTo(CurrencyDepositPaymentMethod::class,'payment_method_id');
    }
}
