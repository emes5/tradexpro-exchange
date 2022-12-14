<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinPair extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'parent_coin_id',
        'child_coin_id',
        'value',
        'volume',
        'change',
        'high',
        'low',
        'status',
        'initial_price',
        'price',
        'is_chart_updated'
    ];

    public function parent_coin()
    {
        return $this->belongsTo(Coin::class,'parent_coin_id');
    }
    public function child_coin()
    {
        return $this->belongsTo(Coin::class,'child_coin_id');
    }
}
