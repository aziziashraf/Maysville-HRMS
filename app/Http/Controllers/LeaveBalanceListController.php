<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalanceList;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveBalanceListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LeaveBalance $leaveBalance)
    {
        return view('leave_balance_list.index', compact('leaveBalance'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(LeaveBalance $leaveBalance)
    {
        // year array from 2022 until next2 years
        $yearArray = range(date('Y'), date('Y', strtotime('+2 years')));
        return view('leave_balance_list.create', compact('leaveBalance', 'yearArray'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return $request;
        $leaveType = LeaveType::find($request->leave_type_id);
        $leaveBalanceList = LeaveBalanceList::find($request->leave_balance_list_id);
        if ($leaveBalanceList) {
            // add updated_by
            $request->request->add([
                'updated_by' => auth()->user()->id
            ]);
            $leaveBalanceList->update($request->all());
        } else {
            // add created_by and updated_by
            $request->request->add([
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id
            ]);
            $leaveBalanceList = LeaveBalanceList::create($request->all());
        }

        return redirect()->route('leave_balance_list.index', $leaveBalanceList->leave_balance_id);
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveBalanceList $leaveBalanceList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveBalanceList $leaveBalanceList)
    {
        $yearArray = range(2022, date('Y', strtotime('+2 years')));
        return view('leave_balance_list.create', compact('leaveBalanceList', 'yearArray'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveBalanceList $leaveBalanceList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveBalanceList $leaveBalanceList)
    {
        //
    }
}
