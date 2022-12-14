<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdminSetting extends Model
{
    protected $fillable = ['slug', 'value'];
}
