<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventType;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $defaultEventTypes = [
            [
                'name' => 'Company Working Day',
            ],
            [
                'name' => 'Company Holiday',
            ],
            [
                'name' => 'Public Holiday',
            ],
        ];

        foreach ($defaultEventTypes as $eventTypeData) {
            $eventType = EventType::where('name', $eventTypeData['name'])->first();

            if (!$eventType) {
                EventType::create($eventTypeData);
            }
        }
    }
}

