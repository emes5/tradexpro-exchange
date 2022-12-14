<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSendCoinHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'updated_by'
    ];

    public function author()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
