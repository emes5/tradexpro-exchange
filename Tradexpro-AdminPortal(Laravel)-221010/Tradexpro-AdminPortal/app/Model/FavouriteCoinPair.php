<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FavouriteCoinPair extends Model
{
    protected $fillable = ['coin_pairs_id', 'user_id'];
}
