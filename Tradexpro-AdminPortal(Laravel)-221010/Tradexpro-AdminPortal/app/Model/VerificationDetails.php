<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VerificationDetails extends Model
{
    protected $fillable = ['user_id', 'field_name', 'status', 'photo'];
}
