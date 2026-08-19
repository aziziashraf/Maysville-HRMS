<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'purpose',
        'date_needed',
        'date_ordered',
        'purchased_from',
        'source_of_fund',
        'auto_renew',
        'remarks',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_remark',
        'submitted_at',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function purchaseRequisitionItems()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function activeClaims()
    {
        // status not cancelled or rejected
        return $this->claims()->whereNotIn('status', ['cancelled', 'rejected']);
    }

    public function totalAmount()
    {
        $total = $this->purchaseRequisitionItems->sum('total_price');
        return round($total, 2);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function approver () {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
