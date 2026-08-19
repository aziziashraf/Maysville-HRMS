<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'remarks',
        'status',
        'submitted_at',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_remark',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_remark',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->status === 'submitted' && !$model->submitted_at) {
                $model->submitted_at = now();
            }
        });
    }

    public function user () {
        return $this->belongsTo(User::class);
    }

    public function leaveType () {
        return $this->belongsTo(LeaveType::class);
    }

    public function reviewer () {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver () {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function leaveDeductionLogs()
    {
        return $this->hasMany(LeaveDeductionLog::class);
    }
}
