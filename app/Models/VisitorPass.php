<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitorPass extends Model
{
    use HasFactory;
	use SoftDeletes;

	protected $fillable = [
        'user_id',
        'visitor_card_id',
        'from_date',
        'to_date',
        'from_time',
        'to_time',
        'visit_purpose',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function accessFloor()
    {
        return $this->morphMany('App\Models\UserAccessFloor', 'content');
    } 

    public function LiftaccessFloor()
    {
        return $this->morphMany('App\Models\UserLiftAccess', 'content');
    } 

    public function visitorPass()
    {
        return $this->hasMany('App\Models\VisitorPass');
    }
    

}
