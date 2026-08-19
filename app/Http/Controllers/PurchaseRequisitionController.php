<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Department;
use App\Models\Attachment;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseRequisitionSubmit;
use App\Mail\PurchaseRequisitionApprove;
use App\Mail\PurchaseRequisitionCancel;

use App\Jobs\SendFCMNotification;
use App\Jobs\SendMultiFCMNotification;

use PDF;

class PurchaseRequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseRequisitions = PurchaseRequisition::where('user_id', auth()->user()->id)->get();
        return view('purchase_requisition.index', compact('purchaseRequisitions'));
    }

    public function requestIndex(Request $request)
    {
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
        $search = $request->query('search');
        $filter =[
            'date_from'=>$date_from, 
            'date_to'=>$date_to,
            'date_range'=>$date_range,
            'department_id'=>$department_id, 
            'status'=>$status,
            'search'=>$search, 
        ];

        $department = Department::all();

        if ($user->isAn('superadmin', 'management')) {
            $purchaseRequisitions = PurchaseRequisition::whereNotIn('status', ['draft']);
        } elseif ($user->isAn('account')) {
            $purchaseRequisitions = PurchaseRequisition::where('status', 'approved');
        } else {
            return redirect()->route('purchase_requisition.index');
        }

        if (!empty($date_from) && !empty($date_to)) {
            $purchaseRequisitions = $purchaseRequisitions->whereBetween('approved_at', [$date_from, $date_to]);            
        } elseif (!empty($date_from)) {
            $purchaseRequisitions = $purchaseRequisitions->whereDate('approved_at' , $date_from);
        }

        if (!empty($department_id) && $department_id > 0) {
            $purchaseRequisitions = $purchaseRequisitions->whereHas('user', function ($query) use ($department_id) {
                $query->where('department_id', $department_id);
            });
        }

        if($status <>null && $status <> ''){
            $purchaseRequisitions = $purchaseRequisitions->where('status',$status);
        }

        if(!empty($search)){
            $purchaseRequisitions = $purchaseRequisitions->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('staff_id', 'like', '%'.$search.'%');
            });
        }

        $purchaseRequisitions = $purchaseRequisitions->orderBy('created_at', 'desc')->get();

        return view('purchase_requisition.request.index', compact('purchaseRequisitions', 'department', 'filter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        return view('purchase_requisition.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return $request;
        if (!$request->has('status')) {
            $request->validate([
                'purchase_requisition_id' => 'required',
                'attachment' => 'required',
            ]);
        } else {
            if ($request->status != 'cancelled'){
                $request->validate([
                    'user_id' => 'required',
                    'purpose' => 'required',
                    'date_needed' => 'required',
                    'source_of_fund' => 'required',
                    'auto_renew' => 'required',
                    'status' => 'required',
                    'remarks' => 'required',
                    'items' => 'required',
                    'items.*.quantity' => 'required',
                    'items.*.description' => 'required',
                ]);
            }
        }

        $purchaseRequisition = PurchaseRequisition::find($request->purchase_requisition_id);

        if ($purchaseRequisition) {
            $purchaseRequisition->update($request->all());
        } else {
            $purchaseRequisition = PurchaseRequisition::create($request->all());
        }

        // Handle items
        if ($request->items) {
            $requestItemIds = collect($request->items)->pluck('purchase_requisition_item_id')->filter()->toArray();
        
            // Delete items not in the request
            $purchaseRequisition->purchaseRequisitionItems()->whereNotIn('id', $requestItemIds)->delete();
        
            // Update or create items in the request
            foreach ($request->items as $item) {
                $purchaseRequisitionItem = isset($item['purchase_requisition_item_id'])
                    ? PurchaseRequisitionItem::find($item['purchase_requisition_item_id'])
                    : null;
        
                if ($purchaseRequisitionItem) {
                    $purchaseRequisitionItem->update($item);
                } else {
                    $item['purchase_requisition_id'] = $purchaseRequisition->id;
                    PurchaseRequisitionItem::create($item);
                }
            }
        }
        
        // Handle attachments
        if ($request->hasFile('attachment')) {
            $attachmentFiles = $request->file('attachment');
            if (!is_array($attachmentFiles)) {
                $attachmentFiles = [$attachmentFiles]; // Convert to array if it's a single file
            }
        
            foreach ($attachmentFiles as $attachmentFile) {
                $path = "purchase_requisition/".$purchaseRequisition->id."/".$attachmentFile->getClientOriginalName();
                $attachment = new Attachment([
                    'filename' => $attachmentFile->getClientOriginalName(),
                    'path' => $path,
                ]);
                $attachmentFile->storeAs('attachments', $path);
                $purchaseRequisition->attachments()->save($attachment);
            }
        }

        if($purchaseRequisition->status == 'submitted') {
            if($purchaseRequisition->user->getLeaveApprovers()->isNotEmpty()){

                $purchaseRequisitionApproversEmail = $purchaseRequisition->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                $purchaseRequisitionApproversFCMToken = $purchaseRequisition->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                // Check if email recipients are not empty
                if (!$purchaseRequisitionApproversEmail->isEmpty()) {
                    // Send the email to the purchase requisition approvers
                    foreach ($purchaseRequisitionApproversEmail as $recipient) {
                        // Mail::to($recipient)->send(new PurchaseRequisitionSubmit($purchaseRequisition));
                    }
                }

                // Check if FCM tokens are not empty
                if (!empty($purchaseRequisitionApproversFCMToken)) {
                    // Send the notification to the purchase requisition approvers
                    SendMultiFCMNotification::dispatch($purchaseRequisitionApproversFCMToken, [
                        'title' => 'Purchase Requisition needs approval',
                        'body' => 'You have a purchase requisition from ' . $purchaseRequisition->user->name . ' to approve',
                        'path' => 'approval',
                        'type' => 'purchaseRequisition',
                        'id' => $purchaseRequisition->id,
                    ]);
                }
            }
        } elseif ($purchaseRequisition->status == 'cancelled') {
            if ($purchaseRequisition->approval_status && $purchaseRequisition->approved_by) {
                if ($purchaseRequisition->approver->email){
                    // Mail::to($purchaseRequisition->approver->email)->send(new PurchaseRequisitionCancel($purchaseRequisition));
                }
                if ($purchaseRequisition->approver->firebase_token){
                    SendFCMNotification::dispatch($purchaseRequisition->approver->firebase_token, [
                        'title' => 'Purchase Requisition cancelled',
                        'body' => $purchaseRequisition->user->name . ' has cancelled their purchase requisition',
                        'path' => 'approval',
                        'type' => 'purchaseRequisition',
                        'id' => $purchaseRequisition->id,
                    ]);
                }
            }
        }

        if (isset($request->status)){
            return redirect()->route('purchase_requisition.index');
        } else {
            return redirect()->route('purchase_requisition.edit', $purchaseRequisition);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseRequisition $purchaseRequisition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseRequisition $purchaseRequisition)
    {
        $departments = Department::all();
        return view('purchase_requisition.create', compact('purchaseRequisition', 'departments'));
    }

    public function requestEdit(PurchaseRequisition $purchaseRequisition)
    {
        $departments = Department::all();
        return view('purchase_requisition.request.create', compact('purchaseRequisition', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        //
    }

    public function requestUpdate(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $user = auth()->user();

        if ($user->position->leave_approver) {
            // Validate the input
            $validatedData = $request->validate([
                'approval_status' => 'required|in:0,1', // approve_status must be 0 or 1
            ]);

            // Check if the purchase requisition is already approved and redirect back if it is with an error message
            if ($purchaseRequisition->status == 'approved'){
                return redirect()->route('purchase_requisition.requestIndex')->with('error', 'Purchase Requisition is already approved');
            } elseif ($purchaseRequisition->status == 'rejected') {
                return redirect()->route('purchase_requisition.requestIndex')->with('error', 'Purchase Requisition is already rejected');
            } elseif ($purchaseRequisition->status == 'cancelled') {
                return redirect()->route('purchase_requisition.requestIndex')->with('error', 'Purchase Requisition is already cancelled');
            }

            if ($request->approval_status == '1') {
                $status = 'approved';
            } elseif ($request->approval_status == '0') {
                $status = 'rejected';
            } else {
                return redirect()->route('purchase_requisition.requestIndex');
            }

            $purchaseRequisition->update([
                'approval_status' => $request->approval_status,
                'approval_remark' => $request->approval_remark,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'status' => $status
            ]);

            if ($purchaseRequisition->user->firebase_token){
                // Mail::to($purchaseRequisition->user->email)->send(new PurchaseRequisitionApprove($purchaseRequisition));
            }

            if ($purchaseRequisition->user->firebase_token){
                SendFCMNotification::dispatch($purchaseRequisition->user->firebase_token, [
                    'title' => 'Purchase Requisition ' . $purchaseRequisition->status,
                    'body' => 'Your purchase requisition has been '.$purchaseRequisition->status.' by ' . $user->name,
                    'path' => 'purchaseRequisition',
                    'type' => 'purchaseRequisition',
                    'id' => $purchaseRequisition->id,
                ]);
            }
        } 

        return redirect()->route('purchase_requisition.requestIndex');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseRequisition $purchaseRequisition)
    {
        //
    }

    public function download(PurchaseRequisition $purchaseRequisition)
    {
        // return view('purchase_requisition.download', compact('purchaseRequisition'));
        $pdf = PDF::loadView('purchase_requisition.download', compact('purchaseRequisition'));
        $pdf->setPaper('A5', 'landscape');
        return $pdf->stream();
        // return $pdf->download('Purchase_Requisition_'.$purchaseRequisition->id.'.pdf');
    }

    public function createClaim(PurchaseRequisition $purchaseRequisition)
    {
        $claimTypes = ClaimType::where('id', 2)->get();
        return view('purchase_requisition.create_claim', compact('purchaseRequisition', 'claimTypes'));
    }
}
