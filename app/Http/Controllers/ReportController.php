<?php

namespace App\Http\Controllers;

use App\Models\UserAttendance;
use App\Models\Department;
use App\Models\EmployeeDailyAttendance;
use App\Models\User;
use App\Models\Access;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\BuildingExport;
use App\Exports\EmployeeExport;
use App\Jobs\GenerateAttendance;
use App\Jobs\SyncData;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function building_access_index(Request $request)
    {
        $user = Auth::user();
        $currentDateTime = Carbon::now()->format('Y-m-d');
        
        $date_range = $request->query('date_range');
        if ($date_range) {
            $dates = explode(" to ", $date_range);
            $date_from = $dates[0];
            $date_to = isset($dates[1]) ? $dates[1] : $date_from;
        } else {
            $date_from = date('Y-m-d', strtotime('now'));
            $date_to = $date_from;
            $date_range = $date_from;
        }

        $role = $request->query('role');
        $access_id = $request->query('access_id');
        $department_id = $request->query('department_id');
        $search = $request->query('search');
        $filter =[
            'date_from'=>$date_from, 
            'date_to'=>$date_to,
            'date_range'=>$date_range,
            'role'=>$role, 
            'access_id'=>$access_id, 
            'department_id'=>$department_id, 
            'search'=>$search, 
        ];

        $attendance = UserAttendance::whereBetween('scan_date', [$date_from, $date_to]);

        if($user->can('show-own-department-only')){
            $attendance = $attendance->whereHas('user', function ($query) use($user) {
                $query->where('department_id',$user->department_id);
            });
        } elseif($department_id){
            $attendance = $attendance->whereHas('user', function ($query) use($department_id) {
                $query->where('department_id',$department_id);
            });
        } else {
            $attendance = $attendance;
        }

        if(!empty($access_id) && $access_id > 0){
            $attendance = $attendance->where('access_id',$access_id);
        }

        if(!empty($search)){
            $attendance = $attendance->whereHas('user', function ($query) use($search) {
                $query->where('name', 'LIKE', '%'.$search.'%')
                ->orWhere('email', 'LIKE', '%'.$search.'%')
                ->orWhere('contact_no', 'LIKE', '%'.$search.'%');
            });
        }
        
        $attendance=$attendance->orderBy('scan_datetime','desc')->get(); 

        $access = Access::all();
        $department = Department::all();
        return view('report.building_access_index')->with('attendance',$attendance)->with('access',$access)->with('department',$department)->with('filter',$filter);
    }

    public function employee_index(Request $request)
    {
        $user = Auth::user();

        $date_range = $request->query('date_range');
        if ($date_range) {
            $dates = explode(" to ", $date_range);
            $date_from = $dates[0];
            $date_to = isset($dates[1]) ? $dates[1] : $date_from;
        } else {
            // $date_from = yesterday()->format('Y-m-d');
            $date_from = date('Y-m-d', strtotime('yesterday'));
            $date_to = $date_from;
            $date_range = $date_from . " to " . $date_to;
        }

        $department_id = $request->query('department_id');
        $status = $request->query('status');
        $search = $request->query('search');
        $filter =[
            'date_from'=>$date_from, 
            'date_to'=>$date_to,
            'date_range'=>$date_range,
            'department_id'=>$department_id, 
            'status'=>$status, 
            'search'=>$search, 
        ];
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $attendance = EmployeeDailyAttendance::whereBetween('check_in_date', [$date_from, $date_to]);

        if($user->can('show-own-department-only')){
            $attendance = $attendance->whereHas('user', function ($query) use($user) {
                $query->where('department_id',$user->department_id);
            });
        } elseif($department_id){
            $attendance = $attendance->whereHas('user', function ($query) use($department_id) {
                $query->where('department_id',$department_id);
            });
        } else {
            $attendance = $attendance;
        }

        if($status <>null && $status <> ''){
            $attendance = $attendance->where('status',$status);
        }

        if(!empty($search)){
            $attendance = $attendance->whereHas('user', function ($query) use($search) {
                $query->where('name', 'LIKE', '%'.$search.'%')
                ->orWhere('staff_id', 'LIKE', '%'.$search.'%');
            });
        }

        $attendance=$attendance->orderBy('check_in_date','desc')->get(); 

        $department = Department::all();

        return view('report.employee_index')->with('attendance',$attendance)->with('department',$department)->with('filter',$filter);
    }

    function sendData(){
        $scan_data = UserAttendance::all();
		SyncData::dispatch($scan_data);
    }

    function generateEmployeeAttendance(){
        $employee = User::where('role','employee')->where('is_active',1)->get();
		GenerateAttendance::dispatch($employee);
    }
    
    public function building_excel($attendance)
    {
       // return (new OutstandingSummary($id))->download('outstanding.xlsx');
        return Excel::download(new BuildingExport($attendance), 'BuildingAccess.xlsx');
    }
    
    public function employee_excel($attendance)
    {
       // return (new OutstandingSummary($id))->download('outstanding.xlsx');
        return Excel::download(new EmployeeExport($attendance), 'EmployeeAccess.xlsx');
    }

    // get coordinates from userattendance table (used in ajax)
    public function getCoordinates(UserAttendance $attendance)
    {
        if (!$attendance) {
            return response()->json([
                'error' => 'Attendance not found',
            ], 404);
        }

        $coordinates = $attendance->coordinates;

        if (!$coordinates) {
            return response()->json([
                'error' => 'Coordinates not available',
            ], 400);
        }

        $result = json_decode($coordinates, true);

        $latitude = $result['coords']['latitude'] ?? null;
        $longitude = $result['coords']['longitude'] ?? null;

        if (!$latitude || !$longitude) {
            return response()->json([
                'error' => 'Invalid coordinates format',
            ], 400);
        }

        $apiKey = env('GOOGLE_MAPS_API_KEY', 'AIzaSyCtiFKWZrg83qeVyU1lvISmJhPfKJMTXkE');
        $mapUrl ="https://www.google.com/maps/embed/v1/place?key=" . $apiKey . "&q=" . $latitude . "," . $longitude . "&zoom=18";
        // $mapUrl = "https://www.google.com/maps/embed/v1/view?key=" . $apiKey . "&center=". $latitude . "," . $longitude . "&zoom=18&markers=color:red%7Clabel:P%7C";

        return response()->json([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'mapUrl' => $mapUrl,
        ]);
    }

}
