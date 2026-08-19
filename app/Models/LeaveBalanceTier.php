<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalanceTier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'leave_type_id',
        'years_of_service',
        'amount',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
