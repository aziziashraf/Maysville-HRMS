<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceList;
use App\Models\LeaveBalanceListLog;
use App\Models\EventType;
use App\Models\Event;
use App\Models\Attachment;
use App\Models\Department;
use App\Models\LeaveDeductionLog;

use Illuminate\Http\Request;
use DateTime;
use DatePeriod;
use DateInterval;
use Mail;
use App\Mail\LeaveSubmit;
use App\Mail\LeaveReview;
use App\Mail\LeaveApprove;
use App\Mail\LeaveCancel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail as FacadesMail;
use App\Jobs\RenewLeaveBalanceMonthly;
use App\Jobs\RenewLeaveBalanceYearly;
use App\Jobs\ClearExpiredLeave;

use App\Jobs\SendFCMNotification;
use App\Jobs\SendMultiFCMNotification;

use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get the currently authenticated user
        $user = auth()->user();

        // Retrieve leaves for the current user
        $leave = Leave::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        // Return the leaves to the view or perform other operations
        return view('leave.index', ['leave' => $leave]);
    }

    public function requestIndex(Request $request)
    {
        // Get the currently authenticated user
        $user = auth()->user();

        $date_range = $request->query('date_range');
        
        if ($date_range) {
            $dates = explode(" to ", $date_range);
            $date_from = $dates[0];
            $date_to = isset($dates[1]) ? $dates[1] : $date_from;
        } else {
            // empty
            $date_from = null;
            $date_to = null;
            $date_range = null;
        }

        $department_id = $request->query('department_id');
        $status = $request->query('status');
        $leave_type_id = $request->query('leave_type_id');
        $search = $request->query('search');
        $filter =[
            'date_from'=>$date_from, 
            'date_to'=>$date_to,
            'date_range'=>$date_range,
            'department_id'=>$department_id, 
            'status'=>$status,
            'leave_type_id' => $leave_type_id,
            'search'=>$search, 
        ];

        $department = Department::all();
        $leave_types = LeaveType::all();

        // check wether user is leave approver and leave reviewer
        if ($user->position && $user->position->leave_reviewer && $user->position->leave_approver) {
            $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))
                            ->orWhereJsonContains('leave_approvers', strval($user->position_id))
                            ->pluck('id');

            // get leave request for the employees
            $leave = Leave::whereIn('user_id', $employees)
                        ->whereNotIn('status', ['draft']);

        } elseif ($user->position && $user->position->leave_reviewer) {
            $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))->pluck('id');

            // get leave request for the employees
            $leave = Leave::whereIn('user_id', $employees)->whereNotIn('status', ['draft']);
        } elseif ($user->position && $user->position->leave_approver) {
            $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))->pluck('id');
            
            // get leave that has is not in draft or submit status
            $leave = Leave::whereIn('user_id', $employees)->whereNotIn('status', ['draft']);
        } elseif ($user->isAn('superadmin')) {
            // get leave request for the user
            $leave = Leave::whereNotIn('status', ['draft']);
        } else {
            // get leave request for the user
            return redirect()->route('leave.index');
        }

        if (!empty($date_from) && !empty($date_to)) {
            $leave = $leave->where(function ($query) use ($date_from, $date_to) {
                $query->whereBetween('start_date', [$date_from, $date_to])
                      ->orWhereBetween('end_date', [$date_from, $date_to]);
            });            
        }

        if (!empty($department_id) && $department_id > 0) {
            $leave = $leave->whereHas('user', function ($query) use ($department_id) {
                $query->where('department_id', $department_id);
            });
        }

        if($status <>null && $status <> ''){
            $leave = $leave->where('status',$status);
        }

        if($leave_type_id <>null && $leave_type_id <> ''){
            $leave = $leave->where('leave_type_id',$leave_type_id);
        }

        if(!empty($search)){
            $leave = $leave->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('staff_id', 'like', '%'.$search.'%');
            });
        }

        $leave = $leave->orderBy('created_at', 'desc')->get();

        // add the duration to the leave
        foreach ($leave as $l){
            $l->duration = $this->calculateDuration($l) . " " . $l->leaveType->balance_unit . "(s)";
        }

        return view('leave.request.index', ['leave' => $leave, 'department' => $department, 'filter' => $filter, 'leave_types' => $leave_types]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $leave_type = LeaveType::all();

        $user = Auth::user();

        $leaveTypes = LeaveType::all();

        foreach ($leaveTypes as $leaveType) {
            $leaveBalance = LeaveBalance::firstOrCreate([
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
            ]);

            if ($leaveBalance->leaveBalanceLists->isEmpty()) {
                if ($leaveType->confirmed_employees_only && !$user->confirmed_date) {
                    $balance = 0;
                } else {
                    $balance = $leaveType->default_amount;
                }

                if ($leaveType->renew_freq == 'year') {
                    $year = date('Y');
                    $expiry_date = $year . '-12-31';
                } elseif ($leaveType->renew_freq == 'month') {
                    $year = date('Y');
                    $month = date('m');
                    $expiry_date = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
                }

                $leaveBalanceList = LeaveBalanceList::create([
                    'leave_balance_id' => $leaveBalance->id,
                    'balance' => $balance,
                    'year' => $year ?? null,
                    'month' => $month ?? null,
                    'expiry_date' => $expiry_date ?? null,
                ]);
            }  
        }

        $leave_balances = LeaveBalance::where('user_id', $user->id)->get();

        $leave_type = [];
        foreach ($leave_balances as $leave_balance) {
            $leave_type[] = (object) [
                'id' => $leave_balance->leaveType->id,
                'name' => $leave_balance->leaveType->name,
                'balance' => $leave_balance->totalBalance(),
                'balance_unit' => $leave_balance->leaveType->balance_unit,
            ];
        }

        $status = array(
            'draft',
            'submitted',
            'reviewed',
            'approved',
            'rejected',
            'cancelled'
        );
        return view('leave.create')->with(['leave_type' => $leave_type, 'status' => $status]);
    }

    public function requestCreate()
    {
        $leave_type = LeaveType::all();

        // Get the currently authenticated user
        $user = auth()->user();

        // check wether user is leave approver and leave reviewer
        if ($user->position && $user->position->leave_reviewer && $user->position->leave_approver) {
            $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))
                            ->orWhereJsonContains('leave_approvers', strval($user->position_id))
                            ->where('is_active', '1')
                            ->get();
            
            $status = array(
                'reviewed',
                'approved',
            );
            
        } elseif ($user->position && $user->position->leave_reviewer) {
            $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))
                            ->where('is_active', '1')
                            ->get();

            $status = array(
                'reviewed',
            );
        } elseif ($user->position && $user->position->leave_approver) {
            $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))
                            ->where('is_active', '1')
                            ->get();

            $status = array(
                'approved',
            );
        } else {
            // redirect to leave request index if user is not leave approver or leave reviewer with error message
            return redirect()->route('leave.requestIndex')->with('error', 'You are not a leave approver or leave reviewer');
        }

        return view('leave.request.create')->with(['leave_type' => $leave_type,'employees' => $employees, 'status' => $status]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request;

        // add validation for request
        if ($request->status != 'cancelled') {

            $request->validate([
                'leave_type_id' => 'required',
                'date_range' => 'required',
                'start_time' => 'nullable|required_with:end_time',
                'end_time' => 'nullable|required_with:start_time|after:start_time',
            ]);

            $date_range = $request->date_range;
            $dates = explode(" to ", $date_range);
            $request->request->add(['start_date' => $dates[0]]);
            $request->request->add(['end_date' => isset($dates[1]) ? $dates[1] : null]);

            $duration =  $this->calculateDurationRequest($request);
            $leave_balance = LeaveBalance::where('user_id', $request->user_id)
                            ->where('leave_type_id', $request->leave_type_id)
                            ->first();
            $leave_type = LeaveType::find($request->leave_type_id);

            if ($leave_type->balance_unit == 'hour' && $duration < 1) {
                // Return error if leave duration is less than 1 hour
                return back()->withErrors(['error' => 'Leave duration must be at least 1 hour']);
            }

            if ($request->start_date < now()->format('Y-m-d')){
                if ($leave_type->back_dated){
                    if ($request->start_date < now()->subDays($leave_type->back_dated_days_limit)->format('Y-m-d')) {
                        return back()->withErrors(['error' => 'Back dated leave exceeds limit to apply. Only ' . $leave_type->back_dated_days_limit . ' days from current day is allowed']);
                    }
                } else {
                    return back()->withErrors(['error' => 'Back dated leave is not allowed']);
                }
            }
            
            if($leave_type->limit_per_leave){
                if($duration > $leave_type->limit_per_leave_amount){
                    return back()->withErrors(['error' => 'Duration exceed limit per leave']);
                }
            }

            if($leave_balance->totalBalance() < $duration){
                return back()->withErrors(['error' => 'Insufficient leave balance']);
            }
        }

        if (isset($request->leave_id)) {
            $leave = Leave::find($request->leave_id);
            if ($leave->status != 'draft' && $request->status != 'cancelled') {
                return back()->withErrors(['error' => 'Leave is already submitted and cannot be edited']);
            }

            // if status is cancelled, check date range is not in the past
            if ($request->status == 'cancelled') {
                if ($leave->start_date < now()->format('Y-m-d')){
                    return back()->withErrors(['error' => 'Leave is already started and cannot be cancelled']);
                }
            } elseif ($leave_type->attachment_required) {
                //check for existing attachment if no attachment validate attachment as required
                if ($leave->attachments->isEmpty()) {
                    $request->validate([
                        'attachment' => 'required',
                    ]);
                }
            }

            $leave->update($request->all());
        } else {
            if ($leave_type->attachment_required) {
                $request->validate([
                    'attachment' => 'required',
                ]);
            }
            $leave = Leave::create($request->all());
        }

        // Handle attachments
        if ($request->hasFile('attachment')) {
            $attachmentFiles = $request->file('attachment');
            if (!is_array($attachmentFiles)) {
                $attachmentFiles = [$attachmentFiles]; // Convert to array if it's a single file
            }
        
            foreach ($attachmentFiles as $attachmentFile) {
                $path = "leave/".$leave->id."/".$attachmentFile->getClientOriginalName();
                $attachment = new Attachment([
                    'filename' => $attachmentFile->getClientOriginalName(),
                    'path' => $path,
                ]);
                $attachmentFile->storeAs('attachments', $path);
                $leave->attachments()->save($attachment);
            }
        } 

        if($leave->status == 'submitted') {
            // deduct leave balance if leave is submitted
            $balanceDeducted = $this->deductBalance($leave);
            if($leave->user->getLeaveReviewers()->isNotEmpty()){
                $leaveReviewersEmail = $leave->user->getLeaveReviewers()->whereNotNull('email')->pluck('email');
                $leaveReviewersFCMToken = $leave->user->getLeaveReviewers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$leaveReviewersEmail->isEmpty()) {
                    // Send the email to the leave reviewers
                    foreach ($leaveReviewersEmail as $recipient) {
                        // Mail::to($recipient)->send(new LeaveSubmit($leave));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($leaveReviewersFCMToken)) {
                    // Send the notification to the leave reviewers
                    SendMultiFCMNotification::dispatch($leaveReviewersFCMToken, [
                        'title' => 'Leave request needs review',
                        'body' => 'You have a leave request from ' . $leave->user->name . ' to review',
                        'path' => 'approval',
                        'type' => 'leave',
                        'id' => $leave->id,
                    ]);
                }
                
            } else if ($leave->user->getLeaveApprovers()->isNotEmpty()){
                $leave->update(['status' => 'reviewed']);

                $leaveApproversEmail = $leave->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                $leaveApproversFCMToken = $leave->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if(!$leaveApproversEmail->isEmpty()){
                    // Send the email to the leave approvers
                    foreach ($leaveApproversEmail as $recipient) {
                        // Mail::to($recipient)->send(new LeaveSubmit($leave));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($leaveApproversFCMToken)) {
                    // Send the notification to the leave approvers
                    SendMultiFCMNotification::dispatch($leaveApproversFCMToken, [
                        'title' => 'Leave request needs approval',
                        'body' => 'You have a leave request from ' . $leave->user->name . ' to approve',
                        'path' => 'approval',
                        'type' => 'leave',
                        'id' => $leave->id,
                    ]);
                }
            }
        } elseif ($leave->status == 'cancelled') {
            // restore leave balance if leave is cancelled
            $balanceRestored = $this->restoreBalance($leave);

            // Email the reviewer and approver if the leave was cancelled and was in review or approved status
            if ($leave->review_status && $leave->reviewed_by) {
                if ($leave->reviewer->email) {
                    // Mail::to($leave->reviewer->email)->send(new LeaveCancel($leave));
                }

                if ($leave->reviewer->firebase_token) {
                    SendFCMNotification::dispatch($leave->reviewer->firebase_token, [
                        'title' => 'Leave request cancelled',
                        'body' => $leave->user->name . ' has cancelled their leave request',
                        'path' => 'approval',
                        'type' => 'leave',
                        'id' => $leave->id,
                    ]);
                }

            }
            if ($leave->approval_status && $leave->approved_by) {
                if ($leave->approver->email) {
                    // Mail::to($leave->approver->email)->send(new LeaveCancel($leave));
                }

                if ($leave->approver->firebase_token) {
                    SendFCMNotification::dispatch($leave->approver->firebase_token, [
                        'title' => 'Leave request cancelled',
                        'body' => $leave->user->name . ' has cancelled their leave request',
                        'path' => 'approval',
                        'type' => 'leave',
                        'id' => $leave->id,
                    ]);
                }
            }
        
            // Restore leave balance if the leave was already approved
            // if ($leave->approval_status) {
            //     $balanceRestored = $this->restoreBalance($leave);
            //     if (!$balanceRestored) {
            //         // Handle error as needed
            //     }
            // }
        }

        return redirect()->route('leave.index');
    }

    public function requestStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'leave_type_id' => 'required',
            'date_range' => 'required',
            'start_time' => 'nullable|required_with:end_time',
            'end_time' => 'nullable|required_with:start_time|after:start_time',
            'status' => 'required',
        ]);

        $date_range = $request->date_range;
        $dates = explode(" to ", $date_range);
        $request->request->add(['start_date' => $dates[0]]);
        $request->request->add(['end_date' => isset($dates[1]) ? $dates[1] : null]);

        if ($request->status == 'reviewed'){
            $request->request->add(['review_status' => '1']);
            $request->request->add(['reviewed_by' => Auth::user()->id]);
            $request->request->add(['reviewed_at' => now()]);
            $request->request->add(['review_remark' => $request->status_remarks]);
        } elseif ($request->status == 'approved') {
            $request->request->add(['approval_status' => '1']);
            $request->request->add(['approved_by' => Auth::user()->id]);
            $request->request->add(['approved_at' => now()]);
            $request->request->add(['approval_remark' => $request->status_remarks]);
        }

        $employee = User::find($request->user_id);

        if (!$employee) {
            return back()->withErrors(['error' => 'Employee not found']);
        }

        $leave_balance = LeaveBalance::where('user_id', $employee->id)
                        ->where('leave_type_id', $request->leave_type_id)
                        ->first();
        $duration =  $this->calculateDurationRequest($request);
        $leave_type = LeaveType::find($request->leave_type_id);

        if ($leave_type->attachment_required) {
            $request->validate([
                'attachment' => 'required',
            ]);
        }

        if (!$leave_balance) {
            if ($leave_type->confirmed_employees_only) {
                if ($employee->confirmed_date) {
                    $leave_balance = new LeaveBalance([
                        'user_id' => $employee->id,
                        'leave_type_id' => $request->leave_type_id,
                        'balance' => $leave_type->default_amount,
                    ]);
                } else {
                    return back()->withErrors(['error' => 'Employee is not confirmed and cannot apply this leave type']);
                }
            } else {
                $leave_balance = new LeaveBalance([
                    'user_id' => $employee->id,
                    'leave_type_id' => $request->leave_type_id,
                    'balance' => $leave_type->default_amount,
                ]);
            }
        }

        if ($leave_type->balance_unit == 'hour' && $duration < 1) {
            // Return error if leave duration is less than 1 hour
            return back()->withErrors(['error' => 'Leave duration must be at least 1 hour']);
        }
        
        if($leave_type->limit_per_leave){
            if($duration > $leave_type->limit_per_leave_amount){
                return back()->withErrors(['error' => 'Duration exceed limit per leave']);
            }
        }

        if($leave_balance->totalBalance() < $duration){
            return back()->withErrors(['error' => 'Insufficient leave balance']);
        }

        $leave = Leave::create($request->all());

        // deduct leave balance
        $balanceDeducted = $this->deductBalance($leave);

        return redirect()->route('leave.requestIndex');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function show(Leave $leave)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function edit(Leave $leave)
    {
        // return $this->calculateDuration($leave) . $leave->leaveType->balance_unit;
        $leave_types = LeaveType::all();

        $leave_balances = LeaveBalance::where('user_id', $leave->user_id)->get();

        $leave_type = [];
        foreach ($leave_balances as $leave_balance) {
            $leave_type[] = (object) [
                'id' => $leave_balance->leaveType->id,
                'name' => $leave_balance->leaveType->name,
                'balance' => $leave_balance->totalBalance(),
                'balance_unit' => $leave_balance->leaveType->balance_unit,
            ];
        }

        $status = array(
            'draft',
            'submitted',
            'reviewed',
            'approved',
            'rejected',
            'cancelled'
        );
        $leave->date_range = $leave->end_date ? $leave->start_date . ' to ' . $leave->end_date : $leave->start_date;
        
        return view('leave.create')->with(['leave' => $leave, 'leave_type' => $leave_type, 'status' => $status]);
    }

    public function requestEdit(Leave $leave)
    {
        // return $this->calculateDuration($leave) . $leave->leaveType->balance_unit;
        $leave_type = LeaveType::all();
        $status = array(
            'draft',
            'submitted',
            'reviewed',
            'approved',
            'rejected',
            'cancelled'
        );
        $leave->date_range = $leave->start_date . " to " . $leave->end_date;
        return view('leave.request.edit')->with(['leave' => $leave, 'leave_type' => $leave_type, 'status' => $status]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Leave $leave)
    {
        //
    }

    public function requestUpdate(Request $request, Leave $leave)
    {
        $user = auth()->user();
        // return $this->calculateDuration($leave) . " " . $leave->leaveType->balance_unit;

        if ($user->position->leave_reviewer) {
            // Validate the input
            $validatedData = $request->validate([
                'review_status' => 'required|in:0,1', // review_status must be 0 or 1
            ]);

            // Check if the leave is already reviewed and redirect back if it is with an error message
            if ($leave->status == 'reviewed'){
                return redirect()->route('leave.requestIndex')->with('error', 'Leave request is already reviewed');
            } elseif ($leave->status == 'approved') {
                return redirect()->route('leave.requestIndex')->with('error', 'Leave request is already approved');
            } elseif ($leave->status == 'rejected') {
                return redirect()->route('leave.requestIndex')->with('error', 'Leave request is already rejected');
            } elseif ($leave->status == 'cancelled') {
                return redirect()->route('leave.requestIndex')->with('error', 'Leave request is already cancelled');
            }

            if ($request->review_status == '1') {
                $status = 'reviewed';
            } elseif ($request->review_status == '0') {
                $status = 'rejected';
            } else {
                return redirect()->route('leave.requestIndex');
            }

            $leave->update([
                'review_status' => $request->review_status,
                'review_remark' => $request->review_remark,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'status' => $status
            ]);

            if ($leave->status == 'rejected'){
                $balanceRestored = $this->restoreBalance($leave);
            }

            if($request->review_status == '1') {
                $leaveApproversEmail = $leave->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                $leaveApproversFCMToken = $leave->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if(!$leaveApproversEmail->isEmpty()){
                    // Send the email to the leave approvers
                    foreach ($leaveApproversEmail as $recipient) {
                        // Mail::to($recipient)->send(new LeaveSubmit($leave));
                    }
                }
                
                // Check if FCM tokens are not empty
                if (!empty($leaveApproversFCMToken)){
                    // Send the notification to the leave approvers
                    SendMultiFCMNotification::dispatch($leaveApproversFCMToken, [
                        'title' => 'Leave request needs approval',
                        'body' => 'You have a leave request from ' . $leave->user->name . ' to approve',
                        'path' => 'approval',
                        'type' => 'leave',
                        'id' => $leave->id,
                    ]);
                }

                if ($leave->user->firebase_token) {
                    SendFCMNotification::dispatch($leave->user->firebase_token, [
                        'title' => 'Leave request reviewed',
                        'body' => 'Your leave request has been reviewed by ' . $user->name,
                        'path' => 'leave',
                        'type' => 'leave',
                        'id' => $leave->id,
                    ]);
                }

            } else {
                if ($leave->user->firebase_token) {
                    SendFCMNotification::dispatch($leave->user->firebase_token, [
                        'title' => 'Leave request rejected',
                        'body' => 'Your leave request has been rejected by ' . $user->name,
                        'path' => 'leave',
                        'type' => 'leave',
                        'id' => $leave->id,
                    ]);
                }
            }

            if ($leave->user->email){
                // Mail::to($leave->user->email)->send(new LeaveReview($leave));
            }

        } elseif ($user->position->leave_approver) {
            // Validate the input
            $validatedData = $request->validate([
                'approval_status' => 'required|in:0,1', // approve_status must be 0 or 1
            ]);

            // Check if the leave is already approved and redirect back if it is with an error message
            if ($leave->status == 'approved'){
                return redirect()->route('leave.requestIndex')->with('error', 'Leave request is already approved');
            } elseif ($leave->status == 'rejected') {
                return redirect()->route('leave.requestIndex')->with('error', 'Leave request is already rejected');
            } elseif ($leave->status == 'cancelled') {
                return redirect()->route('leave.requestIndex')->with('error', 'Leave request is already cancelled');
            }

            if ($request->approval_status == '1') {
                $status = 'approved';
            } elseif ($request->approval_status == '0') {
                $status = 'rejected';
                $balanceRestored = $this->restoreBalance($leave);
            } else {
                return redirect()->route('leave.requestIndex');
            }

            $leave->update([
                'approval_status' => $request->approval_status,
                'approval_remark' => $request->approval_remark,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'status' => $status
            ]);
            
            if ($leave->user->email){
                // Mail::to($leave->user->email)->send(new LeaveApprove($leave));
            }

            if ($request->approval_status == '1') {
                if ($leave->user->firebase_token) {
                    SendFCMNotification::dispatch($leave->user->firebase_token, [
                        'title' => 'Leave request approved',
                        'body' => 'Your leave request has been approved by ' . $user->name,
                        'path' => 'leave',
                        'type' => 'leave',
                        'id' => $leave->id,
                    ]);
                }
            } else {
                if ($leave->user->firebase_token) {
                    SendFCMNotification::dispatch($leave->user->firebase_token, [
                        'title' => 'Leave request rejected',
                        'body' => 'Your leave request has been rejected by ' . $user->name,
                        'path' => 'leave',
                        'type' => 'leave',
                        'id' => $leave->id,
                    ]);
                }
            }
        }

        return redirect()->route('leave.requestIndex');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function destroy(Leave $leave)
    {
        $leave->delete();
        return redirect()->route('leave.index');
    }

    public function calculateDuration($leave)
    {
        // Fetch leaveType property from $leave object
        $leaveType = $leave->leaveType;

        // Convert date and time strings to DateTime objects
        $start_date = new DateTime($leave->start_date);
        $end_date = $leave->end_date ? new DateTime($leave->end_date) : null;
        $start_time = $leave->start_time && !$end_date ? new DateTime($leave->start_time) : null; // Ignore start_time if end_date is present
        $end_time = $leave->end_time && !$end_date ? new DateTime($leave->end_time) : null; // Ignore end_time if end_date is present

        // Calculate the duration based on the available fields
        if ($start_date && $end_date && !$start_time && !$end_time) {
            // Scenario 1: start_date and end_date are available, start_time and end_time are null
        
            // Calculate the number of days between start_date and end_date, excluding weekends
            $interval = new DateInterval('P1D'); // 1 day
            $daterange = new DatePeriod($start_date, $interval, $end_date->modify('+1 day')); // Add 1 day to include end_date in the range
            $days = 0;
            $hours = 0;
            $dateArray = [];
            foreach ($daterange as $date) {
                // array_push($dateArray, $date->format('Y-m-d') . $this->checkEvent($date->format('Y-m-d')));

                if (!in_array($date->format('N'), [6, 7])) { // Exclude Saturday (6) and Sunday (7)
                    if($this->checkEvent($date->format('Y-m-d')) !== 'holiday'){
                        $days++;
                        $hours += 8;
                    }
                } else if ($date->format('N') == 6){
                    if($this->checkEvent($date->format('Y-m-d')) == 'work'){
                        $days++;
                        $hours += 5;
                    }
                }
            }
            // return json_encode($dateArray);
        
            // Deduct 8 hours for each day if leave type is 'day'
            if ($leaveType && $leaveType->balance_unit == 'day') {
                $duration = $days;
            } else if ($leaveType && $leaveType->balance_unit == 'hour') {
                $duration = $hours;
            }
        } elseif ($start_date && !$end_date && !$start_time && !$end_time) {
            // Scenario 2: start_date is available, end_date, start_time, and end_time are null

            // Deduct 8 hours for each day if leave type is 'day'
            if ($leaveType && $leaveType->balance_unit == 'day') {
                $duration = 1; //1 day
            } else if ($leaveType && $leaveType->balance_unit == 'hour') {
                if ($start_date->format('N') == 6) { // Saturday (6)
                    $duration = 5;
                } else {
                    $duration = 8;
                }
            }
        } elseif ($start_date && !$end_date && $start_time && $end_time) {
            $interval = $start_time->diff($end_time);
            $hours = $interval->h;

            // Deduct the duration in hours if leave type is 'hour'
            if ($leaveType && $leaveType->balance_unit == 'day') {
                $days = 1;
                $duration = $days;
            } else if ($leaveType && $leaveType->balance_unit == 'hour') {
                // max duration is 8 hours
                if ($hours > 8) {
                    $duration = 8;
                } else {
                    $duration = $hours;
                }
            }
        } else {
            // Invalid scenario or missing required fields
            $duration = "Invalid scenario or missing required fields";
        }

        return $duration;
    }

    public function deductBalance($leave)
    {
        // Deduct leave balance based on the duration and leave type
        if ($leave->user_id && $leave->leaveType) {
            $user = $leave->user;
            $leaveType = $leave->leaveType;
            $duration = $this->calculateDuration($leave);

            // Deduct balance based on leave type and duration
            $leaveBalance = LeaveBalance::firstOrCreate([
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
            ]);

            if ($leaveBalance->leaveBalanceLists->isEmpty()) {
                if ($leaveType->confirmed_employees_only && !$user->confirmed_date) {
                    $balance = 0;
                } else {
                    $balance = $leaveType->default_amount;
                }

                if ($leaveType->renew_freq == 'year') {
                    $year = date('Y');
                    $expiry_date = $year . '-12-31';
                } elseif ($leaveType->renew_freq == 'month') {
                    $year = date('Y');
                    $month = date('m');
                    $expiry_date = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
                }

                $leaveBalanceList = LeaveBalanceList::create([
                    'leave_balance_id' => $leaveBalance->id,
                    'balance' => $balance,
                    'year' => $year ?? null,
                    'month' => $month ?? null,
                    'expiry_date' => $expiry_date ?? null,
                ]);
            }
            
            $totalBalance = $leaveBalance->totalBalance();
            
            if ($totalBalance >= $duration) {
                $leaveBalanceLists = $leaveBalance->leaveBalanceLists()->where('status', true)->orderByRaw('ISNULL(expiry_date), expiry_date ASC')->get();

                // Deduct from the balance
                foreach ($leaveBalanceLists as $leaveBalanceList) {
                    if ($leaveBalanceList->balance >= $duration) {
                        $leaveBalanceList->balance = $leaveBalanceList->balance - $duration;
                        $leaveBalanceList->save();
                        // add leave deduction log
                        $leaveDeductionLog = LeaveDeductionLog::create([
                            'leave_balance_list_id' => $leaveBalanceList->id,
                            'leave_id' => $leave->id,
                            'amount' => $duration,
                        ]);
                        break;
                    } else {
                        $tempDuration = $leaveBalanceList->balance;
                        $duration = $duration - $leaveBalanceList->balance;
                        $leaveBalanceList->balance = 0;
                        $leaveBalanceList->save();
                        // add leave deduction log
                        $leaveDeductionLog = LeaveDeductionLog::create([
                            'leave_balance_list_id' => $leaveBalanceList->id,
                            'leave_id' => $leave->id,
                            'amount' => $tempDuration,
                        ]);
                    }
                }
                return response()->json(['message' => 'Leave balance deducted'], 200);
            } else {
                return response()->json(['message' => 'Insufficient leave balance. Only ' . $totalBalance . ' ' . $leaveType->balance_unit . '(s) left'], 400);
            }
        } else {
            return response()->json(['message' => 'Invalid input or missing required fields',], 400);
        }
    }

    public function restoreBalance($leave)
    {
        // restore balance based on leaveDeductionLog
        foreach ($leave->leaveDeductionLogs as $leaveDeductionLog) {
            $leaveBalanceList = $leaveDeductionLog->leaveBalanceList;

            $leaveBalanceList->balance = $leaveBalanceList->balance + $leaveDeductionLog->amount;
            $leaveBalanceList->save();
        }
    }

    public function checkEvent($date)
    {
        // Get the event type IDs for the desired event types
        $eventTypeIds = EventType::whereIn('name', [
            'Company Working Day',
            'Company Holiday',
            'Public Holiday',
        ])->pluck('id');

        // Get the event for the given date and event type IDs
        $event = Event::whereIn('event_type_id', $eventTypeIds)
            ->whereDate('date_from', '<=', $date)
            ->whereDate('date_to', '>=', $date)
            ->first();

        if(!$event){
            $event = Event::whereIn('event_type_id', $eventTypeIds)
            ->whereDate('date_from', $date)
            ->whereNull('date_to')
            ->first();
        }
       
        // Check the event's EventType name and return 'work' or 'holiday'
        if ($event && $event->event_type->name === 'Company Working Day') {
            return 'work';
        } elseif ($event && in_array($event->event_type->name, ['Company Holiday', 'Public Holiday'])) {
            return 'holiday';
        } else {
            return null;
        }
    }

    public function getEventName($date)
    {
        $event = Event::whereDate('date_from', '<=', $date)
            ->whereDate('date_to', '>=', $date)
            ->first();

        if(!$event){
            $event = Event::whereDate('date_from', $date)
            ->whereNull('date_to')
            ->first();
        }

        // check if event is not null and return name
        if ($event) {
            return $event->name;
        } else {
            return "No Event";
        }
    }

    function renewLeaveBalanceMonthly (){
        RenewLeaveBalanceMonthly::dispatch();
    }

    function renewLeaveBalanceYearly (){
        RenewLeaveBalanceYearly::dispatch();
    }

    function clearExpiredLeave (){
        ClearExpiredLeave::dispatch();
    }

    public function calculateDurationRequest($request)
    {
        $leaveType = LeaveType::find($request->leave_type_id);

        // Convert date and time strings to DateTime objects
        $start_date = new DateTime($request->start_date);
        $end_date = $request->end_date ? new DateTime($request->end_date) : null;
        $start_time = $request->start_time && !$end_date ? new DateTime($request->start_time) : null; // Ignore start_time if end_date is present
        $end_time = $request->end_time && !$end_date ? new DateTime($request->end_time) : null; // Ignore end_time if end_date is present

        // Calculate the duration based on the available fields
        if ($start_date && $end_date && !$start_time && !$end_time) {
            // Scenario 1: start_date and end_date are available, start_time and end_time are null
        
            // Calculate the number of days between start_date and end_date, excluding weekends
            $interval = new DateInterval('P1D'); // 1 day
            $daterange = new DatePeriod($start_date, $interval, $end_date->modify('+1 day')); // Add 1 day to include end_date in the range
            $days = 0;
            $hours = 0;
            $dateArray = [];
            foreach ($daterange as $date) {
                // array_push($dateArray, $date->format('Y-m-d') . $this->checkEvent($date->format('Y-m-d')));

                if (!in_array($date->format('N'), [6, 7])) { // Exclude Saturday (6) and Sunday (7)
                    if($this->checkEvent($date->format('Y-m-d')) !== 'holiday'){
                        $days++;
                        $hours += 8;
                    }
                } else if ($date->format('N') == 6){
                    if($this->checkEvent($date->format('Y-m-d')) == 'work'){
                        $days++;
                        $hours += 5;
                    }
                }
            }
            // return json_encode($dateArray);
        
            // Deduct 8 hours for each day if leave type is 'day'
            if ($leaveType && $leaveType->balance_unit == 'day') {
                $duration = $days;
            } else if ($leaveType && $leaveType->balance_unit == 'hour') {
                $duration = $hours;
            }
        } elseif ($start_date && !$end_date && !$start_time && !$end_time) {
            // Scenario 2: start_date is available, end_date, start_time, and end_time are null

            // Deduct 8 hours for each day if leave type is 'day'
            if ($leaveType && $leaveType->balance_unit == 'day') {
                $duration = 1; //1 day
            } else if ($leaveType && $leaveType->balance_unit == 'hour') {
                if ($start_date->format('N') == 6) { // Saturday (6)
                    $duration = 5;
                } else {
                    $duration = 8;
                }
            }
        } elseif ($start_date && !$end_date && $start_time && $end_time) {
            // Scenario 3: start_date and start_time are available, end_date and end_time are null
            $interval = $start_time->diff($end_time);
            $hours = $interval->h;

            // Deduct the duration in hours if leave type is 'hour'
            if ($leaveType && $leaveType->balance_unit == 'day') {
                $days = 1;
                $duration = $days;
            } else if ($leaveType && $leaveType->balance_unit == 'hour') {
                // max duration is 8 hours
                if ($hours > 8) {
                    $duration = 8;
                } else {
                    $duration = $hours;
                }
            }
        } else {
            // Invalid scenario or missing required fields
            $duration = "Invalid scenario or missing required fields";
        }

        return $duration;
    }

    public function checkLeave($date, $user_id)
    {
        $leave = Leave::where('user_id', $user_id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->orderBy('start_time')
            ->first();

        if(!$leave){
            $leave = Leave::where('user_id', $user_id)
            ->where('status', 'approved')
            ->whereDate('start_date', $date)
            ->whereNull('end_date')
            ->orderBy('start_time')
            ->first();
        }

        // check if event is not null and return name
        if ($leave) {
            return $leave;
        } else {
            return false;
        }
    }

    public function checkLeaves($date, $user_id)
    {
        $leaves = Leave::where('user_id', $user_id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->orderBy('start_time')
            ->get();

        if($leaves->isEmpty()){
            $leaves = Leave::where('user_id', $user_id)
            ->where('status', 'approved')
            ->whereDate('start_date', $date)
            ->whereNull('end_date')
            ->orderBy('start_time')
            ->get();
        }

        // check if event is not null and return name
        if ($leaves->isNotEmpty()) {
            return $leaves;
        } else {
            return false;
        }
    }
}
