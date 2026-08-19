<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\EmployeeDailyAttendance;
use App\Models\Event;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceList;
use App\Models\LeaveType;
use App\Models\NotificationTarget;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserAccessFloor;
use App\Models\UserAttendance;
use App\Models\VisitorPass;
use App\Models\ClaimType;
use App\Models\Claim;
use App\Models\Overtime;
use App\Models\HandBookCategory;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;

use App\Http\Controllers\LeaveController;

use App\Mail\LeaveApprove;
use App\Mail\LeaveCancel;
use App\Mail\LeaveReview;
use App\Mail\LeaveSubmit;
use App\Mail\ClaimApprove;
use App\Mail\ClaimCancel;
use App\Mail\ClaimReview;
use App\Mail\ClaimSubmit;
use App\Mail\OvertimeRequest;
use App\Mail\OvertimePreReview;
use App\Mail\OvertimeSubmit;
use App\Mail\OvertimeReview;
use App\Mail\OvertimeApprove;
use App\Mail\OvertimeCancel;
use App\Mail\PurchaseRequisitionSubmit;
use App\Mail\PurchaseRequisitionApprove;
use App\Mail\PurchaseRequisitionCancel;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DB;
use DateTime;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Rule;

use App\Jobs\SendFCMNotification;
use App\Jobs\SendMultiFCMNotification;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiController extends Controller
{

    public function app_login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
        $checkEmail = User::where('email',$request->email)->where('is_active',1)->first();
        //dd(auth()->attempt($credentials));
        if (auth()->attempt($credentials) && isset($checkEmail)) {
            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
            $userdetails = User::find(auth()->user()->id);
            $findaccess = $userdetails->accessFloor;
            $access = array();
            array_push($access,$userdetails->department ? $userdetails->department->check_in_out_access_floor:null);
            $currentDate = Carbon::now()->format('Y-m-d');
            $userAttendance = UserAttendance::where('user_id',$userdetails->id)->where('scan_date',$currentDate)->where('access_id',null)->get();
            $no_count = count($userAttendance);
            $button_text ="Disabled";
            $disable = true;
            if($no_count ==  0){
                $button_text ="Clock-In";
                $disable = false;
            }else if($no_count == 1){
                $button_text ="Clock-Out";
                $disable = false;
            }
            $photo = asset('images/logo-color.png');
            if(isset($userdetails->profile_image)){
                $photo = asset('/storage/images/'.$userdetails->profile_image);
            }
            $user = [
                'user_id' => $userdetails->id,
                'name' => $userdetails->name,
                'email' => $userdetails->email,
                'username' => $userdetails->username,
                'profile_image' =>$photo,
                'staff_id' => $userdetails->staff_id??'-',
                'role' => $userdetails->role,
                'department' => $userdetails->department ? $userdetails->department->department_name:null,
                'contact_no' => $userdetails->contact_no,
                'button_text' => $button_text,
                'disable' => $disable,
                'access'=>$access,
                'position' => $userdetails->position ? $userdetails->position->name : null,
                'role' => $userdetails->getRoles()->first(),
            ];
            return response()->json(['token' => $token,'user'=>$user], 200);
        } else {
            return response()->json(['error' => 'UnAuthorised'], 401);
        }
    }

    public function getEmployeeTenant()
    {
        $employee = User::with('department','company')->where('role','<>','visitor')->get();
        return response()->json($employee);
    }

    public function getEmployee(Request $request){
        $data = User::where('role','employee')->where('is_active',1)->select('id','name','email')->get();
        return response()->json($data);
    }

    public function getQRcode(Request $request){
        $user = User::find($request->user_id);
        $currentDateTime = Carbon::now();
        $newDateTime = Carbon::now()->addMinutes(1);
        $timestamp = $currentDateTime->timestamp; 
        if($currentDateTime <= $user->qr_expired_datetime){
            $user->update(['qr_expired_datetime'=>$newDateTime]);
        }else{
            $qr =$user->id;
            $qr.=$timestamp; 
            $user->update(['qr_access'=>$qr,'qr_expired_datetime'=>$newDateTime]);
        }
        $data=[
            'user_id'=>$user->id,
            'timestamp'=>$timestamp,
            'qr_access'=>$user->qr_access,
            'datenow'=>$currentDateTime,
            'qr_expired_datetime'=>$newDateTime,
        ];
        return response()->json($data);
    }

    public function getClockInHistory(Request $request){
        $user = User::find($request->user_id);
        $attendance = UserAttendance::where('user_id',$request->user_id)->orderBy('id','desc')->get();
        foreach($attendance as $a){
            $a->location = $a->location ?? $a->access?->access_name ?? "-";
        }
        return response()->json($attendance);
    }

    public function getAttendance(Request $request){
        $user = User::find($request->user_id);
        $attendance = EmployeeDailyAttendance::where('user_id',$request->user_id)->orderBy('id','desc')->get();
        return response()->json($attendance);
    }

    public function getNotificationByUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            $notificationIDs = NotificationTarget::whereHas('notification', function ($query) {
                $query->where('status', 'Sent');
            })->where('user_id', $request->user_id)
            ->pluck('notification_id');

            $notification = Notification::whereIn('id', $notificationIDs)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($notification as $n) {
                if($n->created_by){
                    $publisher = User::find($n->created_by);
                    $n->publisher_name = $publisher->name;
                    if ($publisher->profile_image) {
                        $n->publisher_profile_image = asset('/storage/images/'.$publisher->profile_image);
                    } else {
                        $n->publisher_profile_image = asset('images/logo-color.png');
                    }
                } else {
                    $n->publisher_name = 'System';
                    $n->publisher_profile_image = asset('images/logo-color.png');
                }
                $n->color = '#f59542';
            }

            return response()->json($notification);
        } catch (\Exception $e) {
            // Handle the exception, e.g., log it or return an error response
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getNotificationByID (Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'notification_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $notification = Notification::find($request->notification_id);

            if (!$notification) {
                return response()->json(['success' => false, 'message' => 'Notification not found'], 200);
            }

            if ($notification->created_by) {
                $publisher = User::find($notification->created_by);
                $notification->publisher_name = $publisher->name;
                if ($publisher->profile_image) {
                    $notification->publisher_profile_image = asset('/storage/images/'.$publisher->profile_image);
                } else {
                    $notification->publisher_profile_image = asset('images/logo-color.png');
                }
            } else {
                $notification->publisher_name = 'System';
                $notification->publisher_profile_image = asset('images/logo-color.png');
            }

            $notification->color = '#f59542';

            return response()->json($notification);
        } catch (\Exception $e) {
            // Handle the exception, e.g., log it or return an error response
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function submitAttendanceRemark(Request $request){
        if($request->attendance_id >0){
            if($request->remarks !=""){
                UserAttendance::where('id',$request->attendance_id)->update(['remarks'=>$request->remarks]);
                $message = "Remarks Updated successfully!";
            }else{
                $message = "No Remarks Found!";
            }
        }else{
            $message = "No Attendance Found!";
        }
        return response()->json($message);
    }

    public function updateProfile(Request $request){
        if($request->user_id >0){
            $user = User::find($request->user_id);
            $user->update($request->all());
            $message = "Profile Updated successfully!";
        }else{
            $message = "User ID not found!";
        }
        return response()->json($message);
    }

    public function app_forgotPassword(Request $request){
        $findEmail = User::where('email',$request->email)->first();
        $hasEmail = false;
        if(isset($findEmail)){
            $hasEmail = true;
        }
        if($hasEmail){
            $token = Str::random(64);
            DB::table('password_resets')->insert([
                'email' => $request->email, 
                'token' => $token, 
                'created_at' => Carbon::now()
              ]);
            Mail::send('email.forgetPassword', ['token' => $token], function($message) use($request){
                $message->to($request->email);
                $message->subject('Reset Password');
            });
            $messagereturn =[
                'message'=>'We have e-mailed your password reset link!',
                'hasEmail'=>$hasEmail,
            ];
        }else{
            $messagereturn =[
                'message'=>'No such email',
                'hasEmail'=>$hasEmail,
            ];
        }

        return response()->json($messagereturn);
    }

    public function remoteWorkingSubmit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'location' => 'required|in:HOME,CLIENT VISIT',
                'coordinates' => 'required',
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            $currentDateTime = now();
            $currentDate = $currentDateTime->format('Y-m-d');
            $currentTime = $currentDateTime->format('H:i:s');

            $userAttendance = UserAttendance::where('user_id', $request->user_id)
                ->where('scan_date', $currentDate)
                ->where('access_id', null)
                ->where('location', $request->location)
                ->orderBy('scan_datetime', 'desc')
                ->first();

            if (!$userAttendance) {
                $scan_status = "clock-in";
                $message = "Clock In Successfully!";
                $button_text = "Clock-Out";
                $disable = false;
            } else {
                $lastScanStatus = $userAttendance->scan_status ?? null;

                $scan_status = ($lastScanStatus == "clock-in") ? "clock-out" : "clock-in";
                $message = ($lastScanStatus == "clock-in") ? "Clock Out Successfully!" : "Clock In Successfully!";
                $button_text = ($lastScanStatus == "clock-in") ? "Clock-In" : "Clock-Out";
                $disable = false;
            }

            $data = [
                'user_id' => $request->user_id,
                'scan_datetime' => $currentDateTime,
                'scan_date' => $currentDate,
                'scan_time' => $currentTime,
                'remarks' => $request->input('remarks', ''),
                'scan_status' => $scan_status,
                'location' => $request->location,
                'coordinates' => $request->coordinates ?? NULL,
            ];

            UserAttendance::create($data);
            $success = true;

            $respon_return = [
                'message' => $message,
                'success' => $success,
                'button_text' => $button_text,
                'disable' => $disable,
            ];

            return response()->json($respon_return);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function changeNewPassword(Request $request){
        if($request->user_id >0){
            $user = User::find($request->user_id);
            $old_password = $request->old_password; 
            $new_password = $request->new_password; 
            if (Hash::check($old_password, $user->password)) {
                $success = true;
                $user->update(['password' => Hash::make($new_password)]);
                $message = "Password Updated";
            }else{
                $success = false;
                $message = "Wrong Old Password";
            }

        }else{
            $success = false;
            $message = "User ID not found!";
        }
        
        $respon_return =[
            'message' =>$message,
            'success' =>$success,
        ];
        return response()->json($respon_return);
    }

    public function saveFirebaseToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'firebase_token' => 'required'
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }
    
            $user = User::find($request->user_id);

            if($user){
                $user->update(['firebase_token'=>$request->firebase_token]);
                return response()->json(['success' => true, 'message' => 'Token saved successfully.'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendNotificationsss(Request $request)
    {
        $firebaseToken = User::where('id',$request->user_id)->select('firebase_token')->first();
        $SERVER_API_KEY = env('FIREBASE_KEY');
        //print_r($firebaseToken);
        $data = [
            "to" => $firebaseToken->firebase_token,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,  
            ]
        ];
        $dataString = json_encode($data);
        print_r($dataString);
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
  
        $ch = curl_init();
  
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
              
        $response = curl_exec($ch);

        return "yesss";
        // dd($response);
    }

    public function createVisitor(Request $request)
    {
        $checkEmail = User::where('email',$request->email)->first();

        if(!isset($checkEmail)){
            $request->merge(['password' => Hash::make($request->password),'role'=>'visitor','is_active'=>2]);
            $user = User::create($request->all());
            $data = array('name'=>$user->name,'email'=>$user->email);
            Mail::send(['text'=>'email.visitor_register_mail'], $data, function($message) use($user){
                $message->to(env('MAIL_USERNAME'),'PXS HRMS')->subject('Visitor Registration');
                $message->from(env('MAIL_USERNAME'),'PXS HRMS');
            });
            return response()->json(['success' => true,'message'=>'Registration Successful, Please wait for approval email'], 200);
        }else{
            return response()->json(['success' => false,'message'=>'Email has been used'], 200);
        }
    }

    public function addNewVisitTime(Request $request)
    {
        if($request->user_id <> null){
            $fromdatetime = Carbon::parse($request->from_date." ".$request->from_time);
            $todatetime = Carbon::parse($request->to_date." ".$request->to_time);
            if($fromdatetime >= $todatetime){
                return response()->json(['success' => false,'message'=>'Please key in proper date time from and date time to'], 200);    
            }else{
                $user = User::find($request->user_id);
                $request->merge(['status'=>"Wait for Approval"]);
                $visitor_pass= VisitorPass::create($request->all());
                //$user->update(['visitor_pass_id'=>$visitor_pass->id,'is_active'=>0]);
                //email or noticesomeone to approve
                $data = array('name'=>$user->name,'email'=>$user->email);
                Mail::send(['text'=>'email.visitor_pass_mail'], $data, function($message) use($user){
                    $message->to(env('MAIL_USERNAME'),'PXS HRMS')->subject('Visitor Pass Added');
                    $message->from(env('MAIL_USERNAME'),'PXS HRMS');
                });
                return response()->json(['success' => true,'message'=>'Visitor pass added'], 200);    
            }
        }else{
            return response()->json(['success' => false,'message'=>'User not found'], 200);
        }
    }

    public function getVisitorPass(Request $request)
    {
        $user = User::find($request->user_id);
        $visitor_pass = VisitorPass::where('user_id',$request->user_id)->orderBy('id','desc')->get();
        return response()->json($visitor_pass);
    }

    // public function sendData(){
    //     $scan_data = UserAttendance::all();
    //     $response = Http::post(env('APP_CLOUD_URL').'api/receivedData', [
    //         'scan_data' => $scan_data,
    //     ]);

        
    //     if($response['message'] == "success"){
    //         foreach($scan_data as $s){
    //             $s->delete();
    //         }
    //         if(count($response['users'])>0){
    //             foreach($response['users'] as $u){
    //                 $user = User::find($u['id']);
    //                 if(isset($user)){
    //                     $user->update($u);
    //                 }else{
    //                     DB::table('users')->insert($u);
    //                 }
    //             }
    //         }
    //         if(count($response['accesses'])>0){
    //             foreach($response['accesses'] as $p){
    //                 $access = Access::find($p['id']);
    //                 if(isset($access)){
    //                     $access->update($p);
    //                 }else{
    //                     DB::table('accesses')->insert($p);
    //                 }
    //             }
    //         }
    //         if(count($response['user_access_floors'])>0){
    //             foreach($response['user_access_floors'] as $a){
    //                 $user_access_floor = UserAccessFloor::find($a['id']);
    //                 if(isset($user_access_floor)){
    //                     $user_access_floor->update($a);
    //                 }else{
    //                     DB::table('user_access_floors')->insert($a);
    //                 }
    //             }
    //         }
    //     }
        
    //     $response_details=[
    //         'success' =>true,
    //         'message' =>"Sync Done!"
    //     ];
    //     return $response_details;

    // }

    public function receivedData(Request $request){
        
        $scan_data = $request->scan_data;
        if(count($scan_data)>0){
            foreach($scan_data as $s){
                UserAttendance::create($s);
            }
        }
        $users = DB::table('users')->where('sync',1)->get();
        $accesses = DB::table('accesses')->where('sync',1)->get();
        $user_access_floors = DB::table('user_access_floors')->where('sync',1)->get();
        $response = [
            'users' => $users,
            'accesses' => $accesses,
            'user_access_floors' => $user_access_floors,
            'message' => 'success',
        ];

        $users = DB::table('users')->where('sync',1)->update(['sync'=>0]);
        $accesses = DB::table('accesses')->where('sync',1)->update(['sync'=>0]);
        $user_access_floors = DB::table('user_access_floors')->where('sync',1)->update(['sync'=>0]);

        return $response;

    }

    public function getLeaveType(Request $request){
        // Validate the request to ensure the compulsory field (user_id) is present
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            // Return error response for failed validation
            $errors = $validator->errors();
            $errorString = implode(' ', $errors->all());
            return response()->json(['success' => false, 'message' => $errorString], 200);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            // Return error response if user is not found
            return response()->json(['success' => false, 'message' => 'User not found'], 200);
        }

        $leave_types = LeaveType::all();

        return response()->json($leave_types);
    }

    public function getLeaveTypeDetail(Request $request)
    {
        try {
            // Validate the request to ensure the compulsory field (leave_type_id) is present
            $validator = Validator::make($request->all(), [
                'leave_type_id' => 'required'
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $leave_type = LeaveType::find($request->leave_type_id);

            if (!$leave_type) {
                // Return error response if leave type is not found
                return response()->json(['success' => false, 'message' => 'Leave type not found'], 200);
            }

            return response()->json($leave_type);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeLeave(Request $request)
    {
        try {
    
            if (isset($request->leave_id) && $request->leave_id != null) {
                $leave = Leave::find($request->leave_id);

                if (!$leave) {
                    // Return error response if leave is not found
                    return response()->json(['success' => false, 'message' => 'Leave not found'], 200);
                } 
                // Validate the request data
                $validator = Validator::make($request->all(), [
                    'status' => 'sometimes|in:draft,submitted,cancelled',
                    'start_time' => 'nullable|required_with:end_time|date_format:H:i:s',
                    'end_time' => 'nullable|required_with:start_time|date_format:H:i:s|after:start_time',
                ]);

                if ($validator->fails()) {
                    // Return error response for failed validation
                    $errors = $validator->errors();
                    $errorString = implode(' ', $errors->all());
                    return response()->json(['success' => false, 'message' => $errorString], 200);
                } else {
                    
                    if ($leave->status != 'draft' && $request->status != 'cancelled') {
                        // Return error response if claim is already submitted
                        return response()->json(['success' => false, 'message' => 'Leave is already submitted'], 200);
                    } else {
                        if ($request->status !== 'cancelled' ) {
                            $duration =  (new LeaveController())->calculateDuration($leave);

                            // if leave type balance unit is hour and leave duration is less than 1 hour
                            if ($leave->leave_type->balance_unit == 'hour' && $duration < 1) {
                                // Return error response if leave duration is less than 1 hour
                                return response()->json(['success' => false, 'message' => 'Leave duration must be at least 1 hour'], 200);
                            }

                            $leave_balance = LeaveBalance::where('user_id', $leave->user_id)
                                            ->where('leave_type_id', $leave->leave_type_id)
                                            ->first();

                            $leave_type = LeaveType::find($leave->leave_type_id);

                            if ($leave_type->attachment_required) {
                                //check for existing attachment if no attachment validate attachment as required
                                if ($leave->attachments->isEmpty()) {
                                    $attachmentValidator = Validator::make($request->all(), [
                                        'attachment' => 'required',
                                    ]);

                                    if ($attachmentValidator->fails()) {
                                        // Return error response for failed validation
                                        $errors = $attachmentValidator->errors();
                                        $errorString = implode(' ', $errors->all());
                                        return response()->json(['success' => false, 'message' => $errorString], 200);
                                    }
                                }
                            }

                            if ($leave->start_date < now()->format('Y-m-d')){
                                if ($leave_type->back_dated){
                                    if ($leave->start_date < now()->subDays($leave_type->back_dated_days_limit)->format('Y-m-d')) {
                                        return response()->json(['success' => false, 'message' => 'Back dated leave exceeds limit to apply. Only ' . $leave_type->back_dated_days_limit . ' days from current day is allowed'], 200);
                                    }
                                } else {
                                    return response()->json(['success' => false, 'message' => 'Back dated leave is not allowed'], 200);
                                }
                            }

                            if($leave_type->limit_per_leave){
                                if($duration > $leave_type->limit_per_leave_amount){
                                    return response()->json(['success' => false, 'message' => 'Duration exceed limit per leave'], 200);
                                }
                            }

                            if($leave_balance->totalBalance() < $duration){
                                return response()->json(['success' => false, 'message' => 'Insufficient leave balance'], 200);
                            }
                        } elseif ($request->status == 'cancelled') {
                            // if status is cancelled, check date range is not in the past
                            if ($leave->start_date < now()->format('Y-m-d')){
                                return response()->json(['success' => false, 'message' => 'Leave is already started and cannot be cancelled'], 200);
                            }
                        }
                        $leave->update($request->all());
                    }
                }
            } else {
                // Validate the request data
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'leave_type_id' => 'required',
                    'start_date' => 'required',
                    'status' => 'required|in:draft,submitted',
                    'start_time' => 'nullable|required_with:end_time|date_format:H:i:s',
                    'end_time' => 'nullable|required_with:start_time|date_format:H:i:s|after:start_time',
                ]);

                if ($validator->fails()) {
                    // Return error response for failed validation
                    $errors = $validator->errors();
                    $errorString = implode(' ', $errors->all());

                    return response()->json(['success' => false, 'message' => $errorString], 200);
                } else {
                    $duration =  (new LeaveController())->calculateDurationRequest($request);
                    $leave_balance = LeaveBalance::where('user_id', $request->user_id)
                                    ->where('leave_type_id', $request->leave_type_id)
                                    ->first();

                    $leave_type = LeaveType::find($request->leave_type_id);

                    if ($leave_type->attachment_required) {
                        $attachmentValidator = Validator::make($request->all(), [
                            'attachment' => 'required',
                        ]);

                        if ($attachmentValidator->fails()) {
                            // Return error response for failed validation
                            $errors = $attachmentValidator->errors();
                            $errorString = implode(' ', $errors->all());
                            return response()->json(['success' => false, 'message' => $errorString], 200);
                        }
                    }

                    // if leave type balance unit is hour and leave duration is less than 1 hour
                    if ($leave_type->balance_unit == 'hour' && $duration < 1) {
                        // Return error response if leave duration is less than 1 hour
                        return response()->json(['success' => false, 'message' => 'Leave duration must be at least 1 hour'], 200);
                    }

                    if ($request->start_date < now()->format('Y-m-d')){
                        if ($leave_type->back_dated){
                            if ($request->start_date < now()->subDays($leave_type->back_dated_days_limit)->format('Y-m-d')) {
                                return response()->json(['success' => false, 'message' => 'Back dated leave exceeds limit to apply. Only ' . $leave_type->back_dated_days_limit . ' days from current day is allowed'], 200);
                            }
                        } else {
                            return response()->json(['success' => false, 'message' => 'Back dated leave is not allowed'], 200);
                        }
                    }

                    if($leave_type->limit_per_leave){
                        if($duration > $leave_type->limit_per_leave_amount){
                            return response()->json(['success' => false, 'message' => 'Duration exceed limit per leave'], 200);
                        }
                    }

                    if($leave_balance->totalBalance() < $duration){
                        return response()->json(['success' => false, 'message' => 'Insufficient leave balance'], 200);
                    }

                    $leave = Leave::create($request->all());
                }
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
                $balanceDeductedNew = (new LeaveController())->deductBalance($leave);
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
                    if (!$leaveApproversEmail->isEmpty()) {
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
                $balanceRestored = (new LeaveController())->restoreBalance($leave);
                // Email the reviewer and approver if the leave was cancelled and was in review or approved status
                if ($leave->review_status && $leave->reviewed_by) {
                    if ($leave->reviewer->email){
                        // Mail::to($leave->reviewer->email)->send(new LeaveCancel($leave));
                    }

                    if($leave->reviewer->firebase_token){
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
                    if ($leave->approver->email){
                        // Mail::to($leave->approver->email)->send(new LeaveCancel($leave));
                    }

                    if ($leave->approver->firebase_token){
                        SendFCMNotification::dispatch($leave->approver->firebase_token, [
                            'title' => 'Leave request cancelled',
                            'body' => $leave->user->name . ' has cancelled their leave request',
                            'path' => 'approval',
                            'type' => 'leave',
                            'id' => $leave->id,
                        ]);
                    }
                }
            
            }

            return response()->json(['success' => true, 'message' => 'Leave request '.$leave->status.' successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getLeave(Request $request)
    {
        try {
            // Validate the request to ensure the compulsory field (user_id) is present
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);

            if (!$user) {
                // Return error response if user is not found
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            $leaves = $user->leaves->sortByDesc('created_at');

            // Filter leaves by status if status parameter is present and not null
            if ($request->status) {
                $leaves = $leaves->where('status', $request->status);
            }

            $leaves = $leaves->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'user_id' => $leave->user_id,
                    'leave_type_id' => $leave->leave_type_id,
                    'leave_type_name' => $leave->leaveType->name,
                    'leave_type_label_color' => $leave->leaveType->label_color,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'start_time' => $leave->start_time,
                    'end_time' => $leave->end_time,
                    'status' => $leave->status,
                ];
            })->values(); // Add values() method to remove numeric keys

            return response()->json($leaves);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getLeaveDetail(Request $request)
    {
        try {
            // Validate the request to ensure the compulsory field (leave_id) is present
            $validator = Validator::make($request->all(), [
                'leave_id' => 'required'
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $leave = Leave::find($request->leave_id);

            if (!$leave) {
                // Return error response if leave is not found
                return response()->json(['success' => false, 'message' => 'Leave not found'], 200);
            }

            if ($leave->leaveType->balance_unit == 'hour') {
                $durationInt = (new LeaveController())->calculateDuration($leave);
                // if leave less than 8 hours display in hours and the start and end time
                if ($durationInt < 8) {
                    // check if day is Saturday
                    if (Carbon::parse($leave->start_date)->dayOfWeek == Carbon::SATURDAY) {
                        $duration = $durationInt . ' hour(s) (Saturday)';
                    } else {
                        $startTime = Carbon::createFromFormat('H:i:s', $leave->start_time)->format('g:i A');
                        $endTime = Carbon::createFromFormat('H:i:s', $leave->end_time)->format('g:i A');
                        $duration = $durationInt . ' hour(s) (' . $startTime . ' - ' . $endTime . ')';
                    }
                } else {
                    $duration_day = floor($durationInt / 8);
                    $duration_hour = $durationInt % 8;
                    if ($duration_hour > 0) {
                        $duration = $duration_day . ' day(s) ' . $duration_hour . ' hour(s)';
                    } else {
                        $duration = $duration_day . ' day(s)';
                    }
                }
            } else {
                $duration = (new LeaveController())->calculateDuration($leave) . ' ' . $leave->leaveType->balance_unit . '(s)';
            }

            $leave->user_name = $leave->user->name;
            $leave->leave_type_name = $leave->leaveType->name;
            $leave->duration = $duration ?? (new LeaveController())->calculateDuration($leave) . ' ' . $leave->leaveType->balance_unit . '(s)';
            $leave->attachments = $leave->attachments;
            // add url to attachments
            foreach($leave->attachments as $attachment){
                $attachment->content_type = mime_content_type(storage_path('app/attachments/'.$attachment->path));
                $attachment->url = route('attachment.show',$attachment->id);
            }

            if (!$leave) {
                // Return error response if leave is not found
                return response()->json(['success' => false, 'message' => 'Leave not found'], 200);
            }

            return response()->json($leave);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getLeaveBalance(Request $request)
    {
        try {
            // Validate the request to ensure the compulsory field (user_id) is present
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);

            if (!$user) {
                // Return error response if user is not found
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            // $leave_types = LeaveType::all();
            // foreach ($leave_types as $leave_type) {
            //     $leaveBalance = LeaveBalance::where('user_id', $user->id)->where('leave_type_id', $leave_type->id,)->first();
            //     if (!$leaveBalance) {
            //         if ($leaveType->confirmed_employees_only && !$user->confirmed_date) {
            //             $balance = 0;
            //         } else {
            //             $balance = $leaveType->default_amount;
            //         }
                    
            //         $leaveBalance = LeaveBalance::create([
            //             'user_id' => $user->id,
            //             'leave_type_id' => $leaveType->id,
            //             'balance' => $balance,
            //         ]);                    
            //     }
            // }
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

            $leave_balance = $user->leaveBalances->map(function ($leaveBalance) {
                // if balance unit is hour, display the balance in days and hours
                if ($leaveBalance->leaveType->balance_unit == 'hour') {
                    $balance_day = floor($leaveBalance->totalBalance() / 8);
                    if ($balance_day > 0) {
                        $balance_hour = $leaveBalance->totalBalance() % 8;
                        if ($balance_hour > 0) {
                            $balance_display = $balance_day . ' day(s) ' . $balance_hour . ' hour(s)';
                        } else {
                            $balance_display = $balance_day . ' day(s)';
                        }
                    } else {
                        $balance_display = $leaveBalance->totalBalance() % 8 . ' hour(s)';
                    }
                } else {
                    $balance_display = $leaveBalance->totalBalance() . ' ' . $leaveBalance->leaveType->balance_unit . '(s)';}

                return [
                    'id' => $leaveBalance->id,
                    'balance' => $leaveBalance->totalBalance(),
                    'balance_display' => $balance_display,
                    'leave_type_id' => $leaveBalance->leave_type_id,
                    'leave_type_name' => $leaveBalance->leaveType->name,
                    'leave_type_label_color' => $leaveBalance->leaveType->label_color,
                    'leave_type_balance_unit' => $leaveBalance->leaveType->balance_unit,
                ];
            });

            return response()->json($leave_balance);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStatus()
    {
        $statusList = [
            'draft',
            'submitted',
            'reviewed',
            'approved',
            'rejected',
            'cancelled'
        ];

        return response()->json($statusList);
    }

    public function getCalendar(Request $request)
    {
        try {
            $eventsJSON = [];
            if ($request->event && $request->event == 1)
            {
                $events = Event::with('event_type')->get();
                foreach ($events as $event) {
                    if (isset($event->date_to) && $event->date_to !== null) {
                        $dateFrom = new DateTime($event->date_from);
                        $dateTo = new DateTime($event->date_to);
                        // add one day to the end date
                        $dateTo->modify('+1 day');
                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($dateFrom, $interval, $dateTo);
                
                        foreach ($period as $date) {
                            $eventsJSON[] = [
                                'title' => $event->name,
                                'event_type' => str_replace(' ', '_', strtolower($event->event_type->name)),
                                'description' => $event->description,
                                'date' => $date->format('Y-m-d'),
                                'color' => $event->event_type->color,
                            ];
                        }
                    } else {
                        $eventsJSON[] = [
                            'title' => $event->name,
                            'event_type' => str_replace(' ', '_', strtolower($event->event_type->name)),
                            'description' => $event->description,
                            'date' => $event->date_from,
                            'color' => $event->event_type->color,
                        ];
                    }
                }
            }
            if ($request->leave && $request->leave == 1)
            {
                $leaves = Leave::where('status', 'approved')->with('leaveType')->get();
                foreach($leaves as $leave) {
                    if (isset($leave->end_date) && $leave->end_date !== null) {
                        $dateFrom = new DateTime($leave->start_date);
                        $dateTo = new DateTime($leave->end_date);
                        // add one day to the end date
                        $dateTo->modify('+1 day');
                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($dateFrom, $interval, $dateTo);
                
                        foreach ($period as $date) {
                            $eventsJSON[] = [
                                'title' => $leave->user->name . ' on ' . $leave->leaveType->name,
                                'event_type' => 'leave',
                                'description' => $leave->description,
                                'date' => $date->format('Y-m-d'),
                                'color' => $leave->leaveType->label_color,
                            ];
                        }
                    } else {
                        $eventsJSON[] = [
                            'title' => $leave->user->name . ' on ' . $leave->leaveType->name,
                            'event_type' => 'leave',
                            'description' => $leave->description,
                            'date' => $leave->start_date,
                            'color' => $leave->leaveType->label_color,
                        ];
                    }
                }
            }
            return response()->json($eventsJSON);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveLeaveIndex(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);

            // check wether user is leave approver
            if ($user->position && $user->position->leave_reviewer) {
                // get employee id under the same department but not the user
                $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))->pluck('id');

                // get leave request for the employees
                $leave = Leave::whereIn('user_id', $employees)->whereIn('status', ['submitted'])->orderByDesc('created_at')->get();
            } elseif ($user->position && $user->position->leave_approver) {
                // get employee id under the same department but not the user
                $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))->pluck('id');

                // get leave that has is not in draft or submit status
                $leave = Leave::whereIn('user_id', $employees)->whereIn('status', ['submitted','reviewed'])->orderByDesc('created_at')->get();
            } else {
                // get leave request for the user
                return response()->json(['success' => false, 'message' => 'User does not have permission to access this resource'], 200);
            }

            $leaveJSON = [];
            foreach ($leave as $l) {
                $leaveJSON[] = [
                    'id' => $l->id,
                    'user_id' => $l->user_id,
                    'user_name' => $l->user->name,
                    'leave_type_id' => $l->leave_type_id,
                    'leave_type_name' => $l->leaveType->name,
                    'leave_type_label_color' => $l->leaveType->label_color,
                    'start_date' => $l->start_date,
                    'end_date' => $l->end_date,
                    'start_time' => $l->start_time,
                    'end_time' => $l->end_time,
                    'duration' => (new LeaveController())->calculateDuration($l) . ' ' . $l->leaveType->balance_unit . '(s)',
                    'remarks' => $l->remarks,
                    'status' => $l->status,
                    'review_status' => $l->review_status,
                    'reviewed_by' => $l->reviewed_by,
                    'reviewed_at' => $l->reviewed_at,
                    'review_remark' => $l->review_remark,
                    'approval_status' => $l->approval_status,
                    'approved_by' => $l->approved_by,
                    'approved_at' => $l->approved_at,
                    'approval_remark' => $l->approval_remark,
                ];
            }                       

            return response()->json($leaveJSON, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveLeave(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'leave_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);
            $leave = Leave::findOrFail($request->leave_id);

            if ($user->position->leave_reviewer) {
                // Validate the input
                $validator = Validator::make($request->all(), [
                    'review_status' => 'required|in:0,1', // review_status must be 0 or 1
                ]);

                if ($validator->fails()) {
                    $errors = $validator->errors();
                    $errorString = implode(' ', $errors->all());
                    return response()->json(['success' => false, 'message' => $errorString], 200);
                }

                // Check if the leave is already reviewed
                if ($leave->status == 'reviewed') {
                    return response()->json(['success' => false, 'message' => 'Leave request has already been reviewed']);
                } else if ($leave->status == 'rejected') {
                    return response()->json(['success' => false, 'message' => 'Leave request has already been rejected']);
                } else if ($leave->status == 'cancelled') {
                    return response()->json(['success' => false, 'message' => 'Leave request has already been cancelled']);
                } else if ($leave->status == 'approved') {
                    return response()->json(['success' => false, 'message' => 'Leave request has already been approved']);
                }


                if ($request->review_status == '1') {
                    $status = 'reviewed';
                } elseif ($request->review_status == '0') {
                    $status = 'rejected';
                    $balanceRestored = (new LeaveController())->restoreBalance($leave);
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid review status'], 200);
                }

                $leave->update([
                    'review_status' => $request->review_status,
                    'review_remark' => $request->review_remark,
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                    'status' => $status
                ]);

                if($request->review_status == '1') {
                    $leaveApproversEmail = $leave->user->getLeaveApprovers()->whereNotNull('email')->pluck('email');
                    $leaveApproversFCMToken = $leave->user->getLeaveApprovers()->whereNotNull('firebase_token')->pluck('firebase_token')->toArray();

                    // Check if email recipients are not empty
                    if (!$leaveApproversEmail->isEmpty()) {
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

                    if ($leave->user->firebase_token){
                        SendFCMNotification::dispatch($leave->user->firebase_token, [
                            'title' => 'Leave request reviewed',
                            'body' => 'Your leave request has been reviewed by ' . $user->name,
                            'path' => 'leave',
                            'type' => 'leave',
                            'id' => $leave->id,
                        ]);
                    }
                } else {
                    if ($leave->user->firebase_token){
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
                $validator = Validator::make($request->all(), [
                    'approval_status' => 'required|in:0,1', // approve_status must be 0 or 1
                ]);

                if ($validator->fails()) {
                    $errors = $validator->errors();
                    $errorString = implode(' ', $errors->all());
                    return response()->json(['success' => false, 'message' => $errorString], 200);
                }

                // Check if the leave is already approved
                if ($leave->status == 'approved') {
                    return response()->json(['success' => false, 'message' => 'Leave request has already been approved']);
                } else if ($leave->status == 'rejected') {
                    return response()->json(['success' => false, 'message' => 'Leave request has already been rejected']);
                } else if ($leave->status == 'cancelled') {
                    return response()->json(['success' => false, 'message' => 'Leave request has already been cancelled']);
                }

                if ($request->approval_status == '1') {
                    $status = 'approved';
                } elseif ($request->approval_status == '0') {
                    $status = 'rejected';
                    $balanceRestored = (new LeaveController())->restoreBalance($leave);
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid approval status'], 200);
                }

                $leave->update([
                    'approval_status' => $request->approval_status,
                    'approval_remark' => $request->approval_remark,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'status' => $status
                ]);

                // Mail::to($leave->user->email)->send(new LeaveApprove($leave));

                if ($request->approval_status == '1') {
                    if ($leave->user->firebase_token){
                        SendFCMNotification::dispatch($leave->user->firebase_token, [
                            'title' => 'Leave request approved',
                            'body' => 'Your leave request has been approved by ' . $user->name,
                            'path' => 'leave',
                            'type' => 'leave',
                            'id' => $leave->id,
                        ]);
                    }
                } else {
                    if ($leave->user->firebase_token){
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

            return response()->json(['success' => true,'message' => 'Leave request '.$leave->status.' updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveLeaveHistory (Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);

            // check wether user is leave approver
            if ($user->position && $user->position->leave_reviewer) {
                // get employee id under the same department but not the user
                $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))->pluck('id');

                // get leave request for the employees
                $leave = Leave::whereIn('user_id', $employees)
                    ->whereNotIn('status', ['draft','submitted'])
                    ->orderByDesc('created_at')
                    ->get();
            } elseif ($user->position && $user->position->leave_approver) {
                // get employee id under the same department but not the user
                $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))->pluck('id');

                // get leave that has is not in draft or submit status
                $leave = Leave::whereIn('user_id', $employees)
                    ->whereNotIn('status', ['draft','submitted','reviewed'])
                    ->orderByDesc('created_at')
                    ->get();
            }else {
                // get leave request for the user
                return response()->json(['success' => false, 'message' => 'User does not have permission to access this resource'], 200);
            }

            $leaveJSON = [];
            foreach ($leave as $l) {
                $leaveJSON[] = [
                    'id' => $l->id,
                    'user_id' => $l->user_id,
                    'user_name' => $l->user->name,
                    'leave_type_id' => $l->leave_type_id,
                    'leave_type_name' => $l->leaveType->name,
                    'leave_type_label_color' => $l->leaveType->label_color,
                    'start_date' => $l->start_date,
                    'end_date' => $l->end_date,
                    'start_time' => $l->start_time,
                    'end_time' => $l->end_time,
                    'duration' => (new LeaveController())->calculateDuration($l) . ' ' . $l->leaveType->balance_unit . '(s)',
                    'remarks' => $l->remarks,
                    'status' => $l->status,
                    'review_status' => $l->review_status,
                    'reviewed_by' => $l->reviewed_by,
                    'reviewed_at' => $l->reviewed_at,
                    'review_remark' => $l->review_remark,
                    'approval_status' => $l->approval_status,
                    'approved_by' => $l->approved_by,
                    'approved_at' => $l->approved_at,
                    'approval_remark' => $l->approval_remark,
                ];
            }                       

            return response()->json($leaveJSON, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadProfilePic(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'profile_image' => 'required|image|max:3000',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            if ($request->hasFile('profile_image')) {
                $filename = $user->id . "/" . $request->profile_image->getClientOriginalName();
                $request->profile_image->storeAs('images', $filename, 'public');
                $user->update(['profile_image' => $filename]);
            }

            return response()->json(['success' => true,
                'message' => 'Profile picture uploaded successfully',
                'profile_image_url' => asset('/storage/images/'.$user->profile_image),
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function remoteWorkingStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'location' => 'required|in:HOME,CLIENT VISIT',
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            $currentDateTime = now();
            $currentDate = $currentDateTime->format('Y-m-d');

            $userAttendance = UserAttendance::where('user_id', $request->user_id)
                ->where('scan_date', $currentDate)
                ->where('access_id', null)
                ->where('location', $request->location)
                ->orderBy('scan_datetime', 'desc')
                ->first();

            if (!$userAttendance) {
                $message = "You have no record for today";
                $button_text = "Clock-In";
                $disable = false;
            } else {
                $lastScanStatus = $userAttendance->scan_status ?? null;

                $message = ($lastScanStatus == "clock-in") ? "You have Clocked In" : "You have Clocked Out";
                $button_text = ($lastScanStatus == "clock-in") ? "Clock-Out" : "Clock-In";
                $disable = false;
            }

            $success = true;

            $respon_return = [
                'success' => $success,
                'message' => $message ?? "",
                'button_text' => $button_text ?? "",
                'disable' => $disable ?? false,
            ];

            return response()->json($respon_return);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function remoteWorkingHistory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'location' => 'required|in:HOME,CLIENT VISIT',
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            $currentDate = Carbon::now()->format('Y-m-d');
            $userAttendance = UserAttendance::where('user_id', $request->user_id)
                ->where('scan_date', $currentDate)
                ->where('access_id', null)
                ->where('location', $request->location)
                ->orderBy('scan_datetime', 'desc')
                ->get();

            return response()->json($userAttendance);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getClaimType(Request $request){
        $claim_types = ClaimType::all();
    
        return response()->json($claim_types);
    }

    public function getClaimAmountType ()
    {
        $amountTypeList = [
            'MYR',
            'Days',
        ];

        return response()->json($amountTypeList);
    }
    
    public function getClaimTypeDetail(Request $request)
    {
        try {
            // Validate the request to ensure the compulsory field (claim_type_id) is present
            $validator = Validator::make($request->all(), [
                'claim_type_id' => 'required'
            ]);
    
            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }
    
            $claim_type = ClaimType::find($request->claim_type_id);
    
            if (!$claim_type) {
                // Return error response if claim type is not found
                return response()->json(['success' => false, 'message' => 'Claim type not found'], 200);
            }
    
            return response()->json($claim_type);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeClaim(Request $request)
    {
        try {
    
            if (isset($request->claim_id) && $request->claim_id != null) {
                $claim = Claim::find($request->claim_id);
                // Validate the request data
                if (!$claim) {
                    // Return error response if claim is not found
                    return response()->json(['success' => false, 'message' => 'Claim not found'], 200);
                } else {
                   
                    $validator = Validator::make($request->all(), [
                        'status' => 'sometimes|in:draft,submitted,cancelled',
                    ]);

                    if ($validator->fails()) {
                        // Return error response for failed validation
                        $errors = $validator->errors();
                        $errorString = implode(' ', $errors->all());
                        return response()->json(['success' => false, 'message' => $errorString], 200);
                    } else {
                        // check if claim_type_id is changed
                        if ($request->status != 'cancelled'){
                            if ($claim->claim_type_id != $request->claim_type_id) {
                                $claim_type = ClaimType::find($request->claim_type_id);
                                if ($claim_type->unit_price){
                                    $quantityValidate = Validator::make($request->all(), [
                                        'unit_quantity' => 'required',
                                    ]);

                                    if ($quantityValidate->fails()) {
                                        // Return error response for failed validation
                                        $errors = $quantityValidate->errors();
                                        $errorString = implode(' ', $errors->all());
                                        return response()->json(['success' => false, 'message' => $errorString], 200);
                                    } else {
                                        $amount = $request->unit_quantity * $claim_type->unit_price_value;
                                        $request->merge(['amount' => $amount]);
                                    }
                                }
                            }

                            if ($claim->attachments->isEmpty()) {
                                $attachmentValidator = Validator::make($request->all(), [
                                    'attachment' => 'required',
                                ]);
    
                                if ($attachmentValidator->fails()) {
                                    // Return error response for failed validation
                                    $errors = $attachmentValidator->errors();
                                    $errorString = implode(' ', $errors->all());
                                    return response()->json(['success' => false, 'message' => $errorString], 200);
                                }
                            }
                        }
                        
                        if ($claim->status != 'draft' && $request->status != 'cancelled') {
                            // Return error response if claim is already submitted
                            return response()->json(['success' => false, 'message' => 'Claim is already submitted'], 200);
                        }
                        
                        $claim->update($request->all());
                    }
                }
            } else {
                // Validate the request data
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'claim_type_id' => 'required',
                    'amount' => 'required',
                    'status' => 'required|in:draft,submitted',
                    'attachment' => 'required',
                ]);

                if ($validator->fails()) {
                    // Return error response for failed validation
                    $errors = $validator->errors();
                    $errorString = implode(' ', $errors->all());
                    return response()->json(['success' => false, 'message' => $errorString], 200);
                } else {
                    $claim_type = ClaimType::find($request->claim_type_id);
                    if ($claim_type->unit_price){
                        $quantityValidate = Validator::make($request->all(), [
                            'unit_quantity' => 'required',
                        ]);

                        if ($quantityValidate->fails()) {
                            // Return error response for failed validation
                            $errors = $quantityValidate->errors();
                            $errorString = implode(' ', $errors->all());
                            return response()->json(['success' => false, 'message' => $errorString], 200);
                        } else {
                            $amount = $request->unit_quantity * $claim_type->unit_price_value;
                            $request->merge(['amount' => $amount]);
                        }
                    }
                    $claim = Claim::create($request->all());
                }
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
                        SendFCMNotification::dispatch($claim->approver->firebase_token, [
                            'title' => 'Claim request cancelled',
                            'body' => $claim->user->name . ' has cancelled their claim request',
                            'path' => 'approval',
                            'type' => 'claim',
                            'id' => $claim->id,
                        ]);
                    }
                }
            

            }

            return response()->json(['success' => true, 'message' => 'Claim request '.$claim->status.' successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getClaim(Request $request)
    {
        try {
            // Validate the request to ensure the compulsory field (user_id) is present
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);

            if (!$user) {
                // Return error response if user is not found
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            $claims = $user->claims->sortByDesc('created_at');

            // Filter claims by status if status parameter is present and not null
            if ($request->status) {
                $claims = $claims->where('status', $request->status);
            }

            $claims = $claims->map(function ($claim) {
                return [
                    'id' => $claim->id,
                    'user_id' => $claim->user_id,
                    'claim_type_id' => $claim->claim_type_id,
                    'claim_type_name' => $claim->claimType->name,
                    'unit' => $claim->claimType->unit,
                    'unit_price' => $claim->claimType->unit_price,
                    'unit_quantity' => $claim->unit_quantity,
                    'unit_price_value' => $claim->claimType->unit_price_value,
                    'amount' => $claim->amount,
                    'status' => $claim->status,
                ];
            })->values(); // Add values() method to remove numeric keys

            return response()->json($claims);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getClaimDetail(Request $request)
    {
        try {
            // Validate the request to ensure the compulsory field (claim_id) is present
            $validator = Validator::make($request->all(), [
                'claim_id' => 'required'
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $claim = Claim::find($request->claim_id);
            $claim->user_name = $claim->user->name;
            $claim->claim_type_name = $claim->claimType->name;
            $claim->unit = $claim->claimType->unit;
            $claim->unit_price = $claim->claimType->unit_price;
            $claim->unit_price_value = $claim->claimType->unit_price_value;
            $claim->attachments = $claim->attachments;
            // add url to attachments
            foreach($claim->attachments as $attachment){
                $attachment->content_type = mime_content_type(storage_path('app/attachments/'.$attachment->path));
                $attachment->url = route('attachment.show',$attachment->id);
            }

            if (!$claim) {
                // Return error response if claim is not found
                return response()->json(['success' => false, 'message' => 'Claim not found'], 200);
            }

            return response()->json($claim);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveClaimIndex(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);

            // check wether user is claim approver
            if ($user->position && $user->position->leave_reviewer) {
                // get employee id under the same department but not the user
                $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))->pluck('id');

                // get claim request for the employees
                $claim = Claim::whereIn('user_id', $employees)->whereIn('status', ['submitted'])->orderByDesc('created_at')->get();
            } elseif ($user->position && $user->position->leave_approver) {
                // get employee id under the same department but not the user
                $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))->pluck('id');

                // get claim that has is not in draft or submit status
                $claim = Claim::whereIn('user_id', $employees)->whereIn('status', ['submitted', 'reviewed'])->orderByDesc('created_at')->get();
            } else {
                // get claim request for the user
                return response()->json(['success' => false, 'message' => 'User does not have permission to access this resource'], 200);
            }

            $claimJSON = [];
            foreach ($claim as $c) {
                $claimJSON[] = [
                    'id' => $c->id,
                    'user_id' => $c->user_id,
                    'user_name' => $c->user->name,
                    'claim_type_id' => $c->claim_type_id,
                    'claim_type_name' => $c->claimType->name,
                    'unit' => $c->claimType->unit,
                    'unit_price' => $c->claimType->unit_price,
                    'unit_quantity' => $c->unit_quantity,
                    'unit_price_value' => $c->claimType->unit_price_value,
                    'amount' => $c->amount,
                    'remarks' => $c->remarks,
                    'status' => $c->status,
                    'review_status' => $c->review_status,
                    'reviewed_by' => $c->reviewed_by,
                    'reviewed_at' => $c->reviewed_at,
                    'review_remark' => $c->review_remark,
                    'approval_status' => $c->approval_status,
                    'approved_by' => $c->approved_by,
                    'approved_at' => $c->approved_at,
                    'approval_remark' => $c->approval_remark,
                ];
            }                       

            return response()->json($claimJSON, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveClaim(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'claim_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);
            $claim = Claim::findOrFail($request->claim_id);

            if ($user->position->leave_reviewer) {
                // Validate the input
                $validator = Validator::make($request->all(), [
                    'review_status' => 'required|in:0,1', // review_status must be 0 or 1
                ]);

                if ($validator->fails()) {
                    $errors = $validator->errors();
                    $errorString = implode(' ', $errors->all());
                    return response()->json(['success' => false, 'message' => $errorString], 200);
                }

                // Check if the claim is already reviewed
                if ($claim->status == 'reviewed') {
                    return response()->json(['success' => false, 'message' => 'Claim request has already been reviewed']);
                } else if ($claim->status == 'rejected') {
                    return response()->json(['success' => false, 'message' => 'Claim request has already been rejected']);
                } else if ($claim->status == 'cancelled') {
                    return response()->json(['success' => false, 'message' => 'Claim request has already been cancelled']);
                } else if ($claim->status == 'approved') {
                    return response()->json(['success' => false, 'message' => 'Claim request has already been approved']);
                }


                if ($request->review_status == '1') {
                    $status = 'reviewed';
                } elseif ($request->review_status == '0') {
                    $status = 'rejected';
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid review status'], 200);
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
                $validator = Validator::make($request->all(), [
                    'approval_status' => 'required|in:0,1', // approve_status must be 0 or 1
                ]);

                if ($validator->fails()) {
                    $errors = $validator->errors();
                    $errorString = implode(' ', $errors->all());
                    return response()->json(['success' => false, 'message' => $errorString], 200);
                }

                // Check if the claim is already approved
                if ($claim->status == 'approved') {
                    return response()->json(['success' => false, 'message' => 'Claim request has already been approved']);
                } else if ($claim->status == 'rejected') {
                    return response()->json(['success' => false, 'message' => 'Claim request has already been rejected']);
                } else if ($claim->status == 'cancelled') {
                    return response()->json(['success' => false, 'message' => 'Claim request has already been cancelled']);
                }

                if ($request->approval_status == '1') {
                    $status = 'approved';
                } elseif ($request->approval_status == '0') {
                    $status = 'rejected';
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid approval status'], 200);
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

            return response()->json(['success' => true,'message' => 'Claim request '.$claim->status.' successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveClaimHistory (Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);

            // check wether user is claim approver
            if ($user->position && $user->position->leave_reviewer) {
                $claim = Claim::where('reviewed_by', $user->id)->whereNotIn('status', ['draft','submitted'])->orderByDesc('created_at')->get();
            } elseif ($user->position && $user->position->leave_approver) {
                $claim = Claim::where('approved_by', $user->id)->whereNotIn('status', ['draft','submitted','reviewed'])->orderByDesc('created_at')->get();
            } else {
                // get claim request for the user
                return response()->json(['success' => false, 'message' => 'User does not have permission to access this resource'], 200);
            }

            $claimJSON = [];
            foreach ($claim as $c) {
                $claimJSON[] = [
                    'id' => $c->id,
                    'user_id' => $c->user_id,
                    'user_name' => $c->user->name,
                    'claim_type_id' => $c->claim_type_id,
                    'claim_type_name' => $c->claimType->name,
                    'unit' => $c->claimType->unit,
                    'unit_price' => $c->claimType->unit_price,
                    'unit_quantity' => $c->unit_quantity,
                    'unit_price_value' => $c->claimType->unit_price_value,
                    'amount' => $c->amount,
                    'remarks' => $c->remarks,
                    'status' => $c->status,
                    'review_status' => $c->review_status,
                    'reviewed_by' => $c->reviewed_by,
                    'reviewed_at' => $c->reviewed_at,
                    'review_remark' => $c->review_remark,
                    'approval_status' => $c->approval_status,
                    'approved_by' => $c->approved_by,
                    'approved_at' => $c->approved_at,
                    'approval_remark' => $c->approval_remark,
                ];
            }                       

            return response()->json($claimJSON, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function showAttachment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'attachment_id' => 'required',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $attachment = Attachment::find($request->attachment_id);
            if (!$attachment) {
                return response()->json(['success' => false, 'message' => 'Attachment not found'], 200);
            }

            $filePath = storage_path('app/attachments/'.$attachment->path);
            $filename = $attachment->filename;

            // Check if the file exists
            if (!file_exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'File not found'], 200);
            }

            // Create a streamed response
            $response = new StreamedResponse(function () use ($filePath) {
                $stream = fopen($filePath, 'rb');
                fpassthru($stream);
                fclose($stream);
            });

            // Set the appropriate headers for streaming
            $response->headers->set('Content-Type', mime_content_type($filePath));
            $response->headers->set('Content-Disposition', 'inline; filename="'.$filename.'"');

            return $response;
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function destroyAttachment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'attachment_id' => 'required',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $attachment = Attachment::find($request->attachment_id);
            if (!$attachment) {
                return response()->json(['success' => false, 'message' => 'Attachment not found'], 200);
            }

            $attachment->delete();
            
            return response()->json(['success' => true, 'message' => 'Attachment deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function storeOvertime(Request $request)
    {
        try {
            if (isset($request->overtime_id) && $request->overtime_id != null) {
                $overtime = Overtime::find($request->overtime_id);
                if (!$overtime){
                    return response()->json(['success' => false, 'message' => 'Overtime not found'], 200);
                } else {
                    $validator = Validator::make($request->all(), [
                        'status' => 'sometimes|in:draft,requested,submitted,cancelled',
                    ]);
    
                    if ($validator->fails()) {
                        // Return error response for failed validation
                        $errors = $validator->errors();
                        $errorString = implode(' ', $errors->all());
                        return response()->json(['success' => false, 'message' => $errorString], 200);
                    } else {
                        if ($request->status != 'cancelled'){
                            if( $overtime->status != 'draft' && $overtime->status != 'pre_reviewed'){
                                return response()->json(['success' => false, 'message' => 'Overtime is already '. str_replace('_', '-', $overtime->status)], 200);
                            }    
                        }

                        if ($request->status == 'submitted'){
                            $validator = Validator::make($request->all(), [
                                'actual_time_start' => 'required',
                                'actual_time_end' => 'required|after:actual_time_start',
                            ]);

                            if ($validator->fails()) {
                                // Return error response for failed validation
                                $errors = $validator->errors();
                                $errorString = implode(' ', $errors->all());
                                return response()->json(['success' => false, 'message' => $errorString], 200);
                            } else {
                                $actual_time_start = strtotime($request->actual_time_start);
                                $actual_time_end = strtotime($request->actual_time_end);
                                $actual_time_taken = ($actual_time_end - $actual_time_start) / 3600;
                                $request->merge(['actual_time_taken' => $actual_time_taken]);
                            }
                        }

                        $overtime->update($request->all());
                    }
                }
            } else {
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'date' => 'required',
                    'reasons' => 'required',
                    'estimated_time_taken' => 'required',
                    'status' => 'required|in:draft,requested,submitted',
                ]);

                if ($validator->fails()) {
                    // Return error response for failed validation
                    $errors = $validator->errors();
                    $errorString = implode(' ', $errors->all());
                    return response()->json(['success' => false, 'message' => $errorString], 200);
                } else {
                    $overtime = Overtime::create($request->all());
                }

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

            return response()->json(['success' => true, 'message' => 'Overtime '. $overtime->status .' successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getOvertime (Request $request){
        try {
            // Validate the request to ensure the compulsory field (user_id) is present
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);

            if (!$user) {
                // Return error response if user is not found
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            $overtimes = $user->overtimes->sortByDesc('created_at');

            // Filter overtimes by status if status parameter is present and not null
            if ($request->status) {
                $overtimes = $overtimes->where('status', $request->status);
            }

            return response()->json($overtimes->values());
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getOvertimeDetail(Request $request)
    {
        try {
            // Validate the request to ensure the compulsory field (overtime_id) is present
            $validator = Validator::make($request->all(), [
                'overtime_id' => 'required'
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $overtime = Overtime::find($request->overtime_id);
            $overtime->user_name = $overtime->user->name;
            $overtime->attachments = $overtime->attachments;
            // add url to attachments
            foreach($overtime->attachments as $attachment){
                $attachment->content_type = mime_content_type(storage_path('app/attachments/'.$attachment->path));
                $attachment->url = route('attachment.show',$attachment->id);
            }

            if (!$overtime) {
                // Return error response if overtime is not found
                return response()->json(['success' => false, 'message' => 'Overtime not found'], 200);
            }

            return response()->json($overtime);
        } catch (\Exception $e) {
            // Return error response for other exceptions
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveOvertimeIndex(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);

            // check wether user is overtime approver and overtime reviewer
            if ($user->position && $user->position->leave_reviewer && $user->position->leave_approver) {
                $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))
                                ->orWhereJsonContains('leave_approvers', strval($user->position_id))
                                ->pluck('id');

                // get overtime request for the employees
                $overtime = Overtime::whereIn('user_id', $employees)->whereIn('status', ['requested', 'pre_reviewed', 'submitted', 'reviewed'])->orderByDesc('created_at')->get();

            } elseif ($user->position && $user->position->leave_reviewer) {
                $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))->pluck('id');

                // get overtime request for the employees
                $overtime = Overtime::whereIn('user_id', $employees)->whereIn('status', ['requested', 'pre_reviewed', 'submitted'])->orderByDesc('created_at')->get();
            } elseif ($user->position && $user->position->leave_approver) {
                $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))->pluck('id');
                
                // get overtime that has is not in draft or submit status
                $overtime = Overtime::whereIn('user_id', $employees)->whereIn('status', ['requested', 'pre_reviewed', 'submitted', 'reviewed'])->orderByDesc('created_at')->get();
            } else {
                // get overtime request for the user
                return response()->json(['success' => false, 'message' => 'User does not have permission to access this resource'], 200);
            }

            foreach ($overtime as $o) {
                $o->user_name = $o->user->name;
            }

            return response()->json($overtime, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveOvertimeHistory(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);

            // check wether user is overtime approver and overtime reviewer
            if ($user->position && $user->position->leave_reviewer && $user->position->leave_approver) {
                $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))
                                ->orWhereJsonContains('leave_approvers', strval($user->position_id))
                                ->pluck('id');

                // get overtime request for the employees
                $overtime = Overtime::whereIn('user_id', $employees)->whereNotIn('status', ['draft','requested', 'pre_reviewed','submitted','reviewed'])->orderByDesc('created_at')->get();

            } elseif ($user->position && $user->position->leave_reviewer) {
                $employees = User::whereJsonContains('leave_reviewers', strval($user->position_id))->pluck('id');

                // get overtime request for the employees
                $overtime = Overtime::whereIn('user_id', $employees)->whereNotIn('status', ['draft','requested', 'pre_reviewed','submitted'])->orderByDesc('created_at')->get();
            } elseif ($user->position && $user->position->leave_approver) {
                $employees = User::whereJsonContains('leave_approvers', strval($user->position_id))->pluck('id');
                
                // get overtime that has is not in draft or submit status
                $overtime = Overtime::whereIn('user_id', $employees)->whereNotIn('status', ['draft','requested', 'pre_reviewed','submitted','reviewed'])->orderByDesc('created_at')->get();
            } else {
                // get overtime request for the user
                return response()->json(['success' => false, 'message' => 'User does not have permission to access this resource'], 200);
            }

            foreach ($overtime as $o) {
                $o->user_name = $o->user->name;
            }

            return response()->json($overtime, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveOvertime(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'overtime_id' => 'required',
                'pre_review_status' => 'required_without_all:review_status,approval_status|in:0,1',
                'review_status' => 'required_without_all:pre_review_status,approval_status|in:0,1',
                'approval_status' => 'required_without_all:pre_review_status,review_status|in:0,1',
                'actual_time_approved' => 'required_if:review_status,1',
                'claim_as' => 'required_if:review_status,1|in:cash,replacement_leave',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);
            $overtime = Overtime::findOrFail($request->overtime_id);

            if ($request->has('pre_review_status')) {
                if ($user->position->leave_reviewer || $user->position->leave_approver){
                    // Check if the overtime is already reviewed and redirect back if it is with an error message
                    if ($overtime->status != 'requested') {
                        return response()->json(['success' => false, 'message' => 'Overtime is already been ' . str_replace('_', '-', $overtime->status)], 200);
                    }
    
                    if ($request->pre_review_status == '1') {
                        $status = 'pre_reviewed';
                    } elseif ($request->pre_review_status == '0') {
                        $status = 'rejected';
                    } else {
                        return response()->json(['success' => false, 'message' => 'Invalid status'], 200);
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
                            'title' => 'Overtime request ' . $overtime->status,
                            'body' => 'Your overtime request has been '.str_replace('_', '-', $overtime->status).' by ' . $user->name,
                            'path' => 'overtime',
                            'type' => 'overtime',
                            'id' => $overtime->id,
                        ]);
                    }
                } else {
                    return response()->json(['success' => false, 'message' => 'You are not authorized to perform this action'], 200);
                }
            } elseif ($request->has('review_status')) {
                if ($user->position->leave_reviewer){
                    // Check if the overtime is already reviewed and redirect back if it is with an error message
                    if ($overtime->status != 'submitted') {
                        return response()->json(['success' => false, 'message' => 'Overtime is already been ' . str_replace('_', '-', $overtime->status)], 200);
                    }
    
                    if ($request->review_status == '1') {
                        $status = 'reviewed';
                    } elseif ($request->review_status == '0') {
                        $status = 'rejected';
                    } else {
                        return response()->json(['success' => false, 'message' => 'Invalid status'], 200);
                    }
    
                    $overtime->update([
                        'review_status' => $request->review_status,
                        'review_remark' => $request->review_remark,
                        'reviewed_by' => $user->id,
                        'reviewed_at' => now(),
                        'status' => $status,
                        'actual_time_approved' => $request->actual_time_approved,
                        'actual_time_approved_updated_by' => $user->id,
                        'claim_as' => $request->claim_as,
                        'claim_as_updated_by' => $user->id
                    ]);
    
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
                                'title' => 'Overtime submission needs approval',
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
                    return response()->json(['success' => false, 'message' => 'You are not authorized to perform this action'], 200);
                }
            } elseif ($request->has('approval_status')) {
                if ($user->position->leave_approver){

                    // check if overtime actual_time_approved is null and make validation if null
                    if($overtime->actual_time_approved == null){
                        $validator = Validator::make($request->all(), [
                            'actual_time_approved' => 'required_if:approval_status,1',
                        ]);
        
                        if ($validator->fails()) {
                            $errors = $validator->errors();
                            $errorString = implode(' ', $errors->all());
                            return response()->json(['success' => false, 'message' => $errorString], 200);
                        }
                    }

                    if ($overtime->claim_as == null){
                        $validator = Validator::make($request->all(), [
                            'claim_as' => 'required_if:approval_status,1|in:cash,replacement_leave',
                        ]);
        
                        if ($validator->fails()) {
                            $errors = $validator->errors();
                            $errorString = implode(' ', $errors->all());
                            return response()->json(['success' => false, 'message' => $errorString], 200);
                        }
                    }

                    // Check if the overtime is already approved and redirect back if it is with an error message
                    if ($overtime->status != 'submitted' && $overtime->status != 'reviewed') {
                        return response()->json(['success' => false, 'message' => 'Overtime is already been ' . str_replace('_', '-', $overtime->status)], 200);
                    }
    
                    if ($request->approval_status == '1') {
                        $status = 'approved';
                    } elseif ($request->approval_status == '0') {
                        $status = 'rejected';
                    } else {
                        return response()->json(['success' => false, 'message' => 'Invalid status'], 200);
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
                    return response()->json(['success' => false, 'message' => 'You are not authorized to perform this action'], 200);
                }
            }

            return response()->json(['success' => true,'message' => 'Overtime request '.str_replace('_', '-', $overtime->status).' successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getHandBookCategory(Request $request){
        $handbook_category = HandBookCategory::all();
    
        return response()->json($handbook_category);
    }

    public function storePurchaseRequisition(Request $request) {
        try {
            // return $request->all();
            if (isset($request->purchase_requisition_id) && $request->purchase_requisition_id != null) {
                $purchaseRequisition = PurchaseRequisition::find($request->purchase_requisition_id);
                if (!$purchaseRequisition){
                    return response()->json(['success' => false, 'message' => 'Purchase Requisition not found'], 200);
                } else {
                    $validator = Validator::make($request->all(), [
                        'status' => 'sometimes|in:draft,submitted,cancelled',
                    ]);
    
                    if ($validator->fails()) {
                        // Return error response for failed validation
                        $errors = $validator->errors();
                        $errorString = implode(' ', $errors->all());
                        return response()->json(['success' => false, 'message' => $errorString], 200);
                    } else {
                        if ($request->status != 'cancelled'){
                            if( $purchaseRequisition->status != 'draft'){
                                return response()->json(['success' => false, 'message' => 'Purchase Requisition is already '. str_replace('_', '-', $overtime->status)], 200);
                            }    
                        }

                        $purchaseRequisition->update($request->all());
                    }
                }
            } else {
                $validator = Validator::make($request->all(), [
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

                if ($validator->fails()) {
                    // Return error response for failed validation
                    $errors = $validator->errors();
                    $errorString = implode(' ', $errors->all());
                    return response()->json(['success' => false, 'message' => $errorString], 200);
                } else {
                    $purchaseRequisition = PurchaseRequisition::create($request->all());
                }

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

                    // check if item has unit_price
                    if(isset($item['unit_price'])){
                        $item['total_price'] = round($item['quantity'] * $item['unit_price'], 2);
                    } else {
                        $item['total_price'] = round(0, 2);
                    }
            
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

            return response()->json(['success' => true, 'message' => 'Purchase Requisition '. $purchaseRequisition->status .' successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPurchaseRequisition(Request $request) {
        try {
            // Validate the request to ensure the compulsory field (user_id) is present
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                // Return error response for failed validation
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::find($request->user_id);

            if (!$user) {
                // Return error response if user is not found
                return response()->json(['success' => false, 'message' => 'User not found'], 200);
            }

            $purchaseRequisition = $user->purchaseRequisition->sortByDesc('created_at');

            // Filter purchaseRequisition by status if status parameter is present and not null
            if ($request->status) {
                $purchaseRequisition = $purchaseRequisition->where('status', $request->status);
            }

            return response()->json($purchaseRequisition->values());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPurchaseRequisitionDetail(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'purchase_requisition_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $purchaseRequisition = PurchaseRequisition::with(['purchaseRequisitionItems', 'attachments'])->find($request->purchase_requisition_id);

            if (!$purchaseRequisition) {
                // Return error response if purchaseRequisition is not found
                return response()->json(['success' => false, 'message' => 'Purchase Requisition not found'], 200);
            }

            $purchaseRequisition->user_name = $purchaseRequisition->user->name;

            foreach($purchaseRequisition->attachments as $attachment){
                $attachment->content_type = mime_content_type(storage_path('app/attachments/'.$attachment->path));
                $attachment->url = route('attachment.show',$attachment->id);
            }

            return response()->json($purchaseRequisition);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approvePurchaseRequisition(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'purchase_requisition_id' => 'required',
                'approval_status' => 'required|in:0,1', // approve_status must be 0 or 1
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);
            $purchaseRequisition = PurchaseRequisition::findOrFail($request->purchase_requisition_id);

            if ($user->isAn('superadmin', 'management')) {
                // Check if the purchaseRequisition is already approved
                if ($purchaseRequisition->status == 'approved') {
                    return response()->json(['success' => false, 'message' => 'Purchase Requisition is already approved']);
                } else if ($purchaseRequisition->status == 'rejected') {
                    return response()->json(['success' => false, 'message' => 'Purchase Requisition is already rejected']);
                } else if ($purchaseRequisition->status == 'cancelled') {
                    return response()->json(['success' => false, 'message' => 'Purchase Requisition is already cancelled']);
                }

                if ($request->approval_status == '1') {
                    $status = 'approved';
                } elseif ($request->approval_status == '0') {
                    $status = 'rejected';
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid approval status'], 200);
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
            } else {
                return response()->json(['success' => false, 'message' => 'You are not authorized to perform this action'], 200);
            }

            return response()->json(['success' => true,'message' => 'Purchase Requisition '.$purchaseRequisition->status.' successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approvePurchaseRequisitionIndex(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);

            if (!$user->isAn('superadmin', 'management')) {
                return response()->json(['success' => false, 'message' => 'You are not authorized to perform this action'], 200);
            }

            $purchaseRequisition = PurchaseRequisition::whereIn('status', ['submitted'])->orderByDesc('created_at')->get();

            foreach ($purchaseRequisition as $pr) {
                $pr->user_name = $pr->user->name;
            }

            return response()->json($purchaseRequisition, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approvePurchaseRequisitionHistory(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            $user = User::findOrFail($request->user_id);

            if (!$user->isAn('superadmin', 'management')) {
                return response()->json(['success' => false, 'message' => 'You are not authorized to perform this action'], 200);
            }

            $purchaseRequisition = PurchaseRequisition::whereNotIn('status', ['draft', 'submitted'])->orderByDesc('created_at')->get();

            foreach ($purchaseRequisition as $pr) {
                $pr->user_name = $pr->user->name;
            }

            return response()->json($purchaseRequisition, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function template(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'template' => 'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorString = implode(' ', $errors->all());
                return response()->json(['success' => false, 'message' => $errorString], 200);
            }

            return response()->json($request, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

