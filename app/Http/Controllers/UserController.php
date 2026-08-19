<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Bouncer;
use stdClass;
use Throwable;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter');
		if (!empty($filter)) {
            $user = User::where('email','<>','admin@sedc.com')->where('role_id','>',0)
            ->where(function($query) use($filter) {
                $query->where('name', 'like', '%'.$filter.'%')
                      ->orWhere('email', 'like', '%'.$filter.'%');
            })
            ->get();
        }else{
            $user = User::where('email','<>','admin@sedc.com')->where('role_id','>',0)->get();
        }
        foreach($user as $u){
            $bouncerRole = $u->getRoles()->first();
            if ($bouncerRole == null) {
                $u->bouncerRole = 'No roles assigned';
            }else{
                $u->bouncerRole = $bouncerRole;
            }
        }
        
        // $user->appends(['filter' => $filter]);

        return view('user.index')->with('user',$user)->with('filter',$filter);
    }

    public function role_index(Request $request)
    {
        $role = Bouncer::role()->where('name','<>','superadmin')->get();
        return view('user.role_index')->with('role',$role);
    }

    public function create()
    {
        $user = [];
        $role = Bouncer::role()->all();
        return view('user.create')->with('user',$user)->with('role',$role);
    }
    
    public function role_create()
    {
        $role = [];
        $allAbilities = Bouncer::ability()->all()->sortBy('name');
        return view('user.role_create')->with('role',$role)->with('allAbilities',$allAbilities);
    }

    public function store(Request $request)
    {
        $role = Bouncer::role()->find($request->role_id);
        $user = User::where('email',$request->email)->first();
        if(isset($user)){
            $old_role = Bouncer::role()->find($user->role_id);
            if(isset($old_role )){
                $user->retract($old_role->name);
            }
            $user->update($request->all());
        }else{
            $request->merge(['password' => Hash::make('123456789')]);
            $user = User::create($request->all());
            $this->sendEmailForUser($user);
        }
        $user->assign($role->name);

        return redirect()->route('user.index');
    }
    

    public function role_store(Request $request)
    {
        if($request->role_id >0){
            $role = Bouncer::role()->find($request->role_id);
            $role->update($request->all());
        }else{
            $role = Bouncer::role()->create($request->all());
        }

        if (empty($request->abilities)) {
          $request->abilities = [];
        }
        Bouncer::sync($role)->abilities($request->abilities);
        Session::flash('success', 'Role\'s abilities has been successfully updated');

        return redirect()->route('user.role_index');
    }

    public function edit(User $user)
    {
        $role = Bouncer::role()->all();
        return view('user.create')->with('user',$user)->with('role',$role);
    }

    public function role_edit($role_id)
    {
        $role = Bouncer::role()->where('id', $role_id)->first();;
        $role->abilities = $role->getAbilities();
        $allAbilities = Bouncer::ability()->all()->sortBy('name');
        return view('user.role_create')->with('role',$role)->with('allAbilities',$allAbilities);
    }

    public function getName($email){
        $user = User::where('email',$email)->first();
        return response()->json($user);
    }
    

    public function destroy(User $user)
    {
        $old_role = Bouncer::role()->find($user->role_id);
        if(isset($old_role )){
            $user->retract($old_role->name);
        }
        $user->update(['role_id',null]);
        return redirect()->back();
    }

    public function role_destroy($role_id)
    {
        $user = User::where('role_id',$role_id)->get();
        if(count($user)>0){
            return back()->withMessage('Cannot delete: this Roles has Users');
        }
        $role = Bouncer::role()->where('id', $role_id)->delete();
        return redirect()->back();
    }

    

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile')->with('user',$user);
    }

    public function saveProfile(Request $request)
    {
        $user = Auth::user();
        $user->update($request->all());
        return redirect()->route('user.profile');
    }

    public function uploadProfilePic(Request $request)
    {
        $user = Auth::user();
        
        if($request->hasFile('profile_image')){
            $filename = $user->id."/".$request->profile_image->getClientOriginalName();
            $request->profile_image->storeAs('images',$filename,'public');
            $user->update(['profile_image'=>$filename]);
        }
        return redirect()->route('user.profile');
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
          'current_password' => 'required',
          'password' => 'required|string|min:6|confirmed',
          'password_confirmation' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password does not match!');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('user.profile')->with('success', 'Password Updated!');
    }

}
