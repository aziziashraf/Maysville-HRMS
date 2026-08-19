<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeDocument;
use App\Models\EmployeeDocumentType;
use App\Models\Position;
use App\Models\User;
use Bouncer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data for evaluating the system: one company, the departments Maysville
 * actually runs, matching positions, ten employees, and a spread of
 * certification records covering every expiry state.
 *
 * Safe to re-run — every step skips rows that already exist.
 */
class DummyEmployeeSeeder extends Seeder
{
    private const PASSWORD = 'password123';

    public function run(): void
    {
        $company = $this->createCompany();
        $departments = $this->createDepartments();
        $positions = $this->createPositions($departments);

        $this->createEmployees($company, $departments, $positions);
        $this->createSampleDocuments();
    }

    private function createCompany(): Company
    {
        return Company::firstOrCreate(['company_name' => 'Maysville Sdn Bhd']);
    }

    /**
     * @return array<string, Department>
     */
    private function createDepartments(): array
    {
        $names = [
            'Operations',
            'Mechanical',
            'Civil',
            'HSE',
            'Catalyst Handling',
            'Tank Maintenance',
            'Human Resources',
            'Finance & Admin',
        ];

        $departments = [];

        foreach ($names as $name) {
            $departments[$name] = Department::firstOrCreate(['department_name' => $name]);
        }

        return $departments;
    }

    /**
     * @param  array<string, Department>  $departments
     * @return array<string, Position>
     */
    private function createPositions(array $departments): array
    {
        $employeeRole = Bouncer::role()->firstOrCreate(['name' => 'employee', 'title' => 'Employee']);

        $definitions = [
            'Site Supervisor' => 'Operations',
            'Mechanical Technician' => 'Mechanical',
            'Mechanical Engineer' => 'Mechanical',
            'Civil Technician' => 'Civil',
            'HSE Officer' => 'HSE',
            'Catalyst Technician' => 'Catalyst Handling',
            'Tank Cleaning Crew' => 'Tank Maintenance',
            'HR Executive' => 'Human Resources',
            'Account Executive' => 'Finance & Admin',
        ];

        $positions = [];

        foreach ($definitions as $title => $departmentName) {
            $positions[$title] = Position::firstOrCreate(
                [
                    'name' => $title,
                    'department_id' => $departments[$departmentName]->id,
                ],
                [
                    'role_id' => $employeeRole->id,
                    'description' => $title . ' — ' . $departmentName,
                    'is_active' => 1,
                    'leave_reviewer' => in_array($title, ['Site Supervisor', 'HR Executive'], true) ? 1 : 0,
                    'leave_approver' => $title === 'Site Supervisor' ? 1 : 0,
                ]
            );
        }

        return $positions;
    }

