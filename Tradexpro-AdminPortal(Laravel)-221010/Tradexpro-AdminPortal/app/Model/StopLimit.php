<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StopLimit extends Model
{
    protected $fillable = ['user_id', 'condition_buy_id', 'trade_coin_id', 'base_coin_id', 'stop', 'limit_price', 'amount', 'order', 'is_conditioned', 'category', 'maker_fees', 'taker_fees', 'status'];

    use SoftDeletes;
}
