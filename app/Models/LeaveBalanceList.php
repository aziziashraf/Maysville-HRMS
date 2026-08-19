<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalanceList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'leave_balance_id',
        'description',
        'balance',
        'expiry_date',
        'month',
        'year',
        'status',
        'carried_forward',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($leaveBalanceList) {
            $log = new LeaveBalanceListLog([
                'leave_balance_list_id' => $leaveBalanceList->id,
                'reason' => 'Initial balance',
                'balance_before' => 0,
                'balance_after' => $leaveBalanceList->balance,
            ]);

            $log->save();
        });

        static::updated(function ($leaveBalanceList) {
            $changes = $leaveBalanceList->getChanges();

            if (array_key_exists('balance', $changes) || array_key_exists('carry_forward_balance', $changes)) {
                $log = new LeaveBalanceListLog([
                    'leave_balance_list_id' => $leaveBalanceList->id,
                    'reason' => 'Balance update',
                    'balance_before' => $leaveBalanceList->getOriginal('balance') ?? 0,
                    'balance_after' => $leaveBalanceList->balance,
                ]);

                $log->save();
            }
        });
    }

    public function leaveBalance()
    {
        return $this->belongsTo(LeaveBalance::class);
    }

    public function leaveBalanceListLogs()
    {
        return $this->hasMany(LeaveBalanceListLog::class);
    }

    public function leaveDeductionLogs()
    {
        return $this->hasMany(LeaveDeductionLog::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
