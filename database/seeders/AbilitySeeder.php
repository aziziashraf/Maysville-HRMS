<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Bouncer;

class AbilitySeeder extends Seeder
{
    public function run()
    {
        $abilitiesToCreate = [
            [ 'name' => 'home', 'title' => 'Home',],
            [ 'name' => 'dashboard', 'title' => 'Dashboard',],

            [ 'name' => 'access-list', 'title' => 'Access List', ],

            [ 'name' => 'department-list', 'title' => 'Department List',],
            [ 'name' => 'department-create', 'title' => 'Department Create',],
            [ 'name' => 'department-edit', 'title' => 'Department Edit',],
            [ 'name' => 'department-delete', 'title' => 'Department Delete',],

            [ 'name' => 'employee-list', 'title' => 'Employee List',],
            [ 'name' => 'employee-create', 'title' => 'Employee Create',],
            [ 'name' => 'employee-edit', 'title' => 'Employee Edit',],
            [ 'name' => 'employee-delete', 'title' => 'Employee Delete',],

            // [ 'name' => 'holiday-list', 'title' => 'Holiday List',],
            // [ 'name' => 'holiday-create', 'title' => 'Holiday Create',],
            // [ 'name' => 'holiday-edit', 'title' => 'Holiday Edit',],
            // [ 'name' => 'holiday-delete', 'title' => 'Holiday Delete',],

            [ 'name' => 'notification-list', 'title' => 'Notification List',],
            [ 'name' => 'notification-create', 'title' => 'Notification Create',],
            [ 'name' => 'notification-edit', 'title' => 'Notification Edit',],
            [ 'name' => 'notification-delete', 'title' => 'Notification Delete',],
            [ 'name' => 'notification-show', 'title' => 'Notification Show',],

            [ 'name' => 'report-building_access_index', 'title' => 'Report Building Access List',],
            [ 'name' => 'report-employee_index', 'title' => 'Report Employee Access List',],
            [ 'name' => 'report-generateEmployeeAttendance', 'title' => 'Report Generate Employee Attendance Button',],
            [ 'name' => 'report-building_excel', 'title' => 'Report Building Access Print',],
            [ 'name' => 'report-employee_excel', 'title' => 'Report Employee Access Print',],

            // [ 'name' => 'tenant-list', 'title' => 'Tenant List',],
            // [ 'name' => 'tenant-create', 'title' => 'Tenant Create',],
            // [ 'name' => 'tenant-edit', 'title' => 'Tenant Edit',],
            // [ 'name' => 'tenant-delete', 'title' => 'Tenant Delete',],

            [ 'name' => 'visitor-list', 'title' => 'Visitor List',],
            [ 'name' => 'visitor-create', 'title' => 'Visitor Create',],
            [ 'name' => 'visitor-edit', 'title' => 'Visitor Edit',],
            [ 'name' => 'visitor-delete', 'title' => 'Visitor Delete',],

            // [ 'name' => 'working_hour-list', 'title' => 'Working Hour List',],
            // [ 'name' => 'working_hour-store', 'title' => 'Working Hour Save',],
            // [ 'name' => 'working_hour-addNewDiv', 'title' => 'Working Hour Add',],
            // [ 'name' => 'working_hour-delete', 'title' => 'Working Hour Delete',],

            [ 'name' => 'role-list', 'title' => 'User Role List',],
            [ 'name' => 'role-create', 'title' => 'User Role Create',],
            [ 'name' => 'role-edit', 'title' => 'User Role Edit',],
            [ 'name' => 'role-delete', 'title' => 'User Role Delete',],

            [ 'name' => 'user-list', 'title' => 'User List',],
            [ 'name' => 'user-create', 'title' => 'User Create',],
            [ 'name' => 'user-edit', 'title' => 'User Edit',],
            [ 'name' => 'user-delete', 'title' => 'User Delete',],

            // [ 'name' => 'company-list', 'title' => 'Company List',],
            // [ 'name' => 'company-create', 'title' => 'Company Create',],
            // [ 'name' => 'company-edit', 'title' => 'Company Edit',],
            // [ 'name' => 'company-delete', 'title' => 'Company Delete',],
            
            [ 'name' => 'show-own-department-only', 'title' => 'Show Own Department Only',],

            [ 'name' => 'position-index',     'title' => 'Position Index'],
            [ 'name' => 'position-create',    'title' => 'Position Create'],
            [ 'name' => 'position-store',     'title' => 'Position Store'],
            [ 'name' => 'position-show',      'title' => 'Position Show'],
            [ 'name' => 'position-edit',      'title' => 'Position Edit'],
            [ 'name' => 'position-update',    'title' => 'Position Update'],
            [ 'name' => 'position-destroy',   'title' => 'Position Destroy'],

            [ 'name' => 'leave-index',     'title' => 'Leave Index'],
            [ 'name' => 'leave-create',    'title' => 'Leave Create'],
            [ 'name' => 'leave-store',     'title' => 'Leave Store'],
            [ 'name' => 'leave-show',      'title' => 'Leave Show'],
            [ 'name' => 'leave-edit',      'title' => 'Leave Edit'],
            [ 'name' => 'leave-update',    'title' => 'Leave Update'],
            [ 'name' => 'leave-destroy',   'title' => 'Leave Destroy'],
            [ 'name' => 'leave-requestIndex',   'title' => 'Leave Request Index'],
            [ 'name' => 'leave-requestEdit',   'title' => 'Leave Request Edit'],
            [ 'name' => 'leave-requestUpdate',   'title' => 'Leave Request Update'],
            [ 'name' => 'leave-requestCreate',   'title' => 'Leave Request Create'],
            [ 'name' => 'leave-requestStore',   'title' => 'Leave Request Store'],

            [ 'name' => 'leave_type-index',     'title' => 'Leave Type Index'],
            [ 'name' => 'leave_type-create',    'title' => 'Leave Type Create'],
            [ 'name' => 'leave_type-store',     'title' => 'Leave Type Store'],
            [ 'name' => 'leave_type-show',      'title' => 'Leave Type Show'],
            [ 'name' => 'leave_type-edit',      'title' => 'Leave Type Edit'],
            [ 'name' => 'leave_type-update',    'title' => 'Leave Type Update'],
            [ 'name' => 'leave_type-destroy',   'title' => 'Leave Type Destroy'],

            [ 'name' => 'event-index',     'title' => 'Event Index'],
            [ 'name' => 'event-create',    'title' => 'Event Create'],
            [ 'name' => 'event-store',     'title' => 'Event Store'],
            [ 'name' => 'event-show',      'title' => 'Event Show'],
            [ 'name' => 'event-edit',      'title' => 'Event Edit'],
            [ 'name' => 'event-update',    'title' => 'Event Update'],
            [ 'name' => 'event-destroy',   'title' => 'Event Destroy'],

            [ 'name' => 'event_type-index',     'title' => 'Event Type Index'],
            [ 'name' => 'event_type-create',    'title' => 'Event Type Create'],
            [ 'name' => 'event_type-store',     'title' => 'Event Type Store'],
            [ 'name' => 'event_type-show',      'title' => 'Event Type Show'],
            [ 'name' => 'event_type-edit',      'title' => 'Event Type Edit'],
            [ 'name' => 'event_type-update',    'title' => 'Event Type Update'],
            [ 'name' => 'event_type-destroy',   'title' => 'Event Type Destroy'],

            [ 'name' => 'claim-index',            'title' => 'Claim Index'],
            [ 'name' => 'claim-create',           'title' => 'Claim Create'],
            [ 'name' => 'claim-store',            'title' => 'Claim Store'],
            [ 'name' => 'claim-show',             'title' => 'Claim Show'],
            [ 'name' => 'claim-edit',             'title' => 'Claim Edit'],
            [ 'name' => 'claim-update',           'title' => 'Claim Update'],
            [ 'name' => 'claim-destroy',          'title' => 'Claim Destroy'],
            [ 'name' => 'claim-requestIndex',     'title' => 'Claim Request Index'],
            [ 'name' => 'claim-requestEdit',      'title' => 'Claim Request Edit'],
            [ 'name' => 'claim-requestUpdate',    'title' => 'Claim Request Update'],

            [ 'name' => 'claim_type-index',     'title' => 'Claim Type Index'],
            [ 'name' => 'claim_type-create',    'title' => 'Claim Type Create'],
            [ 'name' => 'claim_type-store',     'title' => 'Claim Type Store'],
            [ 'name' => 'claim_type-show',      'title' => 'Claim Type Show'],
            [ 'name' => 'claim_type-edit',      'title' => 'Claim Type Edit'],
            [ 'name' => 'claim_type-update',    'title' => 'Claim Type Update'],
            [ 'name' => 'claim_type-destroy',   'title' => 'Claim Type Destroy'],

            // [ 'name' => 'handbook-list', 'title' => 'HandBook List',],
            // [ 'name' => 'handbook-index', 'title' => 'HandBook Index',],
            // [ 'name' => 'handbook-create', 'title' => 'HandBook Create',],
            // [ 'name' => 'handbook-store', 'title' => 'HandBook Store',],
            // [ 'name' => 'handbook-show', 'title' => 'HandBook Show',],
            // [ 'name' => 'handbook-edit', 'title' => 'HandBook Edit',],
            // [ 'name' => 'handbook-update', 'title' => 'HandBook Update',],
            // [ 'name' => 'handbook-delete', 'title' => 'HandBook Delete',],
            // [ 'name' => 'handbook-destroy', 'title' => 'HandBook Destroy',],

            // [ 'name' => 'handbook_category-index',     'title' => 'HandBook Category Index'],
            // [ 'name' => 'handbook_category-create',    'title' => 'HandBook Category Create'],
            // [ 'name' => 'handbook_category-store',     'title' => 'HandBook Category Store'],
            // [ 'name' => 'handbook_category-show',      'title' => 'HandBook Category Show'],
            // [ 'name' => 'handbook_category-edit',      'title' => 'HandBook Category Edit'],
            // [ 'name' => 'handbook_category-update',    'title' => 'HandBook Category Update'],
            // [ 'name' => 'handbook_category-destroy',   'title' => 'HandBook Category Destroy'],
            
            // [ 'name' => 'overtime-index',     'title' => 'Overtime Index'],
            // [ 'name' => 'overtime-create',    'title' => 'Overtime Create'],
            // [ 'name' => 'overtime-store',     'title' => 'Overtime Store'],
            // [ 'name' => 'overtime-show',      'title' => 'Overtime Show'],
            // [ 'name' => 'overtime-edit',      'title' => 'Overtime Edit'],
            // [ 'name' => 'overtime-update',    'title' => 'Overtime Update'],
            // [ 'name' => 'overtime-destroy',   'title' => 'Overtime Destroy'],
            // [ 'name' => 'overtime-requestIndex',   'title' => 'Overtime Request Index'],
            // [ 'name' => 'overtime-requestEdit',   'title' => 'Overtime Request Edit'],
            // [ 'name' => 'overtime-requestUpdate',   'title' => 'Overtime Request Update'],

            [ 'name' => 'leave_balance_list-index',     'title' => 'Leave Balance List Index'],
            [ 'name' => 'leave_balance_list-create',    'title' => 'Leave Balance List Create'],
            [ 'name' => 'leave_balance_list-store',     'title' => 'Leave Balance List Store'],
            [ 'name' => 'leave_balance_list-show',      'title' => 'Leave Balance List Show'],
            [ 'name' => 'leave_balance_list-edit',      'title' => 'Leave Balance List Edit'],
            [ 'name' => 'leave_balance_list-update',    'title' => 'Leave Balance List Update'],
            [ 'name' => 'leave_balance_list-destroy',   'title' => 'Leave Balance List Destroy'],

            // [ 'name' => 'purchase_requisition-index',           'title' => 'Purchase Requisition Index'],
            // [ 'name' => 'purchase_requisition-create',          'title' => 'Purchase Requisition Create'],
            // [ 'name' => 'purchase_requisition-store',           'title' => 'Purchase Requisition Store'],
            // [ 'name' => 'purchase_requisition-show',            'title' => 'Purchase Requisition Show'],
            // [ 'name' => 'purchase_requisition-edit',            'title' => 'Purchase Requisition Edit'],
            // [ 'name' => 'purchase_requisition-update',          'title' => 'Purchase Requisition Update'],
            // [ 'name' => 'purchase_requisition-destroy',         'title' => 'Purchase Requisition Destroy'],
            // [ 'name' => 'purchase_requisition-createClaim',     'title' => 'Purchase Requisition Claim Create'],
            // [ 'name' => 'purchase_requisition-download',        'title' => 'Purchase Requisition Download'],
            // [ 'name' => 'purchase_requisition-requestIndex',    'title' => 'Purchase Requisition Request Index'],
            // [ 'name' => 'purchase_requisition-requestEdit',     'title' => 'Purchase Requisition Request Edit'],
            // [ 'name' => 'purchase_requisition-requestUpdate',   'title' => 'Purchase Requisition Request Update'],
        ];

        // Retrieve all existing abilities from the database
        $existingAbilities = Bouncer::ability()->all()->pluck('name');

        foreach ($abilitiesToCreate as $abilityData) {
            // Check if the ability already exists in the database
            if (!$existingAbilities->contains($abilityData['name'])) {
                Bouncer::ability()->firstOrCreate($abilityData);
            }
        }

        // Delete abilities that are not present in the seeder anymore
        foreach ($existingAbilities as $existingAbility) {
            if (!in_array($existingAbility, array_column($abilitiesToCreate, 'name'))) {
                Bouncer::ability()->where('name', $existingAbility)->delete();
            }
        }
        
    }
}
