<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkingHourDays extends Model
{
    use HasFactory;
	use SoftDeletes;

	protected $table = 'working_hour_days';

	protected $fillable = [
        'working_hour_id',
        'days',
    ];
}
