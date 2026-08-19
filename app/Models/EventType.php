<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class EventType extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    protected static function booted()
    {
        // Prevent updating the names of the default event types
        static::saving(function ($eventType) {
            $defaultEventTypes = ['Company Working Day', 'Company Holiday', 'Public Holiday'];

            if (in_array($eventType->name, $defaultEventTypes)) {
                if ($eventType->isDirty('name') && EventType::where('name', $eventType->name)->first()) {
                    $validator = Validator::make($eventType->getAttributes(), [
                        'name' => 'not_in:'.$eventType->name,
                    ]);

                    if ($validator->fails()) {
                        throw ValidationException::withMessages($validator->errors()->toArray());
                    }
                }
            }
        });
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
