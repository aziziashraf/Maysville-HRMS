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
 * Demo data for evaluating the system: Maysville's real department structure,
 * matching positions, ten employees, and a spread of certification records
 * covering every expiry state.
 *
 * This seeder is authoritative for the departments and for the ten MSV
 * employees — re-running it renames or retires departments that are not in
 * DEPARTMENTS, and rebuilds the sample certification records so the demo set
 * always matches what is defined here.
 */
class DummyEmployeeSeeder extends Seeder
{
    private const PASSWORD = 'password123';

    /** The department structure as confirmed by the business. */
    private const DEPARTMENTS = [
        'Operation',
        'C&P',
        'HSE',
        'Management',
        'Human Resources',
        'Finance',
        'IT',
        'Cafe',
    ];

    /** Departments seeded under earlier names, renamed rather than recreated. */
    private const RENAMES = [
        'Operations' => 'Operation',
        'Finance & Admin' => 'Finance',
    ];

    /** position title => department */
    private const POSITIONS = [
        'Site Supervisor' => 'Operation',
        'Operation Technician' => 'Operation',
        'Procurement Executive' => 'C&P',
        'HSE Officer' => 'HSE',
        'Operations Manager' => 'Management',
        'HR Executive' => 'Human Resources',
        'Account Executive' => 'Finance',
        'IT Executive' => 'IT',
        'Cafe Assistant' => 'Cafe',
    ];

    /** staff_id, name, email local part, position, months of service */
    private const PEOPLE = [
        ['MSV1001', 'Ahmad Faizal bin Rahman',      'ahmad.faizal',      'Site Supervisor',       78],
        ['MSV1002', 'Nurul Aisyah binti Hassan',    'nurul.aisyah',      'HR Executive',          54],
        ['MSV1003', 'Lim Wei Chong',                'lim.weichong',      'Operations Manager',    96],
        ['MSV1004', 'Gregory Anak Jimbai',          'gregory.jimbai',    'Operation Technician',  41],
        ['MSV1005', 'Siti Nurhaliza binti Osman',   'siti.nurhaliza',    'HSE Officer',           63],
        ['MSV1006', 'Rajesh Kumar a/l Subramaniam', 'rajesh.kumar',      'Procurement Executive', 29],
        ['MSV1007', 'Tan Mei Ling',                 'tan.meiling',       'Account Executive',     35],
        ['MSV1008', 'Mohd Hafiz bin Ismail',        'mohd.hafiz',        'Operation Technician',  18],
        ['MSV1009', 'Dayang Nurfatimah bt Awang',   'dayang.nurfatimah', 'IT Executive',          12],
        ['MSV1010', 'Chandran a/l Muthu',           'chandran.muthu',    'Cafe Assistant',         7],
    ];

    public function run(): void
    {
        $company = $this->createCompany();
        $departments = $this->syncDepartments();
        $positions = $this->syncPositions($departments);

        $this->createEmployees($company, $positions);
        $this->retireUnusedDepartments($departments);
        $this->rebuildSampleDocuments();
    }

    private function createCompany(): Company
    {
        return Company::firstOrCreate(['company_name' => 'Maysville Sdn Bhd']);
    }

    /**
     * Bring the department table in line with DEPARTMENTS, renaming any that
     * were seeded under an old name so their id — and therefore every employee
     * already pointing at it — survives.
     *
     * @return array<string, Department>
     */
    private function syncDepartments(): array
    {
        foreach (self::RENAMES as $old => $new) {
            $existing = Department::where('department_name', $old)->first();

            if ($existing && !Department::where('department_name', $new)->exists()) {
                $existing->update(['department_name' => $new]);
                $this->command->info('renamed department: ' . $old . ' -> ' . $new);
            }
        }

        $departments = [];

        foreach (self::DEPARTMENTS as $name) {
            $department = Department::where('department_name', $name)->first();

            if (!$department) {
                $department = Department::create(['department_name' => $name]);
                $this->command->info('created department: ' . $name);
            }

            $departments[$name] = $department;
        }

        return $departments;
    }

