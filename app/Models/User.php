<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndAbilities;
	use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'firebase_token',
        'email_verified_at',
        'email',
        'password',
        'is_active',
        'nric',
        'profile_image',
        'contact_no',
        'role_id',
        'role',
        'staff_id',
        'card_id',
        'visitor_pass_id',
        'qr_access',
        'qr_expired_datetime',
        'department_id',
        'company_id',
        'salary',
        'ot_working_hour',
        'ot_weekend',
        'ot_public_holiday',
        'remarks',
        'deleted_by',
        'start_date',
        'confirmed_date',
        'resigned_date',
        'leave_review',
        'position_id',
        'leave_reviewers',
        'leave_approvers',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
        'leave_reviewers' => 'array',
        'leave_approvers' => 'array',
    ];

    public function accessFloor()
    {
        return $this->morphMany('App\Models\UserAccessFloor', 'content');
    } 

    public function LiftaccessFloor()
    {
        return $this->morphMany('App\Models\UserLiftAccess', 'content');
    } 

    public function visitorPass()
    {
        return $this->hasMany('App\Models\VisitorPass');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }
    public function latestPass()
    {
        return $this->hasOne('App\Models\VisitorPass','user_id','id')->latest();
    }

    public function leaves()
    {
        return $this->hasMany('App\Models\Leave');
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function getLeaveReviewers()
    {
        $leaveReviewers = $this->leave_reviewers ?? [];
        $positions = Position::whereIn('id', $leaveReviewers)->get();
        $users = User::whereIn('position_id', $positions->pluck('id'))->where('is_active',1)->get();
        return $users;
    }

    public function getLeaveApprovers()
    {
        $leaveApprovers = $this->leave_approvers ?? [];
        $positions = Position::whereIn('id', $leaveApprovers)->get();
        $users = User::whereIn('position_id', $positions->pluck('id'))->where('is_active',1)->get();
        return $users;
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }

    public function purchaseRequisition()
    {
        return $this->hasMany(PurchaseRequisition::class);
    }

    public function employeeDocuments()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

}
