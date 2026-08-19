<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTarget extends Model
{
    use HasFactory;
	use SoftDeletes;
	protected $fillable = [
        'notification_id',
        'user_id',
        'department_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function notification()
    {
        return $this->belongsTo('App\Models\Notification');
    }
}