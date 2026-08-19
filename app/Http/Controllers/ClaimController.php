<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Attachment;
use App\Models\User;
use App\Models\Department;
use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Mail\ClaimSubmit;
use App\Mail\ClaimReview;
use App\Mail\ClaimApprove;
use App\Mail\ClaimCancel;

use App\Jobs\SendFCMNotification;
use App\Jobs\SendMultiFCMNotification;

class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the currently authenticated user
        $user = auth()->user();

        // Retrieve claims for the current user
        $claim = Claim::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return view('claim.index', [
            'claim' => $claim,
        ]);
    }

    public function requestIndex(Request $request)
    {
        // Get the currently authenticated user
        $user = auth()->user();
        $date_range = $request->query('date_range');
        
        if ($date_range) {
            $dates = explode(" to ", $date_range);
            $date_from = $dates[0];
            $date_to = isset($dates[1]) ? $dates[1] : null;
        } else {
            // empty
            $date_from = null;
            $date_to = null;
            $date_range = null;
        }

        $department_id = $request->query('department_id');
        $status = $request->query('status');
        $claim_type_id = $request->query('claim_type_id');
        $search = $request->query('search');
        $filter =[
            'date_from'=>$date_from, 
            'date_to'=>$date_to,
            'date_range'=>$date_range,
            'department_id'=>$department_id, 
            'status'=>$status,
            'claim_type_id' => $claim_type_id,
            'search'=>$search, 
        ];

        $department = Department::all();
        $claim_types = ClaimType::all();

        // check wether user is claim approver
        if ($user->position && $user->position->leave_reviewer) {
            // get employee id under the same department but not the user
            $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))->pluck('id');

            // get claim request for the employees
            $claim = Claim::whereIn('user_id', $employees)->whereNotIn('status', ['draft']);
        } elseif ($user->position && $user->position->leave_approver) {
            // get employee id under the same department but not the user
            $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))->pluck('id');
            
            // get claim that has is not in draft or submit status
            $claim = Claim::whereIn('user_id', $employees)->whereNotIn('status', ['draft']);
        } elseif ($user->isAn('superadmin')) {
            // get claim request for the user
            $claim = Claim::whereNotIn('status', ['draft']);
        } elseif ($user->isAn('account')) {
            $claim = Claim::where('status', 'approved');
        } else {
            // get claim request for the user
            return redirect()->route('claim.index');
        }

        if (!empty($date_from) && !empty($date_to)) {
            $claim = $claim->whereBetween('approved_at', [$date_from, $date_to]);            
        } elseif (!empty($date_from)) {
            $claim = $claim->whereDate('approved_at' , $date_from);
        }

        if (!empty($department_id) && $department_id > 0) {
            $claim = $claim->whereHas('user', function ($query) use ($department_id) {
                $query->where('department_id', $department_id);
            });
        }

        if($status <>null && $status <> ''){
            $claim = $claim->where('status',$status);
        }

        if($claim_type_id <>null && $claim_type_id <> ''){
            $claim = $claim->where('claim_type_id',$claim_type_id);
        }

        if(!empty($search)){
            $claim = $claim->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('staff_id', 'like', '%'.$search.'%');
            });
        }

        $claim = $claim->orderBy('created_at', 'desc')->get();

        return view('claim.request.index', [
            'claim' => $claim,
            'department' => $department,
            'filter' => $filter,
            'claim_types' => $claim_types
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('claim.create')->with([
            'claim_type' => ClaimType::all(),
            'amount_type_list' => [
                'MYR',
                'Days',
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->has('status')){
            if ($request->status != 'cancelled') {
                $request->validate([
                    'claim_type_id' => 'required',
                    'amount' => 'required',
                ]);

                $claim_type = ClaimType::find($request->claim_type_id);
                if ($claim_type->unit_price){
                    $request->validate([
                        'unit_quantity' => 'required',
                    ]);

                    $amount = $request->unit_quantity * $claim_type->unit_price_value;
                    $request->merge(['amount' => $amount]);
                }
            } else {
                $request->validate([
                    'claim_id' => 'required',
                ]);
            }
        } else { 
            $request->validate([
                'claim_id' => 'required',
                'attachment' => 'required',
            ]);
        }

        $claim = Claim::find($request->claim_id);
        if ($claim) {
            if ($claim->attachments->isEmpty()) {
                $request->validate([
                    'attachment' => 'required',
                ]);
            }
            $claim->update($request->all());
        } else {
            if(isset($request->purchase_requisition_id)){
                $purchaseRequisition = PurchaseRequisition::find($request->purchase_requisition_id);
                // check if the purchase requisition is already claimed
                if ($purchaseRequisition->activeClaims->isNotEmpty()) {
                    $claim = $purchaseRequisition->activeClaims->first();
                    return redirect()->route('purchase_requisition.index')->with('error', "Purchase Requisition's claim exists (status: ".$claim->status.")");
                }
            }
                
            $request->validate([
                'attachment' => 'required',
            ]);
            $claim = Claim::create($request->all());
        }

        // Handle attachments
        if ($request->hasFile('attachment')) {
            $attachmentFiles = $request->file('attachment');
            if (!is_array($attachmentFiles)) {
                $attachmentFiles = [$attachmentFiles]; // Convert to array if it's a single file
            }
        
            foreach ($attachmentFiles as $attachmentFile) {
                $path = "claim/".$claim->id."/".$attachmentFile->getClientOriginalName();
                $attachment = new Attachment([
                    'filename' => $attachmentFile->getClientOriginalName(),
                    'path' => $path,
                ]);
                $attachmentFile->storeAs('attachments', $path);
                $claim->attachments()->save($attachment);
            }
        } 
        
        if($claim->status == 'submitted') {
            if($claim->user->getLeaveReviewers()->isNotEmpty()){
                $claimReviewersEmail = $claim->user->getLeaveReviewers()->whereNotNull('email')->pluck('email');
                $claimReviewersFCMToken = $claim->user->getLeaveReviewers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$claimReviewersEmail->isEmpty()) {
                    // Send the email to the claim reviewers
                    foreach ($claimReviewersEmail as $recipient) {
                        // Mail::to($recipient)->send(new ClaimSubmit($claim));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($claimReviewersFCMToken)) {
                    // Send the notification to the claim reviewers
                    SendMultiFCMNotification::dispatch($claimReviewersFCMToken, [
                        'title' => 'Claim request needs review',
                        'body' => 'You have a claim request from ' . $claim->user->name . ' to review',
                        'path' => 'approval',
                        'type' => 'claim',
                        'id' => $claim->id,
                    ]);
                }
                
            } else if ($claim->user->getLeaveApprovers()->isNotEmpty()){
                $claim->update(['status' => 'reviewed']);

                $claimApproversEmail = $claim->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                $claimApproversFCMToken = $claim->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$claimApproversEmail->isEmpty()) {
                    // Send the email to the claim approvers
                    foreach ($claimApproversEmail as $recipient) {
                        // Mail::to($recipient)->send(new ClaimSubmit($claim));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($claimApproversFCMToken)) {
                    // Send the notification to the claim approvers
                    SendMultiFCMNotification::dispatch($claimApproversFCMToken, [
                        'title' => 'Claim request needs approval',
                        'body' => 'You have a claim request from ' . $claim->user->name . ' to approve',
                        'path' => 'approval',
                        'type' => 'claim',
                        'id' => $claim->id,
                    ]);
                }
            }
        } elseif ($claim->status == 'cancelled') {
            // Email the reviewer and approver if the claim was cancelled and was in review or approved status
            if ($claim->review_status && $claim->reviewed_by) {
                if ($claim->reviewer->email){
                    // Mail::to($claim->reviewer->email)->send(new ClaimCancel($claim));
                }
                if ($claim->reviewer->firebase_token){
                    SendFCMNotification::dispatch($claim->reviewer->firebase_token, [
                        'title' => 'Claim request cancelled',
                        'body' => $claim->user->name . ' has cancelled their claim request',
                        'path' => 'approval',
                        'type' => 'claim',
                        'id' => $claim->id,
                    ]);
                }
            }
            if ($claim->approval_status && $claim->approved_by) {
                if ($claim->approver->email){
                    // Mail::to($claim->approver->email)->send(new ClaimCancel($claim));
                }
                if ($claim->approver->firebase_token){
                    SendFCMNotification::dispatch($claim->reviewer->firebase_token, [
                        'title' => 'Claim request cancelled',
                        'body' => $claim->user->name . ' has cancelled their claim request',
                        'path' => 'approval',
                        'type' => 'claim',
                        'id' => $claim->id,
                    ]);
                }
            }
        }
        
        if (isset($request->status)){
            return redirect()->route('claim.index');
        } else {
            return redirect()->route('claim.edit', $claim);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Claim $claim)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Claim $claim)
    {
        // return $claim;
        return view('claim.create')->with([
            'claim' => $claim,
            'claim_type' => ClaimType::all(),
            'amount_type_list' => [
                'MYR',
                'Days',
            ]
        ]);
    }

    public function requestEdit(Claim $claim)
    {
        // return $claim;
        return view('claim.request.create')->with([
            'claim' => $claim,
            'claim_type' => ClaimType::all(),
            'amount_type_list' => [
                'MYR',
                'Days',
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Claim $claim)
    {
        //
    }

    public function requestUpdate(Request $request, Claim $claim)
    {
        $user = auth()->user();

        if ($user->position->leave_reviewer) {
            // Validate the input
            $validatedData = $request->validate([
                'review_status' => 'required|in:0,1', // review_status must be 0 or 1
            ]);

            // Check if the claim is already reviewed and redirect back if it is with an error message
            if ($claim->status == 'reviewed'){
                return redirect()->route('claim.requestIndex')->with('error', 'Claim request is already reviewed');
            } elseif ($claim->status == 'approved') {
                return redirect()->route('claim.requestIndex')->with('error', 'Claim request is already approved');
            } elseif ($claim->status == 'rejected') {
                return redirect()->route('claim.requestIndex')->with('error', 'Claim request is already rejected');
            } elseif ($claim->status == 'cancelled') {
                return redirect()->route('claim.requestIndex')->with('error', 'Claim request is already cancelled');
            }

            if ($request->review_status == '1') {
                $status = 'reviewed';
            } elseif ($request->review_status == '0') {
                $status = 'rejected';
            } else {
                return redirect()->route('claim.requestIndex');
            }

            $claim->update([
                'review_status' => $request->review_status,
                'review_remark' => $request->review_remark,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'status' => $status
            ]);

            if($request->review_status == '1') {
                $claimApproversEmail = $claim->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                $claimApproversFCMToken = $claim->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$claimApproversEmail->isEmpty()) {
                    // Send the email to the claim approvers
                    foreach ($claimApproversEmail as $recipient) {
                        // Mail::to($recipient)->send(new ClaimSubmit($claim));
                    }
                }
            
                // Check if FCM tokens are not empty
                if (!empty($claimApproversFCMToken)){
                    // Send the notification to the claim approvers
                    SendMultiFCMNotification::dispatch($claimApproversFCMToken, [
                        'title' => 'Claim request needs approval',
                        'body' => 'You have a claim request from ' . $claim->user->name . ' to approve',
                        'path' => 'approval',
                        'type' => 'claim',
                        'id' => $claim->id,
                    ]);
                }

                if ($claim->user->firebase_token){
                    SendFCMNotification::dispatch($claim->user->firebase_token, [
                        'title' => 'Claim request reviewed',
                        'body' => 'Your claim request has been reviewed by ' . $user->name,
                        'path' => 'claim',
                        'type' => 'claim',
                        'id' => $claim->id,
                    ]);
                }

            } else {
                if ($claim->user->firebase_token){
                    SendFCMNotification::dispatch($claim->user->firebase_token, [
                        'title' => 'Claim request rejected',
                        'body' => 'Your claim request has been rejected by ' . $user->name,
                        'path' => 'claim',
                        'type' => 'claim',
                        'id' => $claim->id,
                    ]);
                }
            }

            if ($claim->user->email){
                // Mail::to($claim->user->email)->send(new ClaimReview($claim));
            }

        } elseif ($user->position->leave_approver) {
            // Validate the input
            $validatedData = $request->validate([
                'approval_status' => 'required|in:0,1', // approve_status must be 0 or 1
            ]);

            // Check if the claim is already approved and redirect back if it is with an error message
            if ($claim->status == 'approved'){
                return redirect()->route('claim.requestIndex')->with('error', 'Claim request is already approved');
            } elseif ($claim->status == 'rejected') {
                return redirect()->route('claim.requestIndex')->with('error', 'Claim request is already rejected');
            } elseif ($claim->status == 'cancelled') {
                return redirect()->route('claim.requestIndex')->with('error', 'Claim request is already cancelled');
            }

            if ($request->approval_status == '1') {
                $status = 'approved';
            } elseif ($request->approval_status == '0') {
                $status = 'rejected';
            } else {
                return redirect()->route('claim.requestIndex');
            }

            $claim->update([
                'approval_status' => $request->approval_status,
                'approval_remark' => $request->approval_remark,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'status' => $status
            ]);

            if ($claim->user->firebase_token){
                // Mail::to($claim->user->email)->send(new ClaimApprove($claim));
            }

            if ($request->approval_status == '1') {
                if ($claim->user->firebase_token){
                    SendFCMNotification::dispatch($claim->user->firebase_token, [
                        'title' => 'Claim request approved',
                        'body' => 'Your claim request has been approved by ' . $user->name,
                        'path' => 'claim',
                        'type' => 'claim',
                        'id' => $claim->id,
                    ]);
                }
            } else {
                if ($claim->user->firebase_token){
                    SendFCMNotification::dispatch($claim->user->firebase_token, [
                        'title' => 'Claim request rejected',
                        'body' => 'Your claim request has been rejected by ' . $user->name,
                        'path' => 'claim',
                        'type' => 'claim',
                        'id' => $claim->id,
                    ]);
                }
            }
        }

        return redirect()->route('claim.requestIndex');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Claim $claim)
    {
        $claim->delete();
        return redirect()->route('claim.index');
    }
}
