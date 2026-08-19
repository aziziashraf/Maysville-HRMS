<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use Bouncer;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $position = Position::all();
        return view('position.index')->with([
            'position' => $position,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $department = Department::all();
        $role = Bouncer::role()->all();
        return view('position.create')->with([
            'department' => $department,
            'role' => $role,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $position = Position::find($request->position_id);
        if ($position) {
            $oldRoleID = $position->role_id;
            $position->update($request->all());

            if($oldRoleID != $position->role_id) {
                $oldRole = Bouncer::role()->find($oldRoleID);
                $newRole = Bouncer::role()->find($position->role_id);
                $users = $position->users;

                foreach ($users as $user) {
                    $user->retract($oldRole->name);
                    $user->assign($newRole->name);
                }
            }
        } else {
            $position = Position::create($request->all());
        }
        return redirect()->route('position.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        $department = Department::all();
        $role = Bouncer::role()->all();
        return view('position.create')->with([
            'position' => $position,
            'department' => $department,
            'role' => $role,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        // Find all the users who have the given position_id
        $users = $position->users;
        
        // Loop through each user and unassign the role associated with the position
        foreach ($users as $user) {
            $role = $position->role;

            if ($role) {
                $user->retract($role->name);
            }
            
            // Unassign the position from the user
            $user->position_id = null;
            $user->save();
        }
        
        // Delete the position record
        $position->delete();
        return redirect()->route('position.index');
    }

    public function getPosition(Department $department)
    {
        $position = $department->position;
        return response()->json($position);
    }
}
