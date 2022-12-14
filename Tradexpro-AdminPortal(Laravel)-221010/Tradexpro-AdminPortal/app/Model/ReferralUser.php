<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralUser extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'parent_id'];

    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
