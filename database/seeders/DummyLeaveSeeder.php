<?php

namespace Database\Seeders;

use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceList;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Demo leave data for the ten MSV employees created by DummyEmployeeSeeder:
 * leave types, an opening balance per employee, and applications spread across
 * every status the workflow can produce.
 *
 * Balances are written to match the approved leave below, so the remaining
 * figures add up rather than looking arbitrary.
 *
 * Re-running rebuilds the applications for those ten accounts only.
 */
class DummyLeaveSeeder extends Seeder
{
    /**
     * name, unit, renew, entitlement, carry forward, carry limit, colour, unlimited
     */
    private const TYPES = [
        ['Annual Leave',        'day', 'year', 16, true,  5, '#1C3A72', false],
        ['Medical Leave',       'day', 'year', 14, false, 0, '#BF0000', false],
        ['Emergency Leave',     'day', 'year',  3, false, 0, '#E2A03F', false],
        ['Compassionate Leave', 'day', 'year',  3, false, 0, '#2196F3', false],
        ['Unpaid Leave',        'day', 'none',  0, false, 0, '#888EA8', true],
        ['Maternity Leave',     'day', 'year', 98, false, 0, '#E7515A', false],
        ['Paternity Leave',     'day', 'year',  7, false, 0, '#00AB55', false],
    ];

    /** Types an employee gets a tracked balance for. */
    private const BALANCE_TYPES = ['Annual Leave', 'Medical Leave', 'Emergency Leave', 'Compassionate Leave'];

    /**
     * staff_id, leave type, days from today to the start, length in WORKING
     * days, status, remarks
     *
     * Lengths are working days because that is what the application itself
     * counts: LeaveController::calculateDuration() skips Saturday and Sunday.
     * Seeding raw calendar spans produced rows reading "0 day(s)" whenever one
     * landed on a weekend.
     */
    private const APPLICATIONS = [
        // Taken earlier in the year
        ['MSV1004', 'Annual Leave',        -120, 3, 'approved',  'Family trip to Kuching'],
        ['MSV1005', 'Medical Leave',        -85, 2, 'approved',  'Fever, MC attached'],
        ['MSV1007', 'Annual Leave',         -60, 5, 'approved',  'Annual break'],
        ['MSV1002', 'Medical Leave',        -45, 1, 'approved',  'Dental appointment'],
        ['MSV1008', 'Emergency Leave',      -30, 1, 'approved',  'Family emergency'],
        ['MSV1010', 'Annual Leave',         -21, 2, 'approved',  'Personal matters'],
        ['MSV1006', 'Compassionate Leave',  -14, 3, 'approved',  'Bereavement'],

        // Approved and still to come
        ['MSV1003', 'Annual Leave',          10, 5, 'approved',  'Year end break'],
        ['MSV1009', 'Annual Leave',          25, 3, 'approved',  'Wedding'],

        // Waiting on a reviewer
        ['MSV1004', 'Annual Leave',           7, 2, 'submitted', 'Short break'],
        ['MSV1008', 'Medical Leave',          3, 1, 'submitted', 'Hospital appointment'],
        ['MSV1010', 'Emergency Leave',        1, 1, 'submitted', 'Urgent family matter'],

        // Reviewed, waiting on an approver
        ['MSV1006', 'Annual Leave',          14, 4, 'reviewed',  'Balik kampung'],
        ['MSV1007', 'Medical Leave',          5, 2, 'reviewed',  'Minor procedure'],

        // Turned down
        ['MSV1009', 'Annual Leave',         -10, 7, 'rejected',  'Clashes with shutdown schedule'],
        ['MSV1010', 'Unpaid Leave',          -5, 3, 'rejected',  'Insufficient notice'],

        // Withdrawn by the employee
        ['MSV1005', 'Annual Leave',          20, 2, 'cancelled', 'Plans changed'],

        // Not submitted yet
        ['MSV1004', 'Annual Leave',          30, 1, 'draft',     'Pending confirmation of dates'],
    ];

