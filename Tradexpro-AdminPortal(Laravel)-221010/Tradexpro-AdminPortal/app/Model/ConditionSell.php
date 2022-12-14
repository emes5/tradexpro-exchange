<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ConditionSell extends Model
{
    protected $fillable = ['user_id', 'condition_buy_id', 'trade_coin_id', 'base_coin_id', 'amount', 'price', 'btc_rate', 'status', 'category', 'maker_fees', 'taker_fees'];

    protected $dates = ['deleted_at'];
}
