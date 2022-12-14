<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SelectedCoinPair extends Model
{
    protected $fillable = ['coin_pair_id', 'user_id', 'base_coin_id', 'trade_coin_id'];
}
