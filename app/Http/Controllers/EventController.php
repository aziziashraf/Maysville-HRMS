<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Leave;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $event = Event::all();
        return view('event.index')->with([
            'event' => $event,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $event_type = EventType::all();
        return view('event.create')->with([
            'event_type' => $event_type,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return $request;
        $date_range = $request->date_range;
        $dates = explode(" to ", $date_range);
        $request->request->add(['date_from' => $dates[0]]);
        $request->request->add(['date_to' => isset($dates[1]) ? $dates[1] : null]);

        $event = Event::find($request->event_id);
        if ($event) {
            $event->update($request->all());
        } else {
            $event = Event::create($request->all());
        }
        return redirect()->route('event.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $event_type = EventType::all();
        $event->date_range = $event->date_to ? $event->date_from . ' to ' . $event->date_to : $event->date_from;

        return view('event.create')->with([
            'event' => $event,
            'event_type' => $event_type,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('event.index');
    }

    public function calendarEvent (Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        
        $events = Event::where(function($query) use ($start, $end) {
                        $query->where('date_from', '>=', $start)
                            ->orWhere(function($query) use ($start, $end) {
                                $query->where('date_to', '>=', $start)
                                        ->where('date_from', '<=', $end);
                            });
                    })
                    ->orWhere(function($query) use ($start, $end) {
                        $query->where('date_from', '<=', $start)
                            ->where('date_to', '>=', $start);
                    })
                    ->get();


        $leaves = Leave::where('status', 'approved')
        ->where(function ($query) use ($start, $end) {
            $query->where(function ($query) use ($start, $end) {
                $query->where('start_date', '>=', $start)
                    ->where('start_date', '<=', $end);
            })->orWhere(function ($query) use ($start, $end) {
                $query->where('end_date', '>=', $start)
                    ->where('end_date', '<=', $end);
            })->orWhere(function ($query) use ($start, $end) {
                $query->where('start_date', '<=', $start)
                    ->where('end_date', '>=', $start);
            });
        })
        ->with('leaveType')
        ->get();

        $eventsJSON = [];

        foreach ($events as $event) {
            $eventsJSON[] = [
                'id' => $event->id,
                'title' => $event->name,
                'start' => $event->date_from,
                'end' => $event->date_to,
                'color' => $event->event_type->color,
                // 'url' => route('event.edit', $event->id) ?? '#',
                'extendedProps' => [
                    'description' => $event->description,
                ]
            ];
        }

        foreach ($leaves as $leave) {
            if (isset($leave->start_time) && isset($leave->end_time)) {
                $start = $leave->start_date . 'T' . $leave->start_time;
                $end = $leave->start_date . 'T' . $leave->end_time;
                // description to show time
                $start_time = date('h:i A', strtotime($leave->start_time));
                $end_time = date('h:i A', strtotime($leave->end_time));
                $description = $start_time . ' to ' . $end_time;
            } else {
                $start = $leave->start_date;
                $end = $leave->end_date;
                // description to show date
                if (isset($leave->end_date)) {
                    $start_date = date('d F Y', strtotime($leave->start_date));
                    $end_date = date('d F Y', strtotime($leave->end_date));
                    $description = $start_date . ' - ' . $end_date;
                } else {
                    $description = 'All Day';
                }
            }

            $eventsJSON[] = [
                'id' => $leave->id,
                'title' => $leave->user->name  . ' on ' . $leave->leaveType->name,
                'start' => $start,
                'end' => $end,
                'color' => '#808080',
                // 'url' => route('leave.edit', $leave->id) ?? '#',
                'extendedProps' => [
                    'description' => $description,
                ]
            ];
        }

        return response()->json($eventsJSON);
    }
}
