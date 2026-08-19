<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiftAccess extends Model
{
    use HasFactory;
	use SoftDeletes;

	protected $table = 'lift_accesses';

	protected $fillable = [
        'access_level',
    ];
}
