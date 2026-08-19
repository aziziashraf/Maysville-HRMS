<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'claim_type_id',
        'purchase_requisition_id',
        'unit_quantity',
        'amount',
        'remarks',
        'status',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_remark',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_remark',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function claimType()
    {
        return $this->belongsTo(ClaimType::class);
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

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }
}
