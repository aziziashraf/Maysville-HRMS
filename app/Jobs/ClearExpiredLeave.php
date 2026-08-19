<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Carbon\Carbon;
use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceList;
use App\Models\LeaveType;

class ClearExpiredLeave implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()// : void
    {
        $leaveBalanceLists = LeaveBalanceList::where('status', 1)->get();
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        foreach ($leaveBalanceLists as $leaveBalanceList) {
            $leaveType = $leaveBalanceList->leaveBalance->leaveType;
            $expiryDate = Carbon::createFromFormat('Y-m-d', $leaveBalanceList->expiry_date);

            if($leaveType->carry_forward && $leaveBalanceList->expiry_date == $yesterday && $leaveBalanceList->carried_forward == 0){
                $newBalance = round($leaveBalanceList->balance * $leaveType->carry_forward_limit / 100,2);
                if ($leaveType->carry_forward_timeframe_type == 'day'){
                    $newExpireDate = $expiryDate->addDays($leaveType->carry_forward_timeframe_value)->format('Y-m-d');
                } elseif ($leaveType->carry_forward_timeframe_type == 'week'){
                    $newExpireDate = $expiryDate->addWeeks($leaveType->carry_forward_timeframe_value)->format('Y-m-d');
                } elseif ($leaveType->carry_forward_timeframe_type == 'month'){
                    $newExpireDate = $expiryDate->addMonths($leaveType->carry_forward_timeframe_value)->format('Y-m-d');
                } else {
                    $newExpireDate = $expiryDate;
                }

                $leaveBalanceList->update([
                    'balance' => $newBalance,
                    'expiry_date' => $newExpireDate,
                    'carried_forward' => 1
                ]);
            }

            // if expiry_date is not null and expiry_date is less than today date in Y-m-d format then set status to 0
            if ($leaveBalanceList->expiry_date != null && $leaveBalanceList->expiry_date < Carbon::now()->format('Y-m-d')) {
                $leaveBalanceList->update(['status' => 0]);
            }
        }
    }
}
