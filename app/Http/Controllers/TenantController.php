<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Access;
use App\Models\Company;
use App\Models\UserAccessFloor;
use App\Models\LiftAccess;
use App\Models\UserLiftAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');
        $company_id = $request->query('company_id');
        $tenant = User::where('role','tenant');
		if (!empty($filter)) {
            $tenant = $tenant->where(function($query) use($filter) {
                $query->where('name', 'like', '%'.$filter.'%')
                      ->orWhere('card_id', 'like', '%'.$filter.'%')
                      ->orWhere('email', 'like', '%'.$filter.'%')
                      ->orWhere('contact_no', 'like', '%'.$filter.'%');
            });
        }

        if ($company_id>0) {
            $tenant = $tenant->where('company_id',$company_id);
        }

        $tenant = $tenant->get();
        $company = Company::all();

        // $tenant->append(['filter' => $filter]);
        // $tenant->append(['company_id' => $company_id]);
        return view('tenant.index')->with('tenant',$tenant)->with('filter', $filter)->with('company', $company)->with('company_id', $company_id);
    
    }


    public function create()
    {
        $tenant = [];
        $access = Access::groupBy('access_name')->select('access_name')->get();
        $lift_access = LiftAccess::all();
        $company = Company::all();
        return view('tenant.create')->with('tenant',$tenant)->with('access',$access)->with('lift_access',$lift_access)->with('company',$company);
    }

    public function store(Request $request)
    {   
        if($request->tenant_id >0){
            if($request->status == 1){
                $is_active = 1;
            }else{
                $is_active = 0;
            }
            $request->merge(['is_active'=>$is_active]);
            $tenant= User::find($request->tenant_id);
            $tenant->update($request->all());
            
        }else{
            if($request->status == 1){
                $is_active = 1;
            }else{
                $is_active = 0;
            }
            $validator = Validator::make($request->all(), [
                'email' => 'required|unique:users',
            ]);
            if ($validator->fails()) {
                return redirect()->back()
                            ->withErrors($validator);
            }
            $request->merge(['password' => Hash::make('123456789'),'role'=>'tenant','is_active'=>$is_active]);
            $tenant = User::create($request->all());
            $this->sendEmailForUser($tenant);
        }
        if(!empty($request->profile_image)){
            
            $filename = $tenant->id."/".$request->profile_image->getClientOriginalName();
            $request->profile_image->storeAs('images',$filename,'public');
            $tenant->update(['profile_image'=>$filename]);
           // $tenant->update(['profile_image' => $this->upload($request->file_upload, 'tenant', $tenant->id)]);
        }
        $tenant->accessFloor()->delete();
        if(isset($request->access) && count($request->access)>0){
            foreach($request->access as $access){
                $findaccess = Access::where('access_name',$access)->get();
                foreach($findaccess as $a){
                    $request->merge(['access_id'=>$a->id,'role'=>$tenant->role]);
                    $tenant->accessFloor()->create($request->all());
                }
            }
        }
        
        $tenant->LiftaccessFloor()->delete();
        if(isset($request->lift_access) && count($request->lift_access)>0){
            foreach($request->lift_access as $lift_access){
                $request->merge(['lift_access_id'=>$lift_access,'role'=>$tenant->role]);
                $tenant->LiftaccessFloor()->create($request->all());
            }
        }
        return redirect()->route('tenant.index');
    }

    public function edit(User $user)
    {
        $access = Access::groupBy('access_name')->select('access_name')->get();
        $lift_access = LiftAccess::all();
        $user->access_floor=$user->accessFloor->pluck('access_id')->all();
        $findaccessByUser = Access::whereIn('id',$user->access_floor)->groupBy('access_name')->select('access_name')->get();
        $user_access =array();
        foreach($findaccessByUser as $a){
            array_push($user_access,$a->access_name);
        }
        $user->all_user_access =$user_access;
        $user->lift_access_floor=$user->LiftaccessFloor->pluck('lift_access_id')->all();
        $company = Company::all();

        return view('tenant.create')->with('tenant',$user)->with('access',$access)->with('lift_access',$lift_access)->with('company',$company);
    }

    public function destroy(User $user)
    {
        $user->update(['deleted_by',Auth::user()->id]);
        $user->delete();
        return redirect()->back();
    }
}
