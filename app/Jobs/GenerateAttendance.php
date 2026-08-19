<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\UserAttendance;
use App\Models\Department;
use App\Models\EmployeeDailyAttendance;
use App\Models\User;
use App\Models\Access;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\NotificationPopUp;
use App\Events\NotificationPopUpAdmin;

use App\Http\Controllers\LeaveController;

class GenerateAttendance //implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $employee;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($employee)
    {
        $this->employee = $employee;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$employee = $this->employee;
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $dayOfWeek = Carbon::yesterday()->format('N');

        // $startDate = '2023-07-01';
        // $endDate = '2023-08-07';

        // $yesterdays = array();
        // $currentDate = $startDate;

        // while ($currentDate <= $endDate) {
        //     $yesterdays[] = $currentDate;
        //     $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        // }

        // foreach($yesterdays as $dateString){
        //     $yesterday = Carbon::createFromFormat("Y-m-d", $dateString);
        //     $dayOfWeek = $yesterday->format('N');

        //     $yesterday = $yesterday->format('Y-m-d');

        foreach($employee as $e){
            $getemployeeAtt = EmployeeDailyAttendance::where('user_id',$e->id)->where('check_in_date',$yesterday)->first();
            if(!isset($getemployeeAtt)){
                $clock_in = [];
                $clock_out = [];

                $event = (new LeaveController())->checkEvent($yesterday);
                $eventName = (new LeaveController())->getEventName($yesterday);

                if (!in_array($dayOfWeek, [6, 7])) { // Weekdays
                    if ($event == 'holiday') {
                        $status = "Holiday"; // Holiday
                        $secondary_status = $eventName;
                    }
                } elseif ($dayOfWeek == 6) { // Saturday
                    if ($event == 'holiday') {
                        $status = "Holiday"; // Holiday
                        $secondary_status = $eventName;
                    } elseif ($event != 'work') {
                        $status = "Non Working Saturday";
                    }
                } elseif ($dayOfWeek == 7) { // Sunday
                    $status = "Sunday";
                    if ($event == 'holiday') {
                        $secondary_status = $eventName;
                    }
                }
                
                // check if there is no $status then run the next code
                if(!isset($status)){
                    // check if user is on leave
                    $leave = (new LeaveController())->checkLeave($yesterday, $e->id);
                    $leaves = (new LeaveController())->checkLeaves($yesterday, $e->id);

                    $officeClockIn  = UserAttendance::where('user_id', $e->id)
                        ->where('scan_date', $yesterday)
                        ->orderBy('scan_datetime', 'asc')
                        ->whereNotNull('access_id')
                        ->first();
                    $officeClockOut = UserAttendance::where('user_id', $e->id)
                        ->where('scan_date', $yesterday)
                        ->orderBy('scan_datetime', 'desc')
                        ->whereNotNull('access_id')
                        ->first();

                    // if ($officeClockIn && $officeClockOut && $officeClockIn->id == $officeClockOut->id) {
                    //     $officeClockOut = NULL; // Make $officeClockOut an empty collection
                    // }

                    $homeClockIn = UserAttendance::where('user_id', $e->id)
                        ->where('scan_date', $yesterday)
                        ->orderBy('scan_datetime', 'asc')->where('access_id', null)
                        ->where('location', 'HOME')
                        ->where('scan_status', 'clock-in')
                        ->first();
                    $homeClockOut = UserAttendance::where('user_id', $e->id)
                        ->where('scan_date', $yesterday)
                        ->orderBy('scan_datetime', 'desc')->where('access_id', null)
                        ->where('location', 'HOME')
                        ->where('scan_status', 'clock-out')
                        ->first();

                    $clientVisitClockIn = UserAttendance::where('user_id', $e->id)
                        ->where('scan_date', $yesterday)
                        ->orderBy('scan_datetime', 'asc')->where('access_id', null)
                        ->where('location', 'CLIENT VISIT')
                        ->where('scan_status', 'clock-in')
                        ->first();
                    $clientVisitClockOut = UserAttendance::where('user_id', $e->id)
                        ->where('scan_date', $yesterday)
                        ->orderBy('scan_datetime', 'desc')->where('access_id', null)
                        ->where('location', 'CLIENT VISIT')
                        ->where('scan_status', 'clock-out')
                        ->first();

                    $clockIns = [
                        $officeClockIn,
                        $homeClockIn,
                        $clientVisitClockIn
                    ];

                    // Filter out null and empty collections
                    $filteredClockIns = array_filter($clockIns, function ($clockIn) {
                        return $clockIn !== null && !empty($clockIn);
                    });                    
                    
                    // Sort by scan_datetime in ascending order
                    usort($filteredClockIns, function ($a, $b) {
                        return strtotime($a->scan_datetime) - strtotime($b->scan_datetime);
                    });
                    
                    $clock_in = reset($filteredClockIns); // Get the first clock-in
                    
                    // $clock_in will contain the earliest clock-in among the non-empty collections,
                    // or it will be null if all collections are empty or null
                    
                    $clockOuts = [
                        $officeClockOut,
                        $homeClockOut,
                        $clientVisitClockOut
                    ];
                    
                    // Filter out null and empty collections
                    $filteredClockOuts = array_filter($clockOuts, function ($clockOut) {
                        return $clockOut !== null && !empty($clockOut);
                    });
                    
                    // Sort by scan_datetime in descending order
                    usort($filteredClockOuts, function ($a, $b) {
                        return strtotime($b->scan_datetime) - strtotime($a->scan_datetime);
                    });
                    
                    $clock_out = reset($filteredClockOuts); // Get the last clock-out

                    //make $clock_out null if clock_in and clock_out is same
                    if ($clock_in && $clock_out && $clock_in->id == $clock_out->id) {
                        $clock_out = NULL; // Make $clock_out an empty collection
                    }
                    
                    // $clock_out will contain the latest clock-out among the non-empty collections,
                    // or it will be null if all collections are empty or null
                    
                    $location = '';
                    $level = '';
                    $remarks = '';
                    $visitedLocations = [];
                    $combinedClocks = array_merge($filteredClockIns, $filteredClockOuts);
                    // $combinedClocks will contain the combined array of filtered clock-ins and clock-outs


                    // check either clock_in or clock_out is not empty
                    if (!empty($combinedClocks)) {
                        foreach ($combinedClocks as $clock) {
                            $currentLocation = $clock->location ?: ($clock->access_id !== null ? 'PXS' : '');

                            if (!in_array($currentLocation, $visitedLocations)) {
                                $location .= $currentLocation . ', ';
                                if ($clock->access_id !== null) {
                                    $findAccess = Access::find($clock->access_id);
                                    if(isset($findAccess)){
                                        $level .= $findAccess->access_level . ', ';
                                    }
                                }
                                $visitedLocations[] = $currentLocation;
                            }

                            if ($clock->remarks) {
                                $remarks .= $clock->remarks . '(' . $currentLocation . '), ';
                            }
                        }

                        $location = rtrim($location, ', '); // Remove trailing comma and space
                        $level = rtrim($level, ', '); // Remove trailing comma and space
                        $remarks = rtrim($remarks, ', '); // Remove trailing comma and space
                        // $location will contain a comma-separated string of unique locations from the combined clock-ins and clock-outs
                    }

                    if ($clock_in && $clock_out) {
                        
                        $homeClockIns = UserAttendance::where('user_id', $e->id)
                            ->where('scan_date', $yesterday)
                            ->orderBy('scan_datetime', 'asc')->where('access_id', null)
                            ->where('location', 'HOME')
                            ->where('scan_status', 'clock-in')
                            ->get();
                        $homeClockOuts = UserAttendance::where('user_id', $e->id)
                            ->where('scan_date', $yesterday)
                            ->orderBy('scan_datetime', 'asc')->where('access_id', null)
                            ->where('location', 'HOME')
                            ->where('scan_status', 'clock-out')
                            ->get();

                        if ($homeClockIns->isNotEmpty()) {
                            // Code to execute if the collection is not empty
                            $homeDuration = '';
                            foreach ($homeClockIns as $key => $homeCI) {
                                $homeDuration .= 'HOME | ';

                                $homeStartTime = strtotime($homeCI->scan_time);
                                // check if $homeClockOuts[$key] is not empty
                                if (isset($homeClockOuts[$key])) {
                                    $homeEndTime = strtotime($homeClockOuts[$key]->scan_time);
                                    $homeDurationInSeconds = $homeEndTime - $homeStartTime;
                                    $homeHours = floor($homeDurationInSeconds / 3600);
                                    $homeMinutes = floor(($homeDurationInSeconds % 3600) / 60);

                                    $homeDuration .= $homeCI->scan_time . ' - ' . $homeClockOuts[$key]->scan_time . ' (' . $homeHours . ' hr ' . $homeMinutes . ' mins), ';
                                } else {
                                    $homeDuration .= $homeCI->scan_time . ' - (Incomplete data - Missing clock-out),';
                                }
                            }

                            $homeDuration = rtrim($homeDuration, ', '); // Remove trailing comma and space
                        }

                        $clientVisitClockIns = UserAttendance::where('user_id', $e->id)
                            ->where('scan_date', $yesterday)
                            ->orderBy('scan_datetime', 'asc')->where('access_id', null)
                            ->where('location', 'CLIENT VISIT')
                            ->where('scan_status', 'clock-in')
                            ->get();
                        $clientVisitClockOuts = UserAttendance::where('user_id', $e->id)
                            ->where('scan_date', $yesterday)
                            ->orderBy('scan_datetime', 'asc')->where('access_id', null)
                            ->where('location', 'CLIENT VISIT')
                            ->where('scan_status', 'clock-out')
                            ->get();

                        if ($clientVisitClockIns->isNotEmpty()) {
                            // Code to execute if the collection is not empty
                            $clientVisitDuration = '';
                            foreach ($clientVisitClockIns as $key => $clientVisitCI) {
                                $clientVisitDuration .= 'CLIENT VISIT | ';

                                $clientVisitStartTime = strtotime($clientVisitCI->scan_time);
                                // check if $clientVisitClockOuts[$key] is not empty
                                if (isset($clientVisitClockOuts[$key])) {
                                    $clientVisitEndTime = strtotime($clientVisitClockOuts[$key]->scan_time);
                                    $clientVisitDurationInSeconds = $clientVisitEndTime - $clientVisitStartTime;
                                    $clientVisitHours = floor($clientVisitDurationInSeconds / 3600);
                                    $clientVisitMinutes = floor(($clientVisitDurationInSeconds % 3600) / 60);

                                    $clientVisitDuration .= $clientVisitCI->scan_time . ' - ' . $clientVisitClockOuts[$key]->scan_time . ' (' . $clientVisitHours . ' hr ' . $clientVisitMinutes . ' mins), ';
                                } else {
                                    $clientVisitDuration .= $clientVisitCI->scan_time . ' - (Incomplete data - Missing clock-out),';
                                }
                            }

                            $clientVisitDuration = rtrim($clientVisitDuration, ', '); // Remove trailing comma and space
                        }

                        if (isset($homeDuration) && isset($clientVisitDuration)) {
                            $remote_working_duration = $homeDuration . ', ' . $clientVisitDuration;
                        } elseif (isset($homeDuration)) {
                            $remote_working_duration = $homeDuration;
                        } elseif (isset($clientVisitDuration)) {
                            $remote_working_duration = $clientVisitDuration;
                        }
                        unset($homeClockIns, $homeClockOuts, $clientVisitClockIns, $clientVisitClockOuts, $homeDuration, $clientVisitDuration);

                        $startTime = strtotime($clock_in->scan_time);
                        $endTime = strtotime($clock_out->scan_time);
                        $durationInSeconds = $endTime - $startTime;
                    
                        $hours = floor($durationInSeconds / 3600);
                        $minutes = floor(($durationInSeconds % 3600) / 60);

                        $lunchhour = 1;
                        if ($hours > 5) {
                            $hours -= $lunchhour;
                        }
                    
                        $duration = $hours . " hr " . $minutes . " mins";
                        $status = "Present";
                        if($clock_in->access_id =='3'){
                            if($clock_in->scan_time > "09:00:00"){
                                // check if on leave and leaves start time is 9am
                                if ($leaves && $leaves->first()->start_time == '09:00:00') {
                                    // Check if clock_in is more than leave end time, then it is late
                                    if ($clock_in->scan_time > $leaves->first()->end_time) {
                                        $status = "Late";
                                    }
                                } else {
                                    $status = "Late";
                                }
                            }
                        }else{
                            if($clock_in->scan_time > "08:00:00"){
                                // check if on leave and leaves start time is 8am
                                if ($leaves && $leaves->first()->start_time == '08:00:00') {
                                    // Check if clock_in is more than leave end time, then it is late
                                    if ($clock_in->scan_time > $leaves->first()->end_time) {
                                        $status = "Late";
                                    }
                                } else {
                                    $status = "Late";
                                }
                            }
                        }

                        if ($leaves) {
                            $leaveName = '';
                            foreach ($leaves as $leave) {
                                $leaveName .= $leave->leaveType->name;
                                $leaveDuration = (new LeaveController())->calculateDuration($leave);
                            
                                if ($leave->leaveType->balance_unit == 'hour') {
                                    // If leave is less than 8 hours, display in hours and show start and end time
                                    if ($leaveDuration < 8) {
                                        // Check if day is Saturday
                                        if (Carbon::parse($leave->start_date)->dayOfWeek == Carbon::SATURDAY) {
                                            if ($leaveDuration < 5) {
                                                $startTime = Carbon::createFromFormat('H:i:s', $leave->start_time)->format('g:i A');
                                                $endTime = Carbon::createFromFormat('H:i:s', $leave->end_time)->format('g:i A');
                                                $leaveDuration = $leaveDuration . ' hour(s) (' . $startTime . ' - ' . $endTime . ')';
                                            } else {
                                                $leaveDuration = $leaveDuration . ' hour(s)';
                                            }
                                        } else {
                                            $startTime = Carbon::createFromFormat('H:i:s', $leave->start_time)->format('g:i A');
                                            $endTime = Carbon::createFromFormat('H:i:s', $leave->end_time)->format('g:i A');
                                            $leaveDuration = $leaveDuration . ' hour(s) (' . $startTime . ' - ' . $endTime . ')';
                                        }
                                    } else {
                                        $leaveDuration_day = floor($leaveDuration / 8);
                                        $leaveDuration_hour = $leaveDuration % 8;
                                        $leaveDuration = $leaveDuration_day . ' day(s)';
                                        
                                        if ($leaveDuration_hour > 0) {
                                            $leaveDuration .= ' ' . $leaveDuration_hour . ' hour(s)';
                                        }
                                    }

                                    $leaveName .= '(' . $leaveDuration . '), ';
                                }
                                
                            }

                            $secondary_status = rtrim($leaveName, ', ');
                        }
                    } else {
                        $duration = "N/A"; // Duration not available
                        // check if user is on leave
                        if ($clock_in || $clock_out) {
                            $status = "Incomplete Data";
                            if ($leaves) {
                                $totalLeaveDuration = 0;
                                $leaveName = '';
                                foreach ($leaves as $leave) {
                                    $leaveName .= $leave->leaveType->name;
                                    $leaveDuration = (new LeaveController())->calculateDuration($leave);
    
                                    $leaveDurationINT = (new LeaveController())->calculateDuration($leave);
                                
                                    if ($leave->leaveType->balance_unit == 'hour') {
                                        // If leave is less than 8 hours, display in hours and show start and end time
                                        if ($leaveDurationINT < 8) {
                                            // Check if day is Saturday
                                            if (Carbon::parse($leave->start_date)->dayOfWeek == Carbon::SATURDAY) {
                                                if ($leaveDurationINT < 5) {
                                                    $startTime = Carbon::createFromFormat('H:i:s', $leave->start_time)->format('g:i A');
                                                    $endTime = Carbon::createFromFormat('H:i:s', $leave->end_time)->format('g:i A');
                                                    $leaveDuration = $leaveDuration . ' hour(s) (' . $startTime . ' - ' . $endTime . ')';
                                                    $leaveName .= '(' . $leaveDuration . '), ';
                                                } else {
                                                    // $leaveDuration = $leaveDuration . ' hour(s)';
                                                }
                                            } else {
                                                $startTime = Carbon::createFromFormat('H:i:s', $leave->start_time)->format('g:i A');
                                                $endTime = Carbon::createFromFormat('H:i:s', $leave->end_time)->format('g:i A');
                                                $leaveDuration = $leaveDuration . ' hour(s) (' . $startTime . ' - ' . $endTime . ')';
                                                $leaveName .= '(' . $leaveDuration . '), ';
                                            }
                                        }
                                        $totalLeaveDuration += $leaveDurationINT;
                                    }
    
                                }
    
                                $containsDayLeave = $leaves->contains(function ($leave) {
                                    return $leave->leaveType->balance_unit === 'day';
                                });
                                
                                if ($containsDayLeave) {
                                    $status = "On Leave";
                                    $secondary_status = rtrim($leaveName, ', ');
                                } else {
                                    if ($totalLeaveDuration < 8) {
                                        if (Carbon::parse($yesterday)->dayOfWeek == Carbon::SATURDAY) {
                                            if ($totalLeaveDuration < 5) {
                                                $status = "Incomplete Data";
                                                $secondary_status = rtrim($leaveName, ', ');
                                            } else {
                                                $status = "On Leave";
                                                $secondary_status = rtrim($leaveName, ', ');
                                            }
                                        } else {
                                            $status = "Incomplete Data";
                                            $secondary_status = rtrim($leaveName, ', ');
                                        }
                                    } else {
                                        $status = "On Leave";
                                        $secondary_status = rtrim($leaveName, ', ');
                                    }
                                }
                            }
                        } elseif ($leaves) {
                            $totalLeaveDuration = 0;
                            $leaveName = '';
                            foreach ($leaves as $leave) {
                                $leaveName .= $leave->leaveType->name;
                                $leaveDuration = (new LeaveController())->calculateDuration($leave);

                                $leaveDurationINT = (new LeaveController())->calculateDuration($leave);
                            
                                if ($leave->leaveType->balance_unit == 'hour') {
                                    // If leave is less than 8 hours, display in hours and show start and end time
                                    if ($leaveDurationINT < 8) {
                                        // Check if day is Saturday
                                        if (Carbon::parse($leave->start_date)->dayOfWeek == Carbon::SATURDAY) {
                                            if ($leaveDurationINT < 5) {
                                                $startTime = Carbon::createFromFormat('H:i:s', $leave->start_time)->format('g:i A');
                                                $endTime = Carbon::createFromFormat('H:i:s', $leave->end_time)->format('g:i A');
                                                $leaveDuration = $leaveDuration . ' hour(s) (' . $startTime . ' - ' . $endTime . ')';
                                                $leaveName .= '(' . $leaveDuration . '), ';
                                            } else {
                                                // $leaveDuration = $leaveDuration . ' hour(s)';
                                            }
                                        } else {
                                            $startTime = Carbon::createFromFormat('H:i:s', $leave->start_time)->format('g:i A');
                                            $endTime = Carbon::createFromFormat('H:i:s', $leave->end_time)->format('g:i A');
                                            $leaveDuration = $leaveDuration . ' hour(s) (' . $startTime . ' - ' . $endTime . ')';
                                            $leaveName .= '(' . $leaveDuration . '), ';
                                        }
                                    }
                                    $totalLeaveDuration += $leaveDurationINT;
                                }

                            }

                            $containsDayLeave = $leaves->contains(function ($leave) {
                                return $leave->leaveType->balance_unit === 'day';
                            });
                            
                            if ($containsDayLeave) {
                                $status = "On Leave";
                                $secondary_status = rtrim($leaveName, ', ');
                            } else {
                                if ($totalLeaveDuration < 8) {
                                    if (Carbon::parse($yesterday)->dayOfWeek == Carbon::SATURDAY) {
                                        if ($totalLeaveDuration < 5) {
                                            $status = "Absent";
                                            $secondary_status = rtrim($leaveName, ', ');
                                        } else {
                                            $status = "On Leave";
                                            $secondary_status = rtrim($leaveName, ', ');
                                        }
                                    } else {
                                        $status = "Absent";
                                        $secondary_status = rtrim($leaveName, ', ');
                                    }
                                } else {
                                    $status = "On Leave";
                                    $secondary_status = rtrim($leaveName, ', ');
                                }
                            }
                        } else {
                            $status = "Absent";
                        }
                    }

                    $scan_in = null;
                    $scan_out = null;
                    $timestamp = null;
                    if(!empty($clock_in)){
                        $scan_in = $clock_in->scan_time;
                        $timestamp_scan_in = Carbon::parse($clock_in->scan_datetime);
                        $timestamp = $timestamp_scan_in->timestamp; 
                    }
                    if(!empty($clock_out)){
                        $scan_out = $clock_out->scan_time;
                    }

                    // if(empty($clock_in) && empty($clock_out)){
                    //     $status = "Absent";
                    // }
                }

                $attendance_list = [
                    'user_id'=>$e->id,
                    'check_in_date'=>$yesterday,
                    'timestamp'=>$timestamp ?? null,
                    'name'=>$e->name,
                    'staff_id'=>$e->staff_id,
                    'department_name'=>$e->department->department_name ??'-',
                    'check_in_time'=>$scan_in ?? null,
                    'check_out_time'=>$scan_out ?? null,
                    'duration'=>$duration ?? '-',
                    'remote_working_duration'=> $remote_working_duration ?? '-',
                    'location'=>$location ?? '-',
                    'status'=>$status,
                    'secondary_status'=>$secondary_status ?? null,
                    'level'=>$level ?? '-',
                    'remarks'=>$remarks ??'-',
                ];
                // dd($attendance_list);
                EmployeeDailyAttendance::create($attendance_list);
                unset($status, $secondary_status, $leaves, $timestamp, $scan_in, $scan_out, $duration, $remote_working_duration, $location, $clock_in, $clock_out, $level);
            }
        }
        UserAttendance::where('scan_date',$yesterday)->update(['status'=>'done']); 
        // }

        $message ="Attendance List Generated";
        return response()->json($message);
    }
}
