<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDailyAttendance extends Model
{
    use HasFactory;
	use SoftDeletes;
	protected $fillable = [
        'user_id',
        'check_in_date',
        'timestamp',
        'name',
        'staff_id',
        'department_name',
        'check_in_time',
        'check_out_time',
        'duration',
        'location',
        'level',
        'status',
        'remarks',
        'secondary_status',
        'remote_working_duration',
    ];

    
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
