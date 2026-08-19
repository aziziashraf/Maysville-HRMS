<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceList;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $leave_type = LeaveType::all();
        return view('leave.leave_type.index')->with([
            'leave_type' => $leave_type,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $leave_types = LeaveType::all();
        return view('leave.leave_type.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $leaveType = LeaveType::find($request->leave_type_id);
        if ($leaveType) {
            $leaveType->update($request->all());
        } else {
            $leaveType = LeaveType::create($request->all());
            
            $employees = User::where('role', 'employee')->where('is_active', 1)->get();
            foreach ($employees as $employee) {
                $leaveBalance = LeaveBalance::create([
                    'user_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                ]);

                if ($leaveType->confirmed_employees_only && !$employee->confirmed_date) {
                    $balance = 0;
                } else {
                    $balance = $leaveType->default_amount;
                }

                if ($leaveType->renew_freq == 'year') {
                    $year = date('Y');
                    $expiry_date = $year . '-12-31';
                } elseif ($leaveType->renew_freq == 'month') {
                    $year = date('Y');
                    $month = date('m');
                    $expiry_date = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
                }

                $leaveBalanceList = LeaveBalanceList::create([
                    'leave_balance_id' => $leaveBalance->id,
                    'balance' => $balance,
                    'year' => $year ?? null,
                    'month' => $month ?? null,
                    'expiry_date' => $expiry_date ?? null,
                ]);
            }
        }
        return redirect()->route('leave_type.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LeaveType  $leave_type
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveType $leave_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LeaveType  $leave_type
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveType $leave_type)
    {   
        $leave_types = LeaveType::where('id', '!=', $leave_type->id)->get();
        return view('leave.leave_type.create')->with(['leave_type' => $leave_type, 'leave_types' => $leave_types]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveType  $leave_type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveType $leave_type)
    {
        $leave_type->update($request->all());
        return redirect()->route('leave_type.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeaveType  $leave_type
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveType $leave_type)
    {
        $leave_type->leaves()->delete();
        $leave_type->delete();
        return redirect()->route('leave_type.index');
    }
}
