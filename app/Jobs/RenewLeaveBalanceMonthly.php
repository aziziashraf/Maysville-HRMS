<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceList;
use App\Models\LeaveType;

class RenewLeaveBalanceMonthly implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    public function handle()
    {
        $employees = User::where('role','employee')->where('is_active',1)->get();
        $echoArray = [];
        foreach ($employees as $employee) {
            $confirmed_date = $employee->confirmed_date;
            $years_of_service = $confirmed_date ? Carbon::createFromFormat('Y-m-d', $confirmed_date)->diffInYears(Carbon::now()) : 0;

            $leaveTypes = LeaveType::where('renew_freq', 'month')->get();

            foreach ($leaveTypes as $leaveType) {
                if ($leaveType->confirmed_employees_only && !$confirmed_date) {
                    continue;
                }
                $leaveBalance = LeaveBalance::firstOrCreate([
                    'user_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                ]);

                if ($leaveType->leaveBalanceTiers->isNotEmpty()) {
                    $leave_balance_tier = $leaveType->leaveBalanceTiers()->where('years_of_service', '<=', $years_of_service)->orderBy('years_of_service', 'desc')->first();
                    $balance = $leave_balance_tier ? $leave_balance_tier->amount : $leaveType->default_amount;
                } else {
                    $balance = $leaveType->default_amount;
                }

                $leaveBalanceList = LeaveBalanceList::create([
                    'leave_balance_id' => $leaveBalance->id,
                    'balance' => $balance,
                    'month' => date('m'),
                    'year' => date('Y'),
                    'expiry_date' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                ]);

                // $echoArray[] = [
                //     'confirmed_employees_only' => $leaveType->confirmed_employees_only,
                //     'confirmed_date' =>$employee->confirmed_date,
                //     'employee_id' => $employee->id,
                //     'leave_type_id' => $leaveType->id,
                //     'renew_freq' => $leaveType->renew_freq,
                //     'years_of_service' => $years_of_service,
                //     'leave_balance_id' => $leaveBalance->id,
                //     'balance' => $balance . $leaveType->balance_unit ?? "whathappen?",
                //     'month' => date('m'),
                //     'year' => date('Y'),
                //     'expiry_date' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                // ];
            }
        }
        // echo '<pre>';
        // echo var_dump($echoArray);
        // echo '</pre>';
        // echo json_encode($echoArray);
    }
}
