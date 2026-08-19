<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Access extends Model
{
    use HasFactory;
	use SoftDeletes;
	protected $fillable = [
        'access_name',
        'access_level',
        'access_type',
        'activity',
        'access_id',
        'door_code',
    ];
}
