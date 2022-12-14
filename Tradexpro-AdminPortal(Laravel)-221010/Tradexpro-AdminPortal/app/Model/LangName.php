<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LangName extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'key',
        'status',
        'image'
    ];
}
