<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserAttendance;
use App\Models\EmployeeDailyAttendance;
use App\Models\Department;
use App\Models\NotificationTarget;
use App\Models\Notification;
use App\Models\Event;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Claim;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Bouncer;

use App\Http\Controllers\LeaveController;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if(Bouncer::is($user)->a('superadmin')){
            return redirect()->route('managementIndex');
        }

        $numberOfMonthsInGraph = 3;
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        $currentMonthName = Carbon::createFromDate($currentYear, $currentMonth)->format('F Y');

        $notificationIDs = NotificationTarget::whereHas('notification', function ($query) {
            $query->where('status', 'Sent');
        })->where('user_id', $user->id)
        ->pluck('notification_id');

        $notifications = Notification::whereIn('id', $notificationIDs)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $start = date($currentYear.'-'.$currentMonth.'-01');
        $end = date($currentYear.'-'.$currentMonth.'-t');

        $attendance = EmployeeDailyAttendance::where('user_id', $user->id)
        ->whereIn('status', ['Present', 'Late', 'Absent', 'On Leave', 'Incomplete Data'])
        ->whereBetween('check_in_date', [$start, $end])
        ->get();

        $groupedAttendance = $attendance->groupBy('status')->map->count();
        $AScolors = array();
        $ASchartvalue = array();
        $ASlabel = array();

        foreach ($groupedAttendance as $key => $value) {
            array_push($ASchartvalue, $value);
            array_push($ASlabel, $key);
            if ($key == 'Present') {
                array_push($AScolors, '#4CAF50');
            } elseif ($key == 'Late') {
                array_push($AScolors, '#FFC107');
            } elseif ($key == 'Absent') {
                array_push($AScolors, '#F44336');
            } elseif ($key == 'On Leave') {
                array_push($AScolors, '#2196F3');
            } elseif ($key == 'Incomplete Data') {
                array_push($AScolors, '#505050');
            }
        }

        $ASdata = [
            'AScolors' => $AScolors,
            'ASchartvalue' => $ASchartvalue,
            'ASlabel' => $ASlabel,
        ];

        $leaves = Leave::where('user_id', $user->id)
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
        ->orderBy('start_date', 'asc')
        ->get();

        $groupedLeaves = $leaves->groupBy('status')->map->count();

        $currentDate = Carbon::createFromDate($currentYear, $currentMonth, 1); // Set the desired month and the 1st day of that month

        $monthsCurrentDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->subMonth($numberOfMonthsInGraph);

        // Calculate the last three months from the current date
        $lastThreeMonths = [];
        $lastThreeMonthsNumber = [];

        // loop for $numberOfMonthsInGraph times
        for ($i = 0; $i < $numberOfMonthsInGraph; $i++) {
            $monthNumber = $monthsCurrentDate->addMonth()->format('Y-m');
            $lastThreeMonthsNumber[] = $monthNumber;
            $lastThreeMonths[] = $monthsCurrentDate->format('F Y');
        }

        // return $lastThreeMonths;

        // Set the end of the specified month as the monthly end
        $monthlyEnd = $currentDate->endOfMonth()->format('Y-m-d');
        
        // Set the start of the third-to-last month as the monthly start
        $monthlyStart  = $currentDate->startOfMonth()->subMonths($numberOfMonthsInGraph-1)->format('Y-m-d');

        $monthlyAttendance = EmployeeDailyAttendance::where('user_id', $user->id)
        ->whereIn('status', ['Present', 'Late', 'Absent', 'On Leave', 'Incomplete Data'])
        ->whereBetween('check_in_date', [$monthlyStart, $monthlyEnd])
        ->get();

        // return $monthlyAttendance;

        // Initialize arrays to hold the counts for each status
        $statusCounts = [
            'Present' => array_fill(0, $numberOfMonthsInGraph, 0),
            'Late' => array_fill(0, $numberOfMonthsInGraph, 0),
            'Absent' => array_fill(0, $numberOfMonthsInGraph, 0),
            'On Leave' => array_fill(0, $numberOfMonthsInGraph, 0),
            'Incomplete Data' => array_fill(0, $numberOfMonthsInGraph, 0)
        ];

        // return $statusCounts;

        // Loop through the attendance data and populate the statusCounts arrays
        foreach ($monthlyAttendance as $entry) {
            $checkInDate = date('Y-m-d', strtotime($entry['check_in_date']));
            $month = date('Y-m', strtotime($checkInDate));
            // return $month;
            $status = $entry['status'];
            
            if (isset($statusCounts[$status]) && in_array($month, $lastThreeMonthsNumber)) {
                $monthIndex = array_search($month, $lastThreeMonthsNumber);
                $statusCounts[$status][$monthIndex]++;
            }
        }

        // Construct the series array for ApexCharts
        $series = [];
        foreach ($statusCounts as $status => $counts) {
            $series[] = [
                'name' => $status,
                'data' => $counts
            ];
        }

        // Your ApexCharts configuration
        $MAdata = [
            'series' => $series,
            'xaxis' => [
                'categories' => $lastThreeMonths
            ],
            'colors' => ['#4CAF50', '#FFC107', '#F44336', '#2196F3', '#505050']
        ];

        return view('home')
            ->with('notifications', $notifications)
            ->with('groupedLeaves', $groupedLeaves)
            ->with('currentMonthName', $currentMonthName)
            ->with('ASdata', $ASdata)
            ->with('MAdata', $MAdata);
    }

    public function managementIndex(Request $request)
    {
        $user = Auth::user();
        if(Bouncer::is($user)->notA('management', 'hod', 'superadmin')){
            return redirect()->route('index');
        }

        $department_id = $request->query('department_id');

        $filter =[
            'department_id'=>$department_id, 
        ];

        $numberOfMonthsInGraph = 3;
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        $currentMonthName = Carbon::createFromDate($currentYear, $currentMonth)->format('F Y');

        $start = date($currentYear.'-'.$currentMonth.'-01');
        $end = date($currentYear.'-'.$currentMonth.'-t');

        $attendance = EmployeeDailyAttendance::whereIn('status', ['Present', 'Late', 'Absent', 'On Leave', 'Incomplete Data'])
        ->whereBetween('check_in_date', [$start, $end]);

        if($user->can('show-own-department-only')){
            $attendance = $attendance->whereHas('user', function ($query) use($user) {
                $query->where('department_id',$user->department_id);
            })->get();
        } elseif($department_id){
            $attendance = $attendance->whereHas('user', function ($query) use($department_id) {
                $query->where('department_id',$department_id);
            })->get();
        } else {
            $attendance = $attendance->get();
        }

        $groupedAttendance = $attendance->groupBy('status')->map->count();

        $AScolors = array();
        $ASchartvalue = array();
        $ASlabel = array();

        foreach ($groupedAttendance as $key => $value) {
            array_push($ASchartvalue, $value);
            array_push($ASlabel, $key);
            if ($key == 'Present') {
                array_push($AScolors, '#4CAF50');
            } elseif ($key == 'Late') {
                array_push($AScolors, '#FFC107');
            } elseif ($key == 'Absent') {
                array_push($AScolors, '#F44336');
            } elseif ($key == 'On Leave') {
                array_push($AScolors, '#2196F3');
            } elseif ($key == 'Incomplete Data') {
                array_push($AScolors, '#505050');
            }
        }

        $ASdata = [
            'AScolors' => $AScolors,
            'ASchartvalue' => $ASchartvalue,
            'ASlabel' => $ASlabel,
        ];

        $leaves = Leave::where('status', 'approved')->where(function ($query) use ($start, $end) {
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
        });

        if($user->can('show-own-department-only')){
            $leaves = $leaves->whereHas('user', function ($query) use($user) {
                $query->where('department_id',$user->department_id);
            })->get();
        } elseif($department_id){
            $leaves = $leaves->whereHas('user', function ($query) use($department_id) {
                $query->where('department_id',$department_id);
            })->get();
        } else {
            $leaves = $leaves->get();
        }

        $groupedLeaves = $leaves->groupBy('leave_type_id')->map->count();

        $LTDcolors = array();
        $LTDchartvalue = array();
        $LTDlabel = array();
        foreach ($groupedLeaves as $leaveTypeID => $leaveTypeCount){
            $leaveType = LeaveType::find($leaveTypeID);
            array_push($LTDchartvalue, $leaveTypeCount);
            array_push($LTDlabel, $leaveType->name);
            array_push($LTDcolors, $leaveType->label_color);
        }

        $LTDdata = [
            'LTDcolors' => $LTDcolors,
            'LTDchartvalue' => $LTDchartvalue,
            'LTDlabel' => $LTDlabel,
        ];

        $currentDate = Carbon::createFromDate($currentYear, $currentMonth, 1); // Set the desired month and the 1st day of that month

        $monthsCurrentDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->subMonth($numberOfMonthsInGraph);

        // Calculate the last three months from the current date
        $lastThreeMonths = [];
        $lastThreeMonthsNumber = [];

        // loop for $numberOfMonthsInGraph times
        for ($i = 0; $i < $numberOfMonthsInGraph; $i++) {
            $monthNumber = $monthsCurrentDate->addMonth()->format('Y-m');
            $lastThreeMonthsNumber[] = $monthNumber;
            $lastThreeMonths[] = $monthsCurrentDate->format('F Y');
        }

        // return $lastThreeMonths;

        // Set the end of the specified month as the monthly end
        $monthlyEnd = $currentDate->endOfMonth()->format('Y-m-d');
        
        // Set the start of the third-to-last month as the monthly start
        $monthlyStart  = $currentDate->startOfMonth()->subMonths($numberOfMonthsInGraph-1)->format('Y-m-d');

        $monthlyAttendance = EmployeeDailyAttendance::whereIn('status', ['Present', 'Late', 'Absent', 'On Leave', 'Incomplete Data'])
        ->whereBetween('check_in_date', [$monthlyStart, $monthlyEnd]);

        if($user->can('show-own-department-only')){
            $monthlyAttendance = $monthlyAttendance->whereHas('user', function ($query) use($user) {
                $query->where('department_id',$user->department_id);
            })->get();
        } elseif ($department_id){
            $monthlyAttendance = $monthlyAttendance->whereHas('user', function ($query) use($department_id) {
                $query->where('department_id',$department_id);
            })->get();
        } else {
            $monthlyAttendance = $monthlyAttendance->get();
        }

        // return $monthlyAttendance;

        // Initialize arrays to hold the counts for each status
        $statusCounts = [
            'Present' => array_fill(0, $numberOfMonthsInGraph, 0),
            'Late' => array_fill(0, $numberOfMonthsInGraph, 0),
            'Absent' => array_fill(0, $numberOfMonthsInGraph, 0),
            'On Leave' => array_fill(0, $numberOfMonthsInGraph, 0),
            'Incomplete Data' => array_fill(0, $numberOfMonthsInGraph, 0)
        ];

        // return $statusCounts;

        // Loop through the attendance data and populate the statusCounts arrays
        foreach ($monthlyAttendance as $entry) {
            $checkInDate = date('Y-m-d', strtotime($entry['check_in_date']));
            $month = date('Y-m', strtotime($checkInDate));
            // return $month;
            $status = $entry['status'];
            
            if (isset($statusCounts[$status]) && in_array($month, $lastThreeMonthsNumber)) {
                $monthIndex = array_search($month, $lastThreeMonthsNumber);
                $statusCounts[$status][$monthIndex]++;
            }
        }

        // Construct the series array for ApexCharts
        $series = [];
        foreach ($statusCounts as $status => $counts) {
            $series[] = [
                'name' => $status,
                'data' => $counts
            ];
        }

        // Your ApexCharts configuration
        $MAdata = [
            'series' => $series,
            'xaxis' => [
                'categories' => $lastThreeMonths
            ],
            'colors' => ['#4CAF50', '#FFC107', '#F44336', '#2196F3', '#505050']
        ];

        if($user->position && $user->position->leave_reviewer){
            $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))->pluck('id');

            $leaveRequests = Leave::whereIn('user_id', $employees)->where('status', 'submitted')->count();
            $claimRequests = Claim::whereIn('user_id', $employees)->where('status', 'submitted')->count();
            $overtimeRequests = Overtime::whereIn('user_id', $employees)->whereIn('status', ['requested', 'submitted'])->count();
        } elseif ($user->position && $user->position->leave_reviewer) {
            $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))->pluck('id');

            $leaveRequests = Leave::whereIn('user_id', $employees)->whereIn('status', ['submitted', 'reviewed'])->count();
            $claimRequests = Claim::whereIn('user_id', $employees)->whereIn('status', ['submitted', 'reviewed'])->count();
            $overtimeRequests = Overtime::whereIn('user_id', $employees)->whereIn('status', ['requested', 'submitted', 'reviewed'])->count();
        } else {
            $leaveRequests = Leave::whereIn('status', ['submitted', 'reviewed'])->count();
            $claimRequests = Claim::whereIn('status', ['submitted', 'reviewed'])->count();
            $overtimeRequests = Overtime::whereIn('status', ['requested', 'submitted', 'reviewed'])->count();
        }

        // today in Y-m-d format
        $today = Carbon::now()->format('Y-m-d');
        // $today = '2023-08-05';

        if($user->can('show-own-department-only')){
            $activeEmployees = User::where('is_active', 1)->where('department_id', $user->department_id)->pluck('name', 'id');
            $dailyScanDepartment = Department::where('id', $user->department_id)->pluck('department_name', 'id');
        } elseif ($department_id){
            $activeEmployees = User::where('is_active', 1)->where('department_id', $department_id)->pluck('name', 'id');
            $dailyScanDepartment = Department::where('id', $department_id)->pluck('department_name', 'id');
        } else {
            $activeEmployees = User::where('is_active', 1)->whereNotNull('department_id')->pluck('name', 'id');
            $dailyScanDepartment = Department::pluck('department_name', 'id');
        }

        $departmentAttendance = [];

        foreach ($activeEmployees as $employeeID => $employeeName) {
            $employeeScan = UserAttendance::where('user_id', $employeeID)
                ->where('scan_date', $today)
                ->orderBy('scan_time', 'asc')
                ->first();

            $departmentName = User::find($employeeID)->department->department_name;

            if (!isset($departmentAttendance[$departmentName])) {
                $departmentAttendance[$departmentName] = [
                    'Scanned' => array(),
                    'On Leave' => array(),
                    'Haven\'t Scan' => array(),
                ];
            }

            if ($employeeScan) {
                $departmentAttendance[$departmentName]['Scanned'][] = $employeeName;
            } else {
                $leave = (new LeaveController())->checkLeave($today, $employeeID);
                if ($leave){
                    $departmentAttendance[$departmentName]['On Leave'][] = $employeeName;;
                } else {
                    $departmentAttendance[$departmentName]['Haven\'t Scan'][] = $employeeName;;
                }
            }
        }

        // foreach ($dailyScanDepartment as $departmentID => $departmentName) {
        //     if (!isset($departmentAttendance[$departmentName])) {
        //         $departmentAttendance[$departmentName] = [
        //             'Scanned' => array(),
        //             'On Leave' => array(),
        //             'Haven\'t Scan' => array(),
        //         ];
        //     }
        // }

        $departmentDailyScan = [];

        foreach ($departmentAttendance as $departmentName => $attendanceData) {
            $departmentDailyScan[$departmentName] = [
                'Scanned' => $attendanceData['Scanned'],
                'On Leave' => $attendanceData['On Leave'],
                'Haven\'t Scan' => $attendanceData['Haven\'t Scan'],
            ];
        }

        return view('managementHome')
            ->with('currentMonthName', $currentMonthName)
            ->with('ASdata', $ASdata)
            ->with('MAdata', $MAdata)
            ->with('LTDdata', $LTDdata)
            ->with('leaveRequests', $leaveRequests)
            ->with('claimRequests', $claimRequests)
            ->with('overtimeRequests', $overtimeRequests)
            ->with('departmentDailyScan', $departmentDailyScan)
            ->with('departments', Department::all())
            ->with('filter', $filter);
    }

    public function calendar()
    {
        // today's date in 2023-07-30 format
        $start = date('Y-m-d', strtotime('now'));
        // end is one week after
        $end = date('Y-m-d', strtotime('+2 week', strtotime('now')));

        // return $end;
        
        $events = Event::where(function ($query) use ($start, $end) {
            $query->where(function ($query) use ($start, $end) {
                $query->where('date_from', '>=', $start)
                    ->where('date_from', '<=', $end);
            })->orWhere(function ($query) use ($start, $end) {
                $query->where('date_to', '>=', $start)
                    ->where('date_to', '<=', $end);
            })->orWhere(function ($query) use ($start, $end) {
                $query->where('date_from', '<=', $start)
                    ->where('date_to', '>=', $start);
            });
        })
        ->orderBy('date_from', 'asc')
        ->get();
        
        // return $events;
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
        ->orderBy('start_date', 'asc')
        ->with('leaveType')
        ->get();

        return view('calendar')->with('events', $events)->with('leaves', $leaves);
    }


    public function attendance(Request $request)
    {
        $attendance = [];

        $date_range = $request->query('date_range');
        if ($date_range) {
            $dates = explode(" to ", $date_range);
            $date_from = $dates[0];
            $date_to = isset($dates[1]) ? $dates[1] : $date_from;
        } else {
            $date_from = date('Y-m-01', strtotime('now'));
            $date_to = date('Y-m-d', strtotime('now'));
            $date_range = $date_from . " to " . $date_to;
        }

        $filter =[
            'date_range'=>$date_range,
            'date_from'=>$date_from, 
            'date_to'=>$date_to, 
        ];
        $user = Auth::user();

        if (!empty($date_from) && !empty($date_to)) {
            $attendance = EmployeeDailyAttendance::whereBetween('check_in_date', [$date_from, $date_to])->where('user_id',$user->id)->orderBy('check_in_date','desc')->get();
        }else{
            $attendance = EmployeeDailyAttendance::where('user_id',$user->id)->orderBy('check_in_date','desc')->get();
        }

        return view('attendance')->with('attendance',$attendance)->with('filter',$filter);
    }

    public function daily_scan(Request $request)
    {
        $attendance = [];
        $currentDateTime = Carbon::now()->format('Y-m-d');

        $date_range = $request->query('date_range');
        if ($date_range) {
            $dates = explode(" to ", $date_range);
            $date_from = $dates[0];
            $date_to = isset($dates[1]) ? $dates[1] : $date_from;
        } else {
            $date_from = date('Y-m-01', strtotime('now'));
            $date_to = date('Y-m-d', strtotime('now'));
            $date_range = $date_from . " to " . $date_to;
        }

        $filter =[
            'date_range'=>$date_range,
            'date_from'=>$date_from, 
            'date_to'=>$date_to, 
        ];
        
        $user = Auth::user();
        if (!empty($date_from) && !empty($date_to)) {
            $attendance = UserAttendance::whereBetween('scan_date', [$date_from, $date_to])->where('user_id',$user->id)->orderBy('scan_datetime','desc')->get();

        }else{
            $attendance = UserAttendance::where('scan_date',$currentDateTime)->where('user_id',$user->id)->orderBy('scan_datetime','desc')->get();
        }
        return view('daily_scan')->with('attendance',$attendance)->with('filter',$filter);
    }
}
