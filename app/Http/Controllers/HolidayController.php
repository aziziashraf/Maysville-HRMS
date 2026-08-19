<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
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
            $holiday = Holiday::where('holiday_name', 'like', '%'.$filter.'%')->get();
        }else{
            $holiday = Holiday::all();
        }
        // $holiday->appends(['filter' => $filter]);

        return view('holiday.index')->with('holiday',$holiday)->with('filter',$filter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $holiday = [];
        return view('holiday.create')->with('holiday',$holiday);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $date_range = $request->date_range;
        $dates = explode(" to ", $date_range);
        $request->request->add(['date_from' => $dates[0]]);
        $request->request->add(['date_to' => isset($dates[1]) ? $dates[1] : null]);

        if($request->holiday_id >0){
            $holiday=Holiday::find($request->holiday_id);
            $holiday->update($request->all());
        }else{
            Holiday::create($request->all());
        }
        return redirect()->route('holiday.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function show(Holiday $holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function edit(Holiday $holiday)
    {
        $holiday->date_range = $holiday->end_date ? $holiday->start_date . ' to ' . $holiday->end_date : $holiday->start_date;
        return view('holiday.create')->with('holiday',$holiday);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Holiday $holiday)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->back();
    }
}
