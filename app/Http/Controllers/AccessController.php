<?php

namespace App\Http\Controllers;

use App\Models\Access;
use Illuminate\Http\Request;

class AccessController extends Controller
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
            $access = Access::where(function($query) use($filter) {
                $query->where('access_name', 'like', '%'.$filter.'%')
                      ->orWhere('activity', 'like', '%'.$filter.'%')
                      ->orWhere('access_level', 'like', '%'.$filter.'%');
            })
            ->get();
        }else{
            $access = Access::all();
        }

        // $access->appends(['filter' => $filter]);

        return view('access.index')->with('access',$access)->with('filter',$filter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $access = [];
        return view('access.create')->with('access',$access);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->acs_id >0){
            $access=Access::find($request->acs_id);
            $access->update($request->all());
        }else{
            Access::create($request->all());
        }
        return redirect()->route('access.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Access  $access
     * @return \Illuminate\Http\Response
     */
    public function show(Access $access)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Access  $access
     * @return \Illuminate\Http\Response
     */
    public function edit(Access $access)
    {
        return view('access.create')->with('access',$access);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Access  $access
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Access $access)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Access  $access
     * @return \Illuminate\Http\Response
     */
    public function destroy(Access $access)
    {
        $access->delete();
        return redirect()->back();
    }
}