    /**
     * @param  array<string, Department>  $departments
     * @param  array<string, Position>  $positions
     */
    private function createEmployees(Company $company, array $departments, array $positions): void
    {
        $employeeRole = Bouncer::role()->where('name', 'employee')->first();

        // staff_id, name, email local part, position, months of service
        $people = [
            ['MSV1001', 'Ahmad Faizal bin Rahman',      'ahmad.faizal',    'Site Supervisor',       78],
            ['MSV1002', 'Nurul Aisyah binti Hassan',    'nurul.aisyah',    'HR Executive',          54],
            ['MSV1003', 'Lim Wei Chong',                'lim.weichong',    'Mechanical Engineer',   96],
            ['MSV1004', 'Gregory Anak Jimbai',          'gregory.jimbai',  'Mechanical Technician', 41],
            ['MSV1005', 'Siti Nurhaliza binti Osman',   'siti.nurhaliza',  'HSE Officer',           63],
            ['MSV1006', 'Rajesh Kumar a/l Subramaniam', 'rajesh.kumar',    'Catalyst Technician',   29],
            ['MSV1007', 'Tan Mei Ling',                 'tan.meiling',     'Account Executive',     35],
            ['MSV1008', 'Mohd Hafiz bin Ismail',        'mohd.hafiz',      'Tank Cleaning Crew',    18],
            ['MSV1009', 'Dayang Nurfatimah bt Awang',   'dayang.nurfatimah', 'Civil Technician',    12],
            ['MSV1010', 'Chandran a/l Muthu',           'chandran.muthu',  'Mechanical Technician',  7],
        ];

        foreach ($people as $index => [$staffId, $name, $emailLocal, $positionTitle, $monthsOfService]) {
            $email = $emailLocal . '@maysville.com.my';

            if (User::where('email', $email)->exists()) {
                $this->command->info('skipped (exists): ' . $name);
                continue;
            }

            $position = $positions[$positionTitle];
            $startDate = Carbon::today()->subMonths($monthsOfService);

            $employee = User::create([
                'name' => $name,
                'username' => $email,
                'email' => $email,
                'password' => Hash::make(self::PASSWORD),
                'role' => 'employee',
                'role_id' => $employeeRole->id,
                'staff_id' => $staffId,
                'card_id' => 'CARD' . (1001 + $index),
                'department_id' => $position->department_id,
                'position_id' => $position->id,
                'company_id' => $company->id,
                'contact_no' => '+601' . random_int(1, 9) . '-' . random_int(2000000, 9999999),
                'nric' => random_int(70, 99) . str_pad((string) random_int(1, 12), 2, '0', STR_PAD_LEFT)
                    . str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT)
                    . '-13-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                'is_active' => 1,
                'start_date' => $startDate->format('Y-m-d'),
                'confirmed_date' => $startDate->copy()->addMonths(3)->format('Y-m-d'),
            ]);

            $employee->assign($employeeRole->name);

            $this->command->info('created: ' . $staffId . '  ' . $name . '  (' . $positionTitle . ')');
        }
    }

    /**
     * Give the new employees certification records spanning expired, expiring
     * and valid, so the dashboard expiry widget has something to show.
     *
     * Skipped entirely when no information types have been defined yet.
     */
    private function createSampleDocuments(): void
    {
        $safety = EmployeeDocumentType::where('name', 'Safety Passport')->first();
        $forklift = EmployeeDocumentType::where('name', 'Forklift Licence')->first();
        $nextOfKin = EmployeeDocumentType::where('name', 'Next of Kin')->first();

        if (!$safety && !$forklift && !$nextOfKin) {
            $this->command->warn('No information types defined — skipping sample certification records.');
            return;
        }

        // staff_id, type, days from today until expiry (null = no expiry), custom values
        $records = [
            ['MSV1001', $safety,   -45, ['grade' => 'Gold Book', 'issuing_country' => 'Malaysia']],
            ['MSV1004', $safety,   -12, ['grade' => 'Green Book', 'issuing_country' => 'Malaysia']],
            ['MSV1005', $safety,    14, ['grade' => 'Gold Book', 'issuing_country' => 'Malaysia']],
            ['MSV1006', $safety,    44, ['grade' => 'Green Book', 'issuing_country' => 'Malaysia']],
            ['MSV1003', $safety,   210, ['grade' => 'Gold Book', 'issuing_country' => 'Malaysia']],
            ['MSV1008', $forklift,  -3, ['licence_class' => 'Class III']],
            ['MSV1004', $forklift,  21, ['licence_class' => 'Class II']],
            ['MSV1010', $forklift,  27, ['licence_class' => 'Class III']],
            ['MSV1009', $forklift, 160, ['licence_class' => 'Class I']],
            ['MSV1002', $nextOfKin, null, ['contact_name' => 'Hassan bin Yusof', 'relationship' => 'Father', 'contact_number' => '013-8871240']],
            ['MSV1007', $nextOfKin, null, ['contact_name' => 'Tan Ah Kow', 'relationship' => 'Father', 'contact_number' => '016-3320981']],
        ];

        $issuers = ['NIOSH', 'JKKP', 'CIDB', 'DOSH Sarawak'];
        $created = 0;

        foreach ($records as [$staffId, $type, $daysToExpiry, $customValues]) {
            if (!$type) {
                continue;
            }

            $employee = User::where('staff_id', $staffId)->first();
            if (!$employee) {
                continue;
            }

            // Don't duplicate on a re-run.
            $exists = EmployeeDocument::where('user_id', $employee->id)
                ->where('employee_document_type_id', $type->id)
                ->when($daysToExpiry !== null, fn ($q) => $q->whereDate('expiry_date', Carbon::today()->addDays($daysToExpiry)))
                ->when($daysToExpiry === null, fn ($q) => $q->whereNull('expiry_date'))
                ->exists();

            if ($exists) {
                continue;
            }

            $expiryDate = $daysToExpiry === null ? null : Carbon::today()->addDays($daysToExpiry);

            EmployeeDocument::create([
                'user_id' => $employee->id,
                'employee_document_type_id' => $type->id,
                'reference_no' => $daysToExpiry === null
                    ? null
                    : strtoupper(substr($type->name, 0, 2)) . '-' . random_int(1000, 9999),
                'issued_by' => $daysToExpiry === null ? null : $issuers[array_rand($issuers)],
                'issue_date' => $expiryDate ? $expiryDate->copy()->subYears(2)->format('Y-m-d') : null,
                'expiry_date' => $expiryDate?->format('Y-m-d'),
                'custom_values' => $customValues,
            ]);

            $created++;
        }

        $this->command->info('created ' . $created . ' sample certification record(s).');
    }
}
