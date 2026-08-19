<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Bouncer;

class UserSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('email','admin@projekx.com')->first();
        $role = Bouncer::role()->where('name','superadmin')->first();
        if(!isset($user)){
            $data = [
                'name'      => 'Admin',
                'username'  => 'admin@projekx.com',
				'email'     => 'admin@projekx.com',
                'password'  => Hash::make('admin123456'),
                'role_id'     => $role->id,
                'role'      => 'superadmin',
                'is_active' => true
            ];
            $user = User::create($data);
            $user->assign($role->name);
        }
        
        // $abilities = [];
        // for($i = 1;$i <= 49;$i++) {
        //     array_push($abilities,$i);
        // }
        $abilities = Bouncer::ability()->pluck('id');
        
        $bouncerRole = $user->getRoles()->first();
        Bouncer::allow($bouncerRole)->to($abilities);
        Bouncer::disallow($bouncerRole)->to('show-own-department-only');
    }
}
