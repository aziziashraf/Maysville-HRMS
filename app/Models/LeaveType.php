<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'label_color',
        'balance_unit',
        'renew_freq',
        'default_amount',
        'carry_forward',
        'carry_forward_limit',
        'confirmed_employees_only',
        'unlimited',
        'limit_per_leave',
        'limit_per_leave_amount',
        'back_dated',
        'back_dated_days_limit',
        'attachment_required',
        'carry_forward_timeframe_type',
        'carry_forward_timeframe_value',
        // 'leave_type_to_deduct_if_depleted'
    ];

    public function leaves () {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveBalanceTiers()
    {
        return $this->hasMany(LeaveBalanceTier::class);
    }

    // public function toDeductIfDepleted()
    // {
    //     return $this->belongsTo(LeaveType::class, 'leave_type_to_deduct_if_depleted');
    // }
    
}
