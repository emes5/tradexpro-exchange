<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FifteenMinute extends Model
{
    protected $table = 'tv_chart_15mins';
    protected $fillable = ['interval', 'trade_coin_id', 'base_coin_id', 'open', 'close', 'high', 'low','volume'];

}
