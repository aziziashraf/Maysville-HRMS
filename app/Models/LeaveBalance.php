<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'leave_type_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function leaveBalanceLists()
    {
        return $this->hasMany(LeaveBalanceList::class);
    }

    public function totalBalance() {
        // is the total from the leave balance list balance column that status is active
        return $this->leaveBalanceLists()->where('status', true)->sum('balance');
    }
}
