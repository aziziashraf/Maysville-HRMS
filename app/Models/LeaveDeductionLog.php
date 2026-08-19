<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveDeductionLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'leave_balance_list_id',
        'leave_id',
        'amount',
    ];

    public function leaveBalanceList()
    {
        return $this->belongsTo(LeaveBalanceList::class);
    }

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }
}
