<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\Attachment;
use App\Models\User;
use App\Models\Department;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceList;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Mail\OvertimeRequest;
use App\Mail\OvertimePreReview;
use App\Mail\OvertimeSubmit;
use App\Mail\OvertimeReview;
use App\Mail\OvertimeApprove;
use App\Mail\OvertimeCancel;

use App\Jobs\SendFCMNotification;
use App\Jobs\SendMultiFCMNotification;

class OverTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the currently authenticated user
        $user = auth()->user();

        // Get the user's overtime records order by date
        $overtimes = $user->overtimes()->orderBy('date', 'desc')->get();

        // Return the overtime records to the view
        return view('overtime.index')->with('overtimes', $overtimes);
    }

    public function requestIndex (Request $request)
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
        $search = $request->query('search');
        $filter =[
            'date_from'=>$date_from, 
            'date_to'=>$date_to,
            'department_id'=>$department_id, 
            'status'=>$status,
            'search'=>$search, 
        ];

        $department = Department::all();

        // check wether user is overtime approver and overtime reviewer
        if ($user->position && $user->position->leave_reviewer && $user->position->leave_approver) {
            $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))
                            ->orWhereJsonContains('leave_approvers', strval($user->position_id))
                            ->pluck('id');

            // get overtime request for the employees
            $overtime = Overtime::whereIn('user_id', $employees)
                        ->whereNotIn('status', ['draft']);

        } elseif ($user->position && $user->position->leave_reviewer) {
            $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))->pluck('id');

            // get overtime request for the employees
            $overtime = Overtime::whereIn('user_id', $employees)->whereNotIn('status', ['draft']);
        } elseif ($user->position && $user->position->leave_approver) {
            $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))->pluck('id');
            
            // get overtime that has is not in draft or submit status
            $overtime = Overtime::whereIn('user_id', $employees)->whereNotIn('status', ['draft']);
        } elseif ($user->isAn('superadmin')) {
            // get overtime request for the user
            $overtime = Overtime::whereNotIn('status', ['draft']);
        } else {
            // get overtime request for the user
            return redirect()->route('overtime.index');
        }

        if (!empty($date_from) && !empty($date_to)) {
            $overtime = $overtime->where(function ($query) use ($date_from, $date_to) {
                $query->whereBetween('date', [$date_from, $date_to]);
            });            
        }

        if (!empty($department_id) && $department_id > 0) {
            $overtime = $overtime->whereHas('user', function ($query) use ($department_id) {
                $query->where('department_id', $department_id);
            });
        }

        if($status <>null && $status <> ''){
            $overtime = $overtime->where('status',$status);
        }

        if(!empty($search)){
            $overtime = $overtime->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('staff_id', 'like', '%'.$search.'%');
            });
        }

        $overtime = $overtime->orderBy('date', 'desc')->get();

        // Return the overtime records to the view
        return view('overtime.request.index', ['overtime' => $overtime, 'department' => $department, 'filter' => $filter]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return the view
        return view('overtime.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // check if request has no status 
        if (!$request->has('status')) {
            $request->validate([
                'overtime_id' => 'required',
                'attachment' => 'required',
            ]);
        }

        $overtime = Overtime::find($request->overtime_id);
        if ($overtime) {

            if ($request->status != 'cancelled') {
                if( $overtime->status != 'draft' && $overtime->status != 'pre_reviewed'){
                    return back()->withErrors(['error' => 'Overtime is already '. str_replace('_', '-', $overtime->status) .' and cannot be edited']);
                }
            }

            if ($request->status == 'submitted'){
                $request->validate([
                    'actual_time_start' => 'required',
                    'actual_time_end' => 'required|after:actual_time_start',
                ]);

                $actual_time_start = strtotime($request->actual_time_start);
                $actual_time_end = strtotime($request->actual_time_end);
                $actual_time_taken = ($actual_time_end - $actual_time_start) / 3600;
                $request->merge(['actual_time_taken' => $actual_time_taken]);
            }

            $overtime->update($request->all());
        } else {
            $request->validate([
                'user_id' => 'required',
                'status' => 'required',
                'reasons' => 'required',
                'date' => 'required',
                'estimated_time_taken' => 'required',
            ]);
            $overtime = Overtime::create($request->all());
        }

        // Handle attachments
        if ($request->hasFile('attachment')) {
            $attachmentFiles = $request->file('attachment');
            if (!is_array($attachmentFiles)) {
                $attachmentFiles = [$attachmentFiles]; // Convert to array if it's a single file
            }
        
            foreach ($attachmentFiles as $attachmentFile) {
                $path = "overtime/".$overtime->id."/".$attachmentFile->getClientOriginalName();
                $attachment = new Attachment([
                    'filename' => $attachmentFile->getClientOriginalName(),
                    'path' => $path,
                ]);
                $attachmentFile->storeAs('attachments', $path);
                $overtime->attachments()->save($attachment);
            }
        }

        if ($overtime->status == 'requested') {
            if ($overtime->user->getLeaveReviewers()->isNotEmpty()){
                $overtimeReviewersEmail = $overtime->user->getLeaveReviewers()->whereNotNull('email')->pluck('email');
                $overtimeReviewersFCMToken = $overtime->user->getLeaveReviewers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$overtimeReviewersEmail->isEmpty()) {
                    // Send the email to the overtime reviewers
                    foreach ($overtimeReviewersEmail as $recipient) {
                        // Mail::to($recipient)->send(new OvertimeRequest($overtime));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($overtimeReviewersFCMToken)) {
                    // Send the notification to the overtime reviewers
                    SendMultiFCMNotification::dispatch($overtimeReviewersFCMToken, [
                        'title' => 'Overtime request needs review',
                        'body' => 'You have an overtime request from ' . $overtime->user->name . ' to pre-review',
                        'path' => 'approval',
                        'type' => 'overtime',
                        'id' => $overtime->id,
                    ]);
                }
            } elseif ($overtime->user->getLeaveApprovers()->isNotEmpty()) {
                $overtimeApproverEmail = $overtime->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                $overtimeApproverFCMToken = $overtime->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$overtimeApproverEmail->isEmpty()) {
                    // Send the email to the overtime approver
                    foreach ($overtimeApproverEmail as $recipient) {
                        // Mail::to($recipient)->send(new OvertimeRequest($overtime));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($overtimeApproverFCMToken)) {
                    // Send the notification to the overtime approver
                    SendMultiFCMNotification::dispatch($overtimeApproverFCMToken, [
                        'title' => 'Overtime request needs review',
                        'body' => 'You have an overtime request from ' . $overtime->user->name . ' to pre-review',
                        'path' => 'approval',
                        'type' => 'overtime',
                        'id' => $overtime->id,
                    ]);
                }
            }
        } elseif ($overtime->status == 'submitted') {
            if ($overtime->user->getLeaveReviewers()->isNotEmpty()){
                $overtimeReviewersEmail = $overtime->user->getLeaveReviewers()->whereNotNull('email')->pluck('email');
                $overtimeReviewersFCMToken = $overtime->user->getLeaveReviewers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$overtimeReviewersEmail->isEmpty()) {
                    // Send the email to the overtime reviewers
                    foreach ($overtimeReviewersEmail as $recipient) {
                        // Mail::to($recipient)->send(new OvertimeSubmit($overtime));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($overtimeReviewersFCMToken)) {
                    // Send the notification to the overtime reviewers
                    SendMultiFCMNotification::dispatch($overtimeReviewersFCMToken, [
                        'title' => 'Overtime submission needs review',
                        'body' => 'You have an overtime submission from ' . $overtime->user->name . ' to review',
                        'path' => 'approval',
                        'type' => 'overtime',
                        'id' => $overtime->id,
                    ]);
                }
            } elseif ($overtime->user->getLeaveApprovers()->isNotEmpty()) {
                $overtime->update(['status' => 'reviewed']);
                $overtimeApproverEmail = $overtime->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                $overtimeApproverFCMToken = $overtime->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$overtimeApproverEmail->isEmpty()) {
                    // Send the email to the overtime approver
                    foreach ($overtimeApproverEmail as $recipient) {
                        // Mail::to($recipient)->send(new OvertimeSubmit($overtime));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($overtimeApproverFCMToken)) {
                    // Send the notification to the overtime approver
                    SendMultiFCMNotification::dispatch($overtimeApproverFCMToken, [
                        'title' => 'Overtime submission needs review',
                        'body' => 'You have an overtime submission from ' . $overtime->user->name . ' to approve',
                        'path' => 'approval',
                        'type' => 'overtime',
                        'id' => $overtime->id,
                    ]);
                }
            }
        } elseif ($overtime->status == 'cancelled') {
            if ($overtime->user->getLeaveReviewers()->isNotEmpty()){
                $overtimeReviewersEmail = $overtime->user->getLeaveReviewers()->whereNotNull('email')->pluck('email');
                $overtimeReviewersFCMToken = $overtime->user->getLeaveReviewers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$overtimeReviewersEmail->isEmpty()) {
                    // Send the email to the overtime reviewers
                    foreach ($overtimeReviewersEmail as $recipient) {
                        // Mail::to($recipient)->send(new OvertimeCancel($overtime));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($overtimeReviewersFCMToken)) {
                    // Send the notification to the overtime reviewers
                    SendMultiFCMNotification::dispatch($overtimeReviewersFCMToken, [
                        'title' => 'Overtime request has been cancelled',
                        'body' => 'Overtime request from ' . $overtime->user->name . ' has been cancelled',
                        'path' => 'approval',
                        'type' => 'overtime',
                        'id' => $overtime->id,
                    ]);
                }
            } elseif ($overtime->user->getLeaveApprovers()->isNotEmpty()) {
                $overtimeApproverEmail = $overtime->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                $overtimeApproverFCMToken = $overtime->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$overtimeApproverEmail->isEmpty()) {
                    // Send the email to the overtime approver
                    foreach ($overtimeApproverEmail as $recipient) {
                        // Mail::to($recipient)->send(new OvertimeCancel($overtime));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($overtimeApproverFCMToken)) {
                    // Send the notification to the overtime approver
                    SendMultiFCMNotification::dispatch($overtimeApproverFCMToken, [
                        'title' => 'Overtime request has been cancelled',
                        'body' => 'Overtime request from ' . $overtime->user->name . ' has been cancelled',
                        'path' => 'approval',
                        'type' => 'overtime',
                        'id' => $overtime->id,
                    ]);
                }
            }
        }
        return redirect()->route('overtime.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Overtime $overtime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Overtime $overtime)
    {
        return view('overtime.create')->with('overtime', $overtime);
    }

    public function requestEdit(Overtime $overtime)
    {
        return view('overtime.request.create')->with('overtime', $overtime);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Overtime $overtime)
    {
        //
    }

    public function requestUpdate(Request $request, Overtime $overtime)
    {
        $user = auth()->user();

        if ($request->has('pre_review_status')) {
            if ($user->position->leave_reviewer || $user->position->leave_approver){
                $validatedData = $request->validate([
                    'pre_review_status' => 'required|in:0,1',
                ]);

                // Check if the overtime is already reviewed and redirect back if it is with an error message
                if ($overtime->status != 'requested') {
                    return back()->withErrors(['error' => 'Overtime is already been ' . str_replace('_', '-', $overtime->status)]);
                }

                if ($request->pre_review_status == '1') {
                    $status = 'pre_reviewed';
                } elseif ($request->pre_review_status == '0') {
                    $status = 'rejected';
                } else {
                    return back()->withErrors(['error' => 'Invalid status']);
                }

                $overtime->update([
                    'pre_review_status' => $request->pre_review_status,
                    'pre_review_remark' => $request->pre_review_remark,
                    'pre_reviewed_by' => $user->id,
                    'pre_reviewed_at' => now(),
                    'status' => $status
                ]);

                if ($overtime->user->email){
                    // Mail::to($overtime->user->email)->send(new OvertimePreReview($overtime));
                }
    
                if ($overtime->user->firebase_token) {
                    SendFCMNotification::dispatch($overtime->user->firebase_token, [
                        'title' => 'Overtime submission ' . $overtime->status,
                        'body' => 'Your overtime request has been '.str_replace('_', '-', $overtime->status).' by ' . $user->name,
                        'path' => 'overtime',
                        'type' => 'overtime',
                        'id' => $overtime->id,
                    ]);
                }
            } else {
                return back()->withErrors(['error' => 'You are not authorized to perform this action']);
            }
        } elseif ($request->has('review_status')) {
            if ($user->position->leave_reviewer){
                $validatedData = $request->validate([
                    'review_status' => 'required|in:0,1',
                ]);

                // Check if the overtime is already reviewed and redirect back if it is with an error message
                if ($overtime->status != 'submitted') {
                    return back()->withErrors(['error' => 'Overtime is already been ' . str_replace('_', '-', $overtime->status)]);
                }

                if ($request->review_status == '1') {
                    $status = 'reviewed';
                } elseif ($request->review_status == '0') {
                    $status = 'rejected';
                } else {
                    return back()->withErrors(['error' => 'Invalid status']);
                }

                $overtime->update([
                    'review_status' => $request->review_status,
                    'review_remark' => $request->review_remark,
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                    'status' => $status
                ]);

                if($request->has('actual_time_approved')){
                    if($overtime->actual_time_approved == null || $overtime->actual_time_approved != $request->actual_time_approved){
                        $overtime->update([
                            'actual_time_approved' => $request->actual_time_approved,
                            'actual_time_approved_updated_by' => $user->id
                        ]);
                    }
                }

                if($request->has('claim_as')){
                    // dont update if $overtime->claim_as is not null and is the same as $request->claim_as
                    if($overtime->claim_as == null || $overtime->claim_as != $request->claim_as){
                        $overtime->update([
                            'claim_as' => $request->claim_as,
                            'claim_as_updated_by' => $user->id
                        ]);
                    }
                }

                if ($overtime->user->email){
                    // Mail::to($overtime->user->email)->send(new OvertimeReview($overtime));
                }

                if ($overtime->status == 'reviewed') {
                    $leaveApproversEmail = $overtime->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                    $leaveApproversFCMToken = $overtime->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                    if(!$leaveApproversEmail->isEmpty()){
                        foreach ($leaveApproversEmail as $recipient) {
                            // Mail::to($recipient)->send(new OvertimeSubmit($overtime));
                        }
                    }

                    if (!empty($leaveApproversFCMToken)){
                        SendMultiFCMNotification::dispatch($leaveApproversFCMToken, [
                            'title' => 'Overtime submission needs review',
                            'body' => 'You have an overtime submission from ' . $overtime->user->name . ' to approve',
                            'path' => 'approval',
                            'type' => 'overtime',
                            'id' => $overtime->id,
                        ]);
                    }

                    if ($overtime->user->firebase_token) {
                        SendFCMNotification::dispatch($overtime->user->firebase_token, [
                            'title' => 'Overtime submission ' . $overtime->status,
                            'body' => 'Your overtime submission has been '.str_replace('_', '-', $overtime->status).' by ' . $user->name,
                            'path' => 'overtime',
                            'type' => 'overtime',
                            'id' => $overtime->id,
                        ]);
                    }
                } else {
                    if ($overtime->user->firebase_token) {
                        SendFCMNotification::dispatch($overtime->user->firebase_token, [
                            'title' => 'Overtime submission ' . $overtime->status,
                            'body' => 'Your overtime submission has been '.str_replace('_', '-', $overtime->status).' by ' . $user->name,
                            'path' => 'overtime',
                            'type' => 'overtime',
                            'id' => $overtime->id,
                        ]);
                    }
                }
            } else {
                return back()->withErrors(['error' => 'You are not authorized to perform this action']);
            }
        } elseif ($request->has('approval_status')) {
            if ($user->position->leave_approver){
                $validatedData = $request->validate([
                    'approval_status' => 'required|in:0,1',
                ]);

                // Check if the overtime is already approved and redirect back if it is with an error message
                if ($overtime->status != 'submitted' && $overtime->status != 'reviewed') {
                    return back()->withErrors(['error' => 'Overtime is already been ' . str_replace('_', '-', $overtime->status)]);
                }

                if ($request->approval_status == '1') {
                    $status = 'approved';
                } elseif ($request->approval_status == '0') {
                    $status = 'rejected';
                } else {
                    return back()->withErrors(['error' => 'Invalid status']);
                }

                $overtime->update([
                    'approval_status' => $request->approval_status,
                    'approval_remark' => $request->approval_remark,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'status' => $status
                ]);

                if ($request->has('actual_time_approved')){
                    if($overtime->actual_time_approved == null || $overtime->actual_time_approved != $request->actual_time_approved){
                        $overtime->update([
                            'actual_time_approved' => $request->actual_time_approved,
                            'actual_time_approved_updated_by' => $user->id
                        ]);
                    }
                }

                if($request->has('claim_as')){
                    // dont update if $overtime->claim_as is not null and is the same as $request->claim_as
                    if($overtime->claim_as == null || $overtime->claim_as != $request->claim_as){
                        $overtime->update([
                            'claim_as' => $request->claim_as,
                            'claim_as_updated_by' => $user->id
                        ]);
                    }
                }

                //check if claim as is replacement_leave
                if ($overtime->claim_as == 'replacement_leave') {
                    $replacementLeaveBalance = LeaveBalance::firstOrCreate([
                        'user_id' => $overtime->user_id,
                        'leave_type_id' => 5,
                    ]);

                    $replacementLeaveBalanceList = LeaveBalanceList::create([
                        'leave_balance_id' => $replacementLeaveBalance->id,
                        'description' => 'Replacement Leave for Overtime(ID: ' . $overtime->id . ') - ' . $overtime->reasons,
                        'balance' => $overtime->actual_time_taken,
                        'expiry_date' => date('Y-03-31', strtotime('+1 year')),
                        'year' => date('Y'),
                    ]);
                }

                if ($overtime->user->email){
                    // Mail::to($overtime->user->email)->send(new OvertimeApprove($overtime));
                }

                if ($overtime->user->firebase_token) {
                    SendFCMNotification::dispatch($overtime->user->firebase_token, [
                        'title' => 'Overtime submission ' . $overtime->status,
                        'body' => 'Your overtime submission has been '.str_replace('_', '-', $overtime->status).' by ' . $user->name,
                        'path' => 'overtime',
                        'type' => 'overtime',
                        'id' => $overtime->id,
                    ]);
                }
            } else {
                return back()->withErrors(['error' => 'You are not authorized to perform this action']);
            }
        }

        return view('overtime.request.create')->with('overtime', $overtime);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Overtime $overtime)
    {
        //
    }
}