    /**
     * @param  array<string, Department>  $departments
     * @return array<string, Position>
     */
    private function syncPositions(array $departments): array
    {
        $employeeRole = Bouncer::role()->firstOrCreate(['name' => 'employee', 'title' => 'Employee']);
        $positions = [];

        foreach (self::POSITIONS as $title => $departmentName) {
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
                    'leave_approver' => in_array($title, ['Site Supervisor', 'Operations Manager'], true) ? 1 : 0,
                ]
            );
        }

        return $positions;
    }

    /**
     * @param  array<string, Position>  $positions
     */
    private function createEmployees(Company $company, array $positions): void
    {
        $employeeRole = Bouncer::role()->where('name', 'employee')->first();

        foreach (self::PEOPLE as $index => [$staffId, $name, $emailLocal, $positionTitle, $monthsOfService]) {
            $email = $emailLocal . '@maysville.com.my';
            $position = $positions[$positionTitle];
            $startDate = Carbon::today()->subMonths($monthsOfService);

            $employee = User::where('email', $email)->first();

            if ($employee) {
                // Keep the placement authoritative on a re-run.
                $employee->update([
                    'department_id' => $position->department_id,
                    'position_id' => $position->id,
                    'company_id' => $company->id,
                ]);

                $this->command->info('updated: ' . $staffId . '  ' . $name . '  -> ' . $positionTitle);
                continue;
            }

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
     * Soft-delete departments no longer in DEPARTMENTS, along with their
     * positions. Anything still referenced by a user is left alone and
     * reported, so a real employee is never silently orphaned.
     *
     * @param  array<string, Department>  $keep
     */
    private function retireUnusedDepartments(array $keep): void
    {
        $keepIds = collect($keep)->pluck('id')->all();

        foreach (Department::whereNotIn('id', $keepIds)->get() as $department) {
            $userCount = User::where('department_id', $department->id)->count();

            if ($userCount > 0) {
                $this->command->warn(
                    'kept "' . $department->department_name . '" — still assigned to ' . $userCount . ' user(s)'
                );
                continue;
            }

            Position::where('department_id', $department->id)->delete();
            $department->delete();

            $this->command->info('retired department: ' . $department->department_name);
        }
    }

    /**
     * Rebuild the sample certification records for the ten MSV employees so the
     * demo set always matches the table below. Certifications sit on
     * operational staff; office and support staff carry a Next of Kin entry.
     *
     * Only touches records belonging to these ten seeded accounts.
     */
    private function rebuildSampleDocuments(): void
    {
        $safety = EmployeeDocumentType::where('name', 'Safety Passport')->first();
        $forklift = EmployeeDocumentType::where('name', 'Forklift Licence')->first();
        $nextOfKin = EmployeeDocumentType::where('name', 'Next of Kin')->first();

        if (!$safety && !$forklift && !$nextOfKin) {
            $this->command->warn('No information types defined — skipping sample certification records.');
            return;
        }

        $staffIds = array_column(self::PEOPLE, 0);
        $seededUserIds = User::whereIn('staff_id', $staffIds)->pluck('id', 'staff_id');

        $removed = EmployeeDocument::whereIn('user_id', $seededUserIds->values())->forceDelete();
        if ($removed) {
            $this->command->info('cleared ' . $removed . ' previous sample record(s).');
        }

        // staff_id, type, days from today until expiry (null = no expiry), custom values
        $records = [
            ['MSV1001', $safety,    -45, ['grade' => 'Gold Book', 'issuing_country' => 'Malaysia']],
            ['MSV1004', $safety,    -12, ['grade' => 'Green Book', 'issuing_country' => 'Malaysia']],
            ['MSV1008', $forklift,   -3, ['licence_class' => 'Class III']],
            ['MSV1005', $safety,     14, ['grade' => 'Gold Book', 'issuing_country' => 'Malaysia']],
            ['MSV1004', $forklift,   21, ['licence_class' => 'Class II']],
            ['MSV1006', $safety,     44, ['grade' => 'Green Book', 'issuing_country' => 'Malaysia']],
            ['MSV1003', $safety,    210, ['grade' => 'Gold Book', 'issuing_country' => 'Malaysia']],
            ['MSV1001', $forklift,  240, ['licence_class' => 'Class I']],
            ['MSV1002', $nextOfKin, null, ['contact_name' => 'Hassan bin Yusof', 'relationship' => 'Father', 'contact_number' => '013-8871240']],
            ['MSV1007', $nextOfKin, null, ['contact_name' => 'Tan Ah Kow', 'relationship' => 'Father', 'contact_number' => '016-3320981']],
            ['MSV1009', $nextOfKin, null, ['contact_name' => 'Awang bin Drahman', 'relationship' => 'Father', 'contact_number' => '019-8145523']],
            ['MSV1010', $nextOfKin, null, ['contact_name' => 'Kamala a/p Muthu', 'relationship' => 'Spouse', 'contact_number' => '012-7789431']],
        ];

        $issuers = ['NIOSH', 'JKKP', 'CIDB', 'DOSH Sarawak'];
        $created = 0;

        foreach ($records as [$staffId, $type, $daysToExpiry, $customValues]) {
            if (!$type || !isset($seededUserIds[$staffId])) {
                continue;
            }

            $expiryDate = $daysToExpiry === null ? null : Carbon::today()->addDays($daysToExpiry);

            EmployeeDocument::create([
                'user_id' => $seededUserIds[$staffId],
                'employee_document_type_id' => $type->id,
                'reference_no' => $expiryDate
                    ? strtoupper(substr($type->name, 0, 2)) . '-' . random_int(1000, 9999)
                    : null,
                'issued_by' => $expiryDate ? $issuers[array_rand($issuers)] : null,
                'issue_date' => $expiryDate ? $expiryDate->copy()->subYears(2)->format('Y-m-d') : null,
                'expiry_date' => $expiryDate?->format('Y-m-d'),
                'custom_values' => $customValues,
            ]);

            $created++;
        }

        $this->command->info('created ' . $created . ' sample certification record(s).');
    }
}
