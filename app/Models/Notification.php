<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Notification extends Model
{
    use HasFactory;
	use SoftDeletes;

	protected $fillable = [
        'title',
        'short_descriptions',
        'notification_type',
        'descriptions',
        'status',
        'send_datetime',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            $notification->created_by = Auth::id();
        });

        static::updating(function ($notification) {
            $notification->updated_by = Auth::id();
        });

        static::deleting(function ($notification) {
            $notification->deleted_by = Auth::id();
            $notification->save(); // Save the model to persist the deleted_by value
        });
    }

    public function notiTarget()
    {
        return $this->hasMany('App\Models\NotificationTarget');
    }

    public function notiTargetGroupByDepartment()
    {
        return $this->hasMany('App\Models\NotificationTarget')->groupBy('department_id')->select('department_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo('App\Models\User', 'deleted_by');
    }
}