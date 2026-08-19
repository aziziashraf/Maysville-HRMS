<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAttendance extends Model
{
    use HasFactory;
	use SoftDeletes;

	protected $fillable = [
        'user_id',
        'scan_datetime',
        'scan_date',
        'scan_time',
        'access_id',
        'remarks',
        'qr_access',
        'status',
        'scan_status',
        'location',
        'coordinates',
    ];

    public function access()
    {
        return $this->belongsTo('App\Models\Access');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    
}
