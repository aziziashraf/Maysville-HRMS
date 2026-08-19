<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Department;
use App\Models\Access;
use App\Models\UserAccessFloor;
use App\Models\LiftAccess;
use App\Models\UserLiftAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceList;
use App\Models\Position;
use Bouncer;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        //dd($user->can('show-own-department-only'));
		if (!empty($filter)) {
            if($user->can('show-own-department-only')){
                $department = Department::where('department_name', 'like', '%'.$filter.'%')->where('id',$user->department_id)->pluck('id')->all();
            }else{
                $department = Department::where('department_name', 'like', '%'.$filter.'%')->pluck('id')->all();
            }
            //dd($department);
            $employee = User::where('role','employee')
            ->where(function($query) use($filter,$department) {
                $query->where('name', 'like', '%'.$filter.'%')
                      ->orWhere('staff_id', 'like', '%'.$filter.'%')
                      ->orWhere('card_id', 'like', '%'.$filter.'%')
                      ->orWhere('email', 'like', '%'.$filter.'%')
                      ->orWhere('contact_no', 'like', '%'.$filter.'%')
                      ->orWhereIn('department_id', $department);
            })->get();
        }else{
            if($user->can('show-own-department-only')){
                $employee = User::where('role','employee')->where('department_id',$user->department_id)->get();
            }else{
                $employee = User::where('role','employee')->get();
            }
        }
        return view('employee.index')->with('employee',$employee);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $employee = [];
        $department = Department::all();
        // $leave_type = LeaveType::all();
        $access = Access::groupBy('access_name')->select('access_name')->get();
        $lift_access = LiftAccess::all();

        $activeUsersPostionsArray = User::where('is_active', true)->whereNotNull('position_id')->pluck('position_id')->unique()->values()->toArray();

        // Retrieve positions that have leave_reviewer set to true
        $leaveReviewers = Position::where('leave_reviewer', true)->whereIn('id', $activeUsersPostionsArray)->get();
        // Retrieve positions that have leave_approver set to true
        $leaveApprovers = Position::where('leave_approver', true)->whereIn('id', $activeUsersPostionsArray)->get();

        return view('employee.create')->with([
            'employee' => $employee,
            'department' => $department,
            'access' => $access,
            'lift_access' => $lift_access,
            // 'leave_type' => $leave_type,
            'leaveReviewers' => $leaveReviewers,
            'leaveApprovers' => $leaveApprovers,
        ]);
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (isset($request->leave_balance) && $request->leave_balance !== null) {
            $leave_balances = $request->input('leave_balance');
            foreach ($leave_balances as $key => $value) {
                if ($value === null) {
                    $leave_balances[$key] = "0";
                }
            }
            $request->merge(['leave_balance' => $leave_balances]);
        }

        if($request->employee_id >0){
            if($request->status == 1){
                $is_active = 1;
            }else{
                $is_active = 0;
            }
            $request->merge(['is_active'=>$is_active]);
            $employee= User::find($request->employee_id);

            // if $employee->getLeaveReviewers() is not empty but $request->leave_reviewers is empty, then remove all leave reviewers
            if ($employee->getLeaveReviewers()->isNotEmpty() && empty($request->leave_reviewers)) {
                $request->request->add(['leave_reviewers' => []]);
            }

            // if $employee->getLeaveApprovers() is not empty but $request->leave_approvers is empty, then remove all leave approvers
            if ($employee->getLeaveApprovers()->isNotEmpty() && empty($request->leave_approvers)) {
                $request->request->add(['leave_approvers' => []]);
            }

            $employee->update($request->all());
        }else{
            
            $validator = Validator::make($request->all(), [
                'email'=>'required|email|unique:users,email,NULL,id,deleted_at,NULL'
            ]);
            if ($validator->fails()) {
                return redirect()->back()
                            ->withErrors($validator);
            }
            if($request->status == 1){
                $is_active = 1;
            }else{
                $is_active = 0;
            }
            $request->merge(['password' => Hash::make('123456789'),'role'=>'employee','is_active'=>$is_active]);
            $employee = User::create($request->all());
            $this->sendEmailForUser($employee);
        }
        if(!empty($request->profile_image)){
            $filename = $employee->id."/".$request->profile_image->getClientOriginalName();
            $request->profile_image->storeAs('images',$filename,'public');
            $employee->update(['profile_image'=>$filename]);
         // $employee->update(['profile_image' => $this->upload($request->file_upload, 'employee', $employee->id)]);
        }
        $employee->accessFloor()->delete();
        if(isset($request->access) && count($request->access)>0){
            foreach($request->access as $access){
                $findaccess = Access::where('access_name',$access)->get();
                foreach($findaccess as $a){
                    $request->merge(['access_id'=>$a->access_id,'role'=>$employee->role]);
                    $employee->accessFloor()->create($request->all());
                }
            }
        }

        $employee->LiftaccessFloor()->delete();
        if(isset($request->lift_access) && count($request->lift_access)>0){
            foreach($request->lift_access as $lift_access){
                $request->merge(['lift_access_id'=>$lift_access,'role'=>$employee->role]);
		        $employee->LiftaccessFloor()->create($request->all());
            }
        }

        $leaveTypes = LeaveType::all();

        foreach ($leaveTypes as $leaveType) {
            $leaveBalance = LeaveBalance::firstOrCreate([
                'user_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
            ]);

            if ($leaveBalance->leaveBalanceLists->isEmpty()) {
                if ($leaveType->confirmed_employees_only && !$employee->confirmed_date) {
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

        if($employee->position){
            $roles = array($employee->position->role->id);
            // Re-syncing a employee's system roles
            Bouncer::sync($employee)->roles($roles);
        }
        
        return redirect()->route('employee.index');
    }

    public function edit(User $user)
    {
        $department = Department::all();
        $access = Access::groupBy('access_name')->select('access_name')->get();
        $lift_access = LiftAccess::all();
        $user->access_floor=$user->accessFloor->pluck('access_id')->all();
        // $leave_type = LeaveType::all();

        $activeUsersPostionsArray = User::where('is_active', true)->whereNotNull('position_id')->pluck('position_id')->unique()->values()->toArray();

        // Retrieve positions that have leave_reviewer set to true
        $leaveReviewers = Position::where('leave_reviewer', true)->whereIn('id', $activeUsersPostionsArray)->get();
        // Retrieve positions that have leave_approver set to true
        $leaveApprovers = Position::where('leave_approver', true)->whereIn('id', $activeUsersPostionsArray)->get();

        $findaccessByUser = Access::whereIn('id',$user->access_floor)->groupBy('access_name')->select('access_name')->get();
        $user_access =array();
        foreach($findaccessByUser as $a){
            array_push($user_access,$a->access_name);
        }
        $user->all_user_access =$user_access;
        $user->lift_access_floor=$user->LiftaccessFloor->pluck('lift_access_id')->all();
        return view('employee.create')->with([
            'employee' => $user,
            'department' => $department,
            'access' => $access,
            'lift_access' => $lift_access,
            // 'leave_type' => $leave_type,
            'leaveReviewers' => $leaveReviewers,
            'leaveApprovers' => $leaveApprovers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->update(['deleted_by'=>Auth::user()->id]);
        $user->delete();
        return redirect()->back();
    }

    public function reset_password(User $user)
    {
        $user->update(['password'=>Hash::make('123456789')]);
        return redirect()->back()->withSuccess("Password Reset Done.");
    }
}