    public function run(): void
    {
        $types = $this->createLeaveTypes();

        $staff = User::whereIn('staff_id', $this->staffIds())->get()->keyBy('staff_id');

        if ($staff->isEmpty()) {
            $this->command->warn('No MSV employees found — run DummyEmployeeSeeder first.');
            return;
        }

        $reviewer = $staff->get('MSV1001');   // Site Supervisor
        $approver = $staff->get('MSV1003');   // Operations Manager

        $this->createApplications($staff, $types, $reviewer, $approver);
        $this->createBalances($staff, $types);
    }

    private function staffIds(): array
    {
        return ['MSV1001', 'MSV1002', 'MSV1003', 'MSV1004', 'MSV1005',
                'MSV1006', 'MSV1007', 'MSV1008', 'MSV1009', 'MSV1010'];
    }

    /**
     * @return \Illuminate\Support\Collection<string, LeaveType>
     */
    private function createLeaveTypes()
    {
        $created = 0;

        foreach (self::TYPES as [$name, $unit, $renew, $amount, $carry, $carryLimit, $colour, $unlimited]) {
            $existing = LeaveType::where('name', $name)->first();

            if ($existing) {
                continue;
            }

            LeaveType::create([
                'name' => $name,
                'description' => $name . ' entitlement',
                'balance_unit' => $unit,
                'renew_freq' => $renew,
                'default_amount' => $amount,
                'carry_forward' => $carry,
                'carry_forward_limit' => $carryLimit,
                'label_color' => $colour,
                'unlimited' => $unlimited,
                'confirmed_employees_only' => in_array($name, ['Maternity Leave', 'Paternity Leave'], true),
                'limit_per_leave' => false,
                'limit_per_leave_amount' => 0,
                'back_dated' => true,
                'back_dated_days_limit' => 14,
                'attachment_required' => $name === 'Medical Leave',
            ]);

            $created++;
        }

        $this->command->info('leave types: ' . $created . ' created, ' . LeaveType::count() . ' total.');

        return LeaveType::all()->keyBy('name');
    }

    private function createApplications($staff, $types, ?User $reviewer, ?User $approver): void
    {
        // Rebuild only what this seeder owns.
        $removed = Leave::whereIn('user_id', $staff->pluck('id'))->forceDelete();
        if ($removed) {
            $this->command->info('cleared ' . $removed . ' previous leave application(s).');
        }

        $today = Carbon::today();
        $created = 0;

        foreach (self::APPLICATIONS as [$staffId, $typeName, $startOffset, $lengthInDays, $status, $remarks]) {
            $employee = $staff->get($staffId);
            $type = $types->get($typeName);

            if (!$employee || !$type) {
                continue;
            }

            $start = $this->nextWorkingDay($today->copy()->addDays($startOffset));
            $end = $this->endAfterWorkingDays($start, $lengthInDays);

            // Anchor the paper trail to just before the leave was due to start.
            $submittedAt = $start->copy()->subDays(7)->setTime(9, 15);

            $row = [
                'user_id' => $employee->id,
                'leave_type_id' => $type->id,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'remarks' => $remarks,
                'status' => $status,
            ];

            if ($status !== 'draft') {
                $row['submitted_at'] = $submittedAt;
            }

            // Anything past the reviewer carries a review stamp.
            if (in_array($status, ['reviewed', 'approved', 'rejected', 'cancelled'], true) && $reviewer) {
                $row['review_status'] = 1;
                $row['reviewed_by'] = $reviewer->id;
                $row['reviewed_at'] = $submittedAt->copy()->addDay();
                $row['review_remark'] = $status === 'rejected' ? 'Reviewed, referred to approver' : 'Reviewed';
            }

            if ($status === 'approved' && $approver) {
                $row['approval_status'] = 1;
                $row['approved_by'] = $approver->id;
                $row['approved_at'] = $submittedAt->copy()->addDays(2);
                $row['approval_remark'] = 'Approved';
            }

            if ($status === 'rejected' && $approver) {
                $row['approval_status'] = 0;
                $row['approved_by'] = $approver->id;
                $row['approved_at'] = $submittedAt->copy()->addDays(2);
                $row['approval_remark'] = $remarks;
            }

            Leave::create($row);
            $created++;
        }

        $this->command->info('created ' . $created . ' leave application(s).');
    }

