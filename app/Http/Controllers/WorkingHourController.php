<?php

namespace App\Http\Controllers;

use App\Models\WorkingHour;
use App\Models\WorkingHourDays;
use Illuminate\Http\Request;

class WorkingHourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $working_hour = WorkingHour::all();
        foreach($working_hour as $row){
            $row->working_hour_days=$row->workHourDays->pluck('days')->all();
        }
        return view('working_hour.index')->with('working_hour',$working_hour);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addNewDiv()
    {
        $working_hour = WorkingHour::create();
        return redirect()->route('working_hour.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(isset($request->idss)&&count($request->idss)>0){
            foreach($request->idss as $row){
                $shift_label = 'shift_label_'.$row;
                $break_start_time = 'break_start_time_'.$row;
                $start_time = 'start_time_'.$row;
                $break_end_time = 'break_end_time_'.$row;
                $end_time = 'end_time_'.$row;
                $working_days = 'working_days_'.$row;
                $data =[
                    'shift_label'=>$request->$shift_label,
                    'break_start_time'=>$request->$break_start_time,
                    'start_time'=>$request->$start_time,
                    'break_end_time'=>$request->$break_end_time,
                    'end_time'=>$request->$end_time,
                ];
                $working_hour = WorkingHour::find($row);
                $working_hour->update($data);
                $findDays = WorkingHourDays::where('working_hour_id',$row)->delete();
                foreach($request->$working_days as $w){
                    WorkingHourDays::create(['working_hour_id'=>$row,'days'=>$w]);
                }
                
            }
        }
        
        return redirect()->route('working_hour.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkingHour  $workingHour
     * @return \Illuminate\Http\Response
     */
    public function show(WorkingHour $workingHour)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkingHour  $workingHour
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkingHour $workingHour)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkingHour  $workingHour
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WorkingHour $workingHour)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkingHour  $workingHour
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkingHour $workingHour)
    {
        $workingHour->delete();
        return redirect()->back();
    }
}
