<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLiftAccess extends Model
{
    use HasFactory;
	use SoftDeletes;

	protected $table = 'user_lift_accesses';

	protected $fillable = [
        'content_id',
        'content_type',
        'lift_access_id',
        'role',
    ];

    public function lift_access()
    {
        return $this->belongsTo('App\Models\LiftAccess');
    }
    
    public function content()
    {
        return $this->morphTo();
    }
}