    /**
     * Opening balance per employee per tracked type, reduced by the approved
     * leave seeded above so the remaining figures are consistent with history.
     */
    private function createBalances($staff, $types): void
    {
        $year = Carbon::today()->year;
        $expiry = Carbon::create($year, 12, 31)->format('Y-m-d');
        $created = 0;

        // Rebuild the opening entries this seeder owns so they always reflect
        // the applications above. Anything an admin has since adjusted by hand
        // for these ten demo accounts is replaced.
        $balanceIds = LeaveBalance::whereIn('user_id', $staff->pluck('id'))->pluck('id');

        if ($balanceIds->isNotEmpty()) {
            $listIds = LeaveBalanceList::whereIn('leave_balance_id', $balanceIds)->pluck('id');

            if ($listIds->isNotEmpty()) {
                \App\Models\LeaveBalanceListLog::whereIn('leave_balance_list_id', $listIds)->forceDelete();
                LeaveBalanceList::whereIn('id', $listIds)->forceDelete();
                $this->command->info('cleared ' . $listIds->count() . ' previous balance entr(ies).');
            }
        }

        foreach ($staff as $employee) {
            foreach (self::BALANCE_TYPES as $typeName) {
                $type = $types->get($typeName);

                if (!$type) {
                    continue;
                }

                $balance = LeaveBalance::firstOrCreate([
                    'user_id' => $employee->id,
                    'leave_type_id' => $type->id,
                ]);

                $taken = Leave::where('user_id', $employee->id)
                    ->where('leave_type_id', $type->id)
                    ->where('status', 'approved')
                    ->get()
                    ->sum(fn ($leave) => $this->workingDaysBetween(
                        Carbon::parse($leave->start_date),
                        Carbon::parse($leave->end_date ?: $leave->start_date)
                    ));

                LeaveBalanceList::create([
                    'leave_balance_id' => $balance->id,
                    'description' => $year . ' entitlement',
                    'balance' => max(0, $type->default_amount - $taken),
                    'expiry_date' => $expiry,
                    'month' => null,
                    'year' => $year,
                    'status' => true,
                    'carried_forward' => false,
                ]);

                $created++;
            }
        }

        $this->command->info('created ' . $created . ' leave balance entr(ies).');
    }

    /*
    |--------------------------------------------------------------------------
    | Working-day helpers
    |--------------------------------------------------------------------------
    |
    | These mirror LeaveController::calculateDuration(), which counts Monday to
    | Friday and skips public holidays. The holidays table is empty on a fresh
    | install, so weekends are the only exclusion reproduced here; if holidays
    | are later seeded, durations shown in the app may come out slightly below
    | what the balances here assume.
    |
    */

    private function isWorkingDay(Carbon $date): bool
    {
        return !in_array((int) $date->format('N'), [6, 7], true);
    }

    private function nextWorkingDay(Carbon $date): Carbon
    {
        $date = $date->copy();

        while (!$this->isWorkingDay($date)) {
            $date->addDay();
        }

        return $date;
    }

    /**
     * Last calendar day of a leave that should span $workingDays working days.
     */
    private function endAfterWorkingDays(Carbon $start, int $workingDays): Carbon
    {
        $date = $start->copy();
        $counted = $this->isWorkingDay($date) ? 1 : 0;

        while ($counted < $workingDays) {
            $date->addDay();

            if ($this->isWorkingDay($date)) {
                $counted++;
            }
        }

        return $date;
    }

    private function workingDaysBetween(Carbon $start, Carbon $end): int
    {
        $date = $start->copy();
        $days = 0;

        while ($date->lte($end)) {
            if ($this->isWorkingDay($date)) {
                $days++;
            }

            $date->addDay();
        }

        return $days;
    }
}
