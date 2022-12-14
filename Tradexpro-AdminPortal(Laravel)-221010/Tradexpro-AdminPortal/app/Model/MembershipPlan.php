<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    protected $fillable = [
        'plan_name',
        'duration',
        'amount',
        'image',
        'status',
        'bonus_type',
        'bonus_coin_type',
        'bonus',
        'description'
    ];
}
