<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Overtime extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'date',
        'reasons',
        'estimated_time_taken',
        'actual_time_start',
        'actual_time_end',
        'actual_time_taken',
        'actual_time_approved',
        'claim_as',
        'remarks',
        'status',
        'pre_review_status',
        'pre_reviewed_by',
        'pre_reviewed_at',
        'pre_review_remark',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_remark',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_remark',
        'actual_time_approved_updated_by',
        'claim_as_updated_by',
    ];

    public function user () {
        return $this->belongsTo(User::class);
    }

    public function pre_reviewer () {
        return $this->belongsTo(User::class, 'pre_reviewed_by');
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

    public function actualTimeApprover () {
        return $this->belongsTo(User::class, 'actual_time_approved_updated_by');
    }

    public function claimAsApprover () {
        return $this->belongsTo(User::class, 'claim_as_updated_by');
    }
}
