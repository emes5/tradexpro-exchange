<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FourHour extends Model
{
    protected $table = 'tv_chart_4hours';
    protected $fillable = ['interval', 'trade_coin_id', 'base_coin_id', 'open', 'close', 'high', 'low','volume'];

}
