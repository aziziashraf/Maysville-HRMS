<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationTarget;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;
use App\Jobs\SendMultiFCMNotification;
use Illuminate\Support\Facades\Auth;
use Silber\Bouncer\Bouncer;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notification = Notification::all();

        $department = Department::all();

        foreach($notification as $row){
            $department_ids = $row->notiTargetGroupByDepartment->pluck('department_id')->all();
            $row->department_names = $department->whereIn('id',$department_ids)->pluck('department_name')->all();
        }

        return view('notification.index')->with('notification',$notification);
    }

    public function indexUser ()
    {
        $user = Auth::user();
        $notificationIDs = NotificationTarget::whereHas('notification', function ($query) {
            $query->where('status', 'Sent');
        })->where('user_id', $user->id)
        ->pluck('notification_id');

        $notification = Notification::whereIn('id', $notificationIDs)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $department = Department::all();

        foreach($notification as $row){
            $department_ids = $row->notiTargetGroupByDepartment->pluck('department_id')->all();
            $row->department_names = $department->whereIn('id',$department_ids)->pluck('department_name')->all();
        }

        return view('notification.indexUser')->with('notification', $notification);
    }

    public function create()
    {
        $notification = [];
        $department = Department::all();
        $tenant = User::where('role','tenant')->where('is_active',1)->get();
        return view('notification.create')->with('notification',$notification)->with('department',$department)->with('tenant',$tenant);
    }

    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required',
            'short_descriptions' => 'required',
            'descriptions' => 'required',
            'notification_type' => 'required',
            'status' => 'required',
            'department' => 'required_if:notification_type,specific_division|array',
        ]);

        if($request->notification_id >0){
            $notification=Notification::find($request->notification_id);
            $notification->update($request->all());
        }else{
            $notification = Notification::create($request->all());
        }
        $notification->notiTarget()->forceDelete();

        switch ($request->notification_type) {
            case "all_employee":
                $all_employee = User::where('role','employee')->where('is_active',1)->get();
                foreach($all_employee as $a){
                    $datas = [
                        'notification__id'=>$notification->id,
                        'user_id'=>$a->id,
                        'department_id'=>$a->department_id,
                    ];
                    $notification->notiTarget()->create($datas);
                }
                break;
            case "all_tenant":
                $all_tenant = User::where('role','tenant')->where('is_active',1)->get();
                foreach($all_tenant as $b){
                    $datas = [
                        'notification__id'=>$notification->id,
                        'user_id'=>$b->id,
                        'department_id'=>$b->department_id,
                    ];
                    $notification->notiTarget()->create($datas);
                }
                break;
            case "specific_division":
                if(count($request->department)>0){
                    $department_ids = array();
                    foreach($request->department as $c){
                        array_push($department_ids,$c);
                    }
                    $all_user = User::whereIn('department_id',$department_ids)->where('is_active',1)->get();
                    foreach($all_user as $d){
                        $datas = [
                            'notification__id'=>$notification->id,
                            'user_id'=>$d->id,
                            'department_id'=>$d->department_id,
                        ];
                        $notification->notiTarget()->create($datas);
                    }
                }
                break;
            case "specific_tenant":
                if(count($request->tenant)>0){
                    $tenant_ids = array();
                    foreach($request->tenant as $e){
                        array_push($tenant_ids,$e);
                    }
                    $all_tenants = User::whereIn('id',$tenant_ids)->where('is_active',1)->get();
                    foreach($all_tenants as $f){
                        $datas = [
                            'notification__id'=>$notification->id,
                            'user_id'=>$f->id,
                            'department_id'=>$f->department_id,
                        ];
                        $notification->notiTarget()->create($datas);
                    }
                }
                break;
            default:
                echo "No type select";
        }

        if($request->status == "Sent"){
            $notification->update(['send_datetime' => date('Y-m-d H:i:s')]);

            $targets = NotificationTarget::where('notification_id',$notification->id)->get();
            $firebaseTokens = array();
            $allEmails = array();
            foreach($targets as $target){
                if ($target->user->firebase_token != null) {
                    array_push($firebaseTokens,$target->user->firebase_token);
                }
                array_push($allEmails,$target->user->email);
            }
            
            $FCMdata = [
                'title'     => $notification->title,
                'body'      => $notification->descriptions,
                'path'      => 'announcement',
                'type'      => 'announcement',
                'id'        => $notification->id
            ];

            SendMultiFCMNotification::dispatch($firebaseTokens, $FCMdata);

            $data = array('name'=>$target->user->name,'notification_description'=>$notification->descriptions);
            
        }
        
        //exit;
        return redirect()->route('notification.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function show(Notification $notification)
    {
        $department = Department::all();
        $tenant = User::where('role','tenant')->where('is_active',1)->get();
        $notification->department_idss = $notification->notiTargetGroupByDepartment->pluck('department_id')->all();
        $notification->tenants_idss = $notification->notiTarget->pluck('user_id')->all();
        return view('notification.create')->with('notification',$notification)->with('department',$department)->with('tenant',$tenant)->with('show',true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function edit(Notification $notification)
    {
        $department = Department::all();
        $tenant = User::where('role','tenant')->where('is_active',1)->get();
        $notification->department_idss = $notification->notiTargetGroupByDepartment->pluck('department_id')->all();
        $notification->tenants_idss = $notification->notiTarget->pluck('user_id')->all();
        return view('notification.create')->with('notification',$notification)->with('department',$department)->with('tenant',$tenant);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notification $notification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();
        $notification->notiTarget()->delete();
        return redirect()->back();
    }
}
