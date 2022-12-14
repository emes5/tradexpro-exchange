<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class IcoPhase extends Model
{
    protected $fillable = [
        'phase_name',
        'start_date',
        'end_date',
        'fees',
        'rate',
        'amount',
        'bonus',
        'status',
        'affiliation_level',
        'affiliation_percentage'
    ];
}
