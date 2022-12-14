<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MembershipBonus extends Model
{
    protected $fillable = ['bonus_amount', 'user_id'];
}
