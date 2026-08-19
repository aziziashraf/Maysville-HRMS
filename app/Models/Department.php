<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory;
	use SoftDeletes;
	protected $fillable = [
        'department_name',
        'check_in_out_access_floor',
    ];

    public function position(){
        return $this->hasMany(Position::class);
    }
}
