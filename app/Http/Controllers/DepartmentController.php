<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Access;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter');
		if (!empty($filter)) {
            $department = Department::where('department_name', 'like', '%'.$filter.'%')->get();
        }else{
            $department = Department::all();
        }


        return view('department.index')->with('department',$department)->with('filter',$filter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $department = [];
        $checkInAccess = Access::groupBy('access_name')->select('access_name')->get();
        return view('department.create')->with('department',$department)->with('checkInAccess',$checkInAccess);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->department_id >0){
            $department=Department::find($request->department_id);
            $department->update($request->all());
        }else{
            Department::create($request->all());
        }
        return redirect()->route('department.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function edit(Department $department)
    {
        $checkInAccess = Access::groupBy('access_name')->select('access_name')->get();
        return view('department.create')->with('department',$department)->with('checkInAccess',$checkInAccess);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Department $department)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->back();
    }
}
