<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalanceTier;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveBalanceTierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(LeaveType $leave_type)
    {
        return view('leave.leave_type.create_tier')->with([
            'leave_type' => $leave_type,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $leave_balance_tier = LeaveBalanceTier::find($request->leave_balance_tier_id);
        if ($leave_balance_tier) {
            $leave_balance_tier->update($request->all());
        } else {
            $leave_balance_tier = LeaveBalanceTier::create($request->all());
        }
        return redirect()->route('leave_type.edit',$leave_balance_tier->leave_type_id);
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveBalanceTier $leave_balance_tier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveBalanceTier $leave_balance_tier)
    {
        return view('leave.leave_type.create_tier')->with([
            'leave_balance_tier' => $leave_balance_tier,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveBalanceTier $leave_balance_tier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveBalanceTier $leave_balance_tier)
    {
        $leave_balance_tier->delete();
        return redirect()->route('leave_type.edit',$leave_balance_tier->leave_type_id);
    }
}
