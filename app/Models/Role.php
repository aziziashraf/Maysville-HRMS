<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class Role extends Model
{
    use HasRolesAndAbilities;

    use HasFactory;
	// use SoftDeletes;
	protected $fillable = [
        'name',
        'title',
    ];
}
