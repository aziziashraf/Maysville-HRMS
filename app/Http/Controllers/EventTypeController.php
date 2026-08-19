<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\Request;

class EventTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $event_type = EventType::all();
        return view('event_type.index')->with([
            'event_type' => $event_type,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('event_type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event_type = EventType::find($request->event_type_id);
        if ($event_type) {
            $event_type->update($request->all());
        } else {
            $event_type = EventType::create($request->all());
        }
        return redirect()->route('event_type.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(EventType $event_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventType $event_type)
    {
        return view('event_type.create')->with([
            'event_type' => $event_type,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventType $event_type)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventType $event_type)
    {
        $event_type->events()->delete();
        $event_type->delete();
        return redirect()->route('event_type.index');
    }
}
