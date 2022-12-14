<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FavouriteOrderBook extends Model
{

    protected $fillable = ['user_id', 'base_coin_id','trade_coin_id','price','type'];

}
