<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AffiliationCode extends Model
{
    use SoftDeletes;

    protected $fillable=['user_id','code','status'];

    protected $dates = ['deleted_at'];

    public function AffiliateUser(){
        return $this->belongsTo(User::class,'user_id');
    }
}
