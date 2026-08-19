<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(ReportController::class)->prefix('/report')->as('report.')->middleware('auth')->group(function() {
    Route::get('/building_access_index', 'building_access_index')->name('building_access_index')->middleware('can:report-building_access_index');
    Route::get('/employee_index', 'employee_index')->name('employee_index')->middleware('can:report-employee_index');
    Route::get('/getBuildingAttendance/{access_id}/{role}/{date_from}/{date_to}', 'getBuildingAttendance')->name('getBuildingAttendance');
    Route::get('/searchBuildingAttendance/{search}', 'searchBuildingAttendance')->name('searchBuildingAttendance');
    Route::get('/generateEmployeeAttendance', 'generateEmployeeAttendance')->name('generateEmployeeAttendance')->middleware('can:report-generateEmployeeAttendance');
    Route::get('/building_excel/{attendance}', 'building_excel')->name('building_excel')->middleware('can:report-building_excel');
    Route::get('/employee_excel/{attendance}', 'employee_excel')->name('employee_excel')->middleware('can:report-employee_excel');
    Route::get('/getCoordinates/{attendance}', 'getCoordinates')->name('getCoordinates');
});

Route::controller(ReportController::class)->prefix('/report')->as('report.')->group(function() {
    Route::get('/cronGenerateAttendances', 'generateEmployeeAttendance')->name('cronGenerateAttendances');
});
