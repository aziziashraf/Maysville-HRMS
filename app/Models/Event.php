<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'event_type_id',
    ];

    public function event_type()
    {
        return $this->belongsTo(EventType::class);
    }
}
