<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Bouncer;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Bouncer::role()->firstOrCreate([ 'name' => 'superadmin', 'title' => 'Super Administrator' ]);
        Bouncer::role()->firstOrCreate([ 'name' => 'hod', 'title' => 'Head of Department' ]);
        Bouncer::role()->firstOrCreate([ 'name' => 'employee', 'title' => 'Employee' ]);
        Bouncer::role()->firstOrCreate([ 'name' => 'management', 'title' => 'Management' ]);
    }
}
