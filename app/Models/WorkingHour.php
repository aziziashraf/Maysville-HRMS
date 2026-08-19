<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkingHour extends Model
{
    use HasFactory;
	use SoftDeletes;
	protected $fillable = [
        'shift_label',
        'break_start_time',
        'start_time',
        'break_end_time',
        'end_time',
    ];

    public function workHourDays()
    {
        return $this->hasMany('App\Models\WorkingHourDays');
    }
    
}
