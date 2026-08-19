<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserAttendance;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Bouncer;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create dummy employees
        $this->createDummyEmployees();
        
        // Seed attendance data
        $this->seedAttendanceData();
    }
    
    /**
     * Create dummy employees
     */
    private function createDummyEmployees(): void
    {
        // Get available departments for assigning employees
        $departments = Department::all();
        if ($departments->isEmpty()) {
            // Create a default department if none exists
            $department = Department::create([
                'department_name' => 'Human Resources',
            ]);
            $departments = Department::all();
        }
        
        // Get employee role
        $employeeRole = Bouncer::role()->where('name', 'employee')->first();
        if (!$employeeRole) {
            // Create role if it doesn't exist
            $employeeRole = Bouncer::role()->firstOrCreate([
                'name' => 'employee',
                'title' => 'Employee'
            ]);
        }
        
        // Get position for employees
        $positions = Position::where('role_id', $employeeRole->id)->get();
        if ($positions->isEmpty()) {
            // Create a default position if none exists
            $position = Position::create([
                'name' => 'Staff',
                'department_id' => $departments->first()->id,
                'role_id' => $employeeRole->id,
                'description' => 'Regular staff position',
                'is_active' => 1
            ]);
            $positions = Position::where('role_id', $employeeRole->id)->get();
        }
        
        // Define dummy employee data
        $dummyEmployees = [
            [
                'name' => 'Jane Doe',
                'email' => 'jane.doe@example.com',
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@example.com',
            ],
            [
                'name' => 'Sara Williams',
                'email' => 'sara.williams@example.com',
            ],
            [
                'name' => 'Robert Chen',
                'email' => 'robert.chen@example.com',
            ],
        ];
        
        // Create the employees
        foreach ($dummyEmployees as $employeeData) {
            // Check if employee with this email already exists
            if (User::where('email', $employeeData['email'])->exists()) {
                continue; // Skip if employee already exists
            }
            
            // Select random department and position
            $department = $departments->random();
            $position = $positions->random();
            
            // Create user
            $employee = User::create([
                'name' => $employeeData['name'],
                'email' => $employeeData['email'],
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'role_id' => $employeeRole->id,
                'department_id' => $department->id,
                'position_id' => $position->id,
                'is_active' => 1,
                'contact_no' => '+1' . mt_rand(2000000000, 9999999999),
                'start_date' => Carbon::now()->subMonths(rand(1, 24))->format('Y-m-d'),
                'confirmed_date' => Carbon::now()->subMonths(rand(0, 12))->format('Y-m-d'),
                'card_id' => Str::random(10),
            ]);

            // assign user->id to employee_id
            $employee->update(['staff_id' => $employee->id]);
            
            // Assign role to the employee
            $employee->assign($employeeRole->name);
            
            echo "Created employee: " . $employee->name . "\n";
        }
    }
    
    /**
     * Seed attendance data for employees
     */
    private function seedAttendanceData(): void
    {
        // date in Y-m-d format; 2025-01-01
        $startDate = '2025-01-01';
        $endDate = '2025-05-31';
        $employees = User::where('role','employee')->where('is_active',1)->get();

        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            foreach ($employees as $employee) {

                // random skip some employee, 20% chance to skip
                if (rand(1, 100) <= 20) {
                    continue;
                }


                // clock in random from 07:30:00 to 08:30:00 in H:i:s format
                $clockIn = Carbon::createFromFormat('H:i:s', '07:30:00')
                    ->add(rand(0, 3600), 'seconds')
                    ->format('H:i:s');

                UserAttendance::create([
                    'user_id' => $employee->id,
                    'scan_datetime' => $currentDate . ' ' . $clockIn,
                    'scan_date' => $currentDate,
                    'scan_time' => $clockIn,
                    'access_id' => '1'
                ]);

                // clock out random from 16:50:00 to 17:50:00 in H:i:s format
                $clockOut = Carbon::createFromFormat('H:i:s', '16:57:00')
                    ->add(rand(0, 3600), 'seconds')
                    ->format('H:i:s');

                UserAttendance::create([
                    'user_id' => $employee->id,
                    'scan_datetime' => $currentDate . ' ' . $clockOut,
                    'scan_date' => $currentDate,
                    'scan_time' => $clockOut,
                    'access_id' => '1'
                ]);
            }
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
    }
}
