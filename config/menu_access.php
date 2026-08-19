<?php

/*
|--------------------------------------------------------------------------
| Per-staff sidebar menu access
|--------------------------------------------------------------------------
|
| One entry per item in the sidebar's personal section. This is the single
| source of truth: the sidebar reads it to decide what to render, and the
| Menu Access screen reads it to build its checkboxes, so the two cannot
| drift apart. Adding an item here makes it assignable everywhere.
|
| key      the value stored in user_menu_accesses.menu_key — never change it
|          for an existing item or the saved rows stop matching
| label    shown on the Menu Access screen
| default  whether a staff member sees this when nothing has been saved for
|          them yet, so existing accounts keep working untouched
| ability  optional Bouncer ability that must ALSO pass. Keeps the role-level
|          permissions already in place; per-staff access narrows, never widens
| routes   route names this item covers, used by the menu.access middleware
|          so hiding an item also blocks the URL
|
*/

return [

    'items' => [

        'dashboard' => [
            'label' => 'Dashboard',
            'description' => 'Personal and management dashboards',
            'default' => true,
            'routes' => ['index', 'managementIndex'],
        ],

        'calendar' => [
            'label' => 'Calendar',
            'description' => 'Company calendar of events and leave',
            'default' => true,
            'routes' => ['calendar'],
        ],

        'attendance' => [
            'label' => 'Attendance',
            'description' => 'Own attendance records',
            'default' => true,
            'routes' => ['attendance'],
        ],

        'daily_scan' => [
            'label' => 'Daily Scan',
            'description' => 'Daily scan history',
            'default' => true,
            'routes' => ['daily_scan'],
        ],

        'announcement' => [
            'label' => 'Announcement',
            'description' => 'Company announcements',
            'default' => true,
            'routes' => ['notification.indexUser'],
        ],

        'leave' => [
            'label' => 'Leave Application',
            'description' => 'Apply for and track leave',
            'default' => true,
            'ability' => 'leave-index',
            'routes' => ['leave.index', 'leave.create', 'leave.edit'],
        ],

        'purchase_requisition' => [
            'label' => 'Purchase Requisition',
            'description' => 'Raise purchase requisitions',
            'default' => true,
            'ability' => 'purchase_requisition-index',
            'routes' => ['purchase_requisition.index', 'purchase_requisition.create', 'purchase_requisition.edit'],
        ],

        'claim' => [
            'label' => 'Claim Application',
            'description' => 'Submit and track expense claims',
            'default' => true,
            'ability' => 'claim-index',
            'routes' => ['claim.index', 'claim.create', 'claim.edit'],
        ],

        'overtime' => [
            'label' => 'Overtime Application',
            'description' => 'Submit and track overtime',
            'default' => true,
            'ability' => 'overtime-index',
            'routes' => ['overtime.index', 'overtime.create', 'overtime.edit'],
        ],

    ],

];
