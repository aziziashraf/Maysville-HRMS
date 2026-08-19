<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAccessFloor extends Model
{
    use HasFactory;
	use SoftDeletes;

	protected $table = 'user_access_floors';

	protected $fillable = [
        'content_id',
        'content_type',
        'access_id',
        'role',
    ];

    public function access()
    {
        return $this->belongsTo('App\Models\Access');
    }
    
    public function content()
    {
        return $this->morphTo();
    }
}
