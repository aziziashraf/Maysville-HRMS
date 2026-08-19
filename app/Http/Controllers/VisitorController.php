<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Access;
use App\Models\UserAccessFloor;
use App\Models\LiftAccess;
use App\Models\UserLiftAccess;
use App\Models\VisitorPass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mail; 

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');
        
		// if (!empty($filter)) {
        //     $visitor = VisitorPass::whereHas('user', function($q) use($filter){
        //         $q->where('name', 'like', '%'.$filter.'%')
        //         ->orWhere('nric', 'like', '%'.$filter.'%')
        //         ->orWhere('card_id', 'like', '%'.$filter.'%')
        //         ->orWhere('email', 'like', '%'.$filter.'%')
        //         ->orWhere('contact_no', 'like', '%'.$filter.'%');
        //     })->get();
        // }else{
        //     $visitor = VisitorPass::all();
        // }
        // $visitor->appends(['filter' => $filter]);
       
        $visitor = User::where('role','visitor');
		if (!empty($filter)) {
            $visitor = $visitor->where(function($query) use($filter) {
                $query->where('name', 'like', '%'.$filter.'%')
                      ->orWhere('nric', 'like', '%'.$filter.'%')
                      ->orWhere('card_id', 'like', '%'.$filter.'%')
                      ->orWhere('email', 'like', '%'.$filter.'%')
                      ->orWhere('contact_no', 'like', '%'.$filter.'%');
            });
        }

        $visitor = $visitor->get();
        // $visitor->appends(['filter' => $filter]);

        return view('visitor.index')->with('visitor',$visitor)->with('filter', $filter);
    }

    public function create()
    {
        $visitor = [];
        $access = Access::groupBy('access_name')->select('access_name')->get();
        $lift_access = LiftAccess::all();
        return view('visitor.create')->with('visitor',$visitor)->with('access',$access)->with('lift_access',$lift_access);
    }

    public function store(Request $request)
    {
        $is_active = $request->status;
        if($request->visitor_id >0){
            $request->merge(['is_active'=>$is_active]);
            $visitor= User::find($request->visitor_id);
            $oldstatus = $visitor->is_active;
            if($oldstatus == 2 && $is_active <> 1){
                $request->merge(['is_active'=>$oldstatus]);
            }
            $visitor->update($request->all());
            
            if($visitor->email_verified_at == null && $visitor->is_active == 1){
                $data = array('name'=>$visitor->name,'email'=>$visitor->email);
                if($oldstatus == 2){
                    Mail::send(['text'=>'email.visitor_reg_done_mail'], $data, function($message) use($visitor){
                        $message->to($visitor->email, $visitor->name)->subject('Registration Approved');
                        $message->from(env('MAIL_USERNAME'),'PXS HRMS');
                    });
                    $visitor->update(['email_verified_at'=>Carbon::now()]);
                }else{
                    Mail::send(['text'=>'email.visitor_reg_done_by_admin_mail'], $data, function($message) use($visitor){
                        $message->to($visitor->email, $visitor->name)->subject('Registration');
                        $message->from(env('MAIL_USERNAME'),'PXS HRMS');
                    });
                    $visitor->update(['email_verified_at'=>Carbon::now()]);
                }
            }
        }else{
            
            $validator = Validator::make($request->all(), [
                'email' => 'required|unique:users',
                'nric' => 'required|unique:users',
            ]);
            if ($validator->fails()) {
                return redirect()->back()
                            ->withErrors($validator);
            }

            $request->merge(['password' => Hash::make('123456789'),'role'=>'visitor','is_active'=>$is_active]);
            $visitor = User::create($request->all());
            if($is_active == 1){
                $data = array('name'=>$visitor->name,'email'=>$visitor->email);
                Mail::send(['text'=>'email.visitor_reg_done_by_admin_mail'], $data, function($message) use($visitor){
                    $message->to($visitor->email, $visitor->name)->subject('Registration');
                    $message->from(env('MAIL_USERNAME'),'PXS HRMS');
                });
                $visitor->update(['email_verified_at'=>Carbon::now()]);
            }
        }


        //$request->merge(['user_id'=>$visitor->id]);
        // if($request->visitor_pass_id > 0){
        //     $visitor_pass = VisitorPass::find($request->visitor_pass_id);
        //     $visitor_pass->update($request->all());
        // }else{
        //     $visitor_pass= VisitorPass::create($request->all());
        // }
        // $visitor->update(['card_id'=>$request->visitor_card_id,'visitor_pass_id'=>$visitor_pass->id]);
        
        // $visitor_pass->accessFloor()->delete();
        // if(isset($request->access) && count($request->access)>0){
        //     foreach($request->access as $access){
        //         $findaccess = Access::where('access_name',$access)->get();
        //         foreach($findaccess as $a){
        //             $request->merge(['access_id'=>$a->id,'role'=>$visitor->role]);
        //             $visitor_pass->accessFloor()->create($request->all());
        //         }
        //     }
        // }
        
        // $visitor_pass->LiftaccessFloor()->delete();
        // if(isset($request->lift_access) && count($request->lift_access)>0){
        //     foreach($request->lift_access as $lift_access){
        //         $request->merge(['lift_access_id'=>$lift_access,'role'=>$visitor->role]);
        //         $visitor_pass->LiftaccessFloor()->create($request->all());
        //     }
        // }

        //$this->sendEmailForUser($visitor);

        return redirect()->route('visitor.index');
    }

    public function storeVisitorPass(Request $request)
    {
        $visitor = User::find($request->visitor_idd);
        $fromdatetime = Carbon::parse($request->from_date_time);
        $todatetime = Carbon::parse($request->to_date_time);
        $from_date = $fromdatetime->format('Y-m-d');
        $from_time = $fromdatetime->format('H:i:s');
        $to_date = $todatetime->format('Y-m-d');
        $to_time = $todatetime->format('H:i:s');
        $request->merge(['user_id'=>$visitor->id,'from_date'=>$from_date,'from_time'=>$from_time,'to_date'=>$to_date,'to_time'=>$to_time]);
        $visitor->visitorPass()->create($request->all());

        return redirect()->route('visitor.edit',compact('visitor'));
    }

    public function storeVisitorPassAccess(Request $request)
    {
        $visitor_pass = VisitorPass::find($request->visitor_pass_id);
        $visitor = User::find($visitor_pass->user_id);

        $visitor_pass->accessFloor()->delete();
        if(isset($request->access) && count($request->access)>0){
            foreach($request->access as $access){
                $findaccess = Access::where('access_name',$access)->get();
                foreach($findaccess as $a){
                    $request->merge(['access_id'=>$a->id,'role'=>$visitor->role]);
                    $visitor_pass->accessFloor()->create($request->all());
                }
            }
        }
        
        $visitor_pass->LiftaccessFloor()->delete();
        if(isset($request->lift_access) && count($request->lift_access)>0){
            foreach($request->lift_access as $lift_access){
                $request->merge(['lift_access_id'=>$lift_access,'role'=>$visitor->role]);
                $visitor_pass->LiftaccessFloor()->create($request->all());
            }
        }

        return redirect()->route('visitor.edit',compact('visitor'));
    }

    public function edit(User $visitor)
    {
        return view('visitor.create')->with('visitor',$visitor);
    }

    public function setAccess(VisitorPass $visitorPass)
    {
        $access = Access::groupBy('access_name')->select('access_name')->get();
        $lift_access = LiftAccess::all();
        $visitorPass->access_floor=$visitorPass->accessFloor->pluck('access_id')->all();
        $findaccessByUser = Access::whereIn('id',$visitorPass->access_floor)->groupBy('access_name')->select('access_name')->get();
        $user_access =array();
        foreach($findaccessByUser as $a){
            array_push($user_access,$a->access_name);
        }
        $visitorPass->all_user_access =$user_access;
        $visitorPass->lift_access_floor=$visitorPass->LiftaccessFloor->pluck('lift_access_id')->all();


        return view('visitor.setAccess')->with('visitorPass',$visitorPass)->with('access',$access)->with('lift_access',$lift_access);
    }

    public function destroy(User $visitor)
    {
        $visitor->update(['deleted_by',Auth::user()->id]);
        $visitor->delete();
        return redirect()->back();
    }

    public function destroyPass(VisitorPass $visitorPass)
    {
        $visitorPass->delete();
        return redirect()->back();
    }

    public function approvePass(VisitorPass $visitorPass)
    {
        $visitor = User::find($visitorPass->user_id);
        VisitorPass::where('user_id',$visitor->id)->where('status','Approved')->update(['status'=>'Expired']);
        $visitorPass->update(['status'=>"Approved"]);
        $visitor->update(['card_id'=>$visitorPass->visitor_card_id,'visitor_pass_id'=>$visitorPass->id]);
        $this->sendEmailForUser($visitor);
        return redirect()->back();
    }
}
