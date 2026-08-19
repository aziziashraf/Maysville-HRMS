<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\UserAttendance;
use App\Models\Department;
use App\Models\EmployeeDailyAttendance;
use App\Models\User;
use App\Models\Access;
use App\Models\UserAccessFloor;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\NotificationPopUp;
use App\Events\NotificationPopUpAdmin;
use DB; 
use Mail; 
use Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class SyncData //implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $scan_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($scan_data)
    {
        $this->scan_data = $scan_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$scan_data = $this->scan_data;
        $response = Http::post(env('APP_CLOUD_URL').'api/receivedData', [
            'scan_data' => $scan_data,
        ]);

        
        if($response['message'] == "success"){
            foreach($scan_data as $s){
                $s->delete();
            }
            if(count($response['users'])>0){
                foreach($response['users'] as $u){
                    $user = User::find($u['id']);
                    if(isset($user)){
                        $user->update($u);
                    }else{
                        DB::table('users')->insert($u);
                    }
                }
            }
            if(count($response['accesses'])>0){
                foreach($response['accesses'] as $p){
                    $access = Access::find($p['id']);
                    if(isset($access)){
                        $access->update($p);
                    }else{
                        DB::table('accesses')->insert($p);
                    }
                }
            }
            if(count($response['user_access_floors'])>0){
                foreach($response['user_access_floors'] as $a){
                    $user_access_floor = UserAccessFloor::find($a['id']);
                    if(isset($user_access_floor)){
                        $user_access_floor->update($a);
                    }else{
                        DB::table('user_access_floors')->insert($a);
                    }
                }
            }
        }
        
        $response_details=[
            'success' =>true,
            'message' =>"Sync Done!"
        ];
        return $response_details;
    }
}
