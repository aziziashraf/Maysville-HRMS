<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class LeaveBalanceListLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'leave_balance_list_id',
        'reason',
        'balance_before',
        'balance_after',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by = Auth::check() ? Auth::user()->id : NULL;
            $model->updated_by = Auth::check() ? Auth::user()->id : NULL;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::check() ? Auth::user()->id : NULL;
        });

        static::deleting(function ($model) {
            $model->deleted_by = Auth::check() ? Auth::user()->id : NULL;
            $model->save();
        });
    }

    public function leaveBalanceList()
    {
        return $this->belongsTo(LeaveBalanceList::class);
    }
}
