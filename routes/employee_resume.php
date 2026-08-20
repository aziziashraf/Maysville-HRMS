<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::controller(EmployeeResumeController::class)->prefix('/employee_resume')->as('employee_resume.')->middleware('auth')->group(function () {
    Route::get('/edit/{user}', 'edit')->name('edit')->middleware('can:employee_resume-edit');
    Route::post('/update/{user}', 'update')->name('update')->middleware('can:employee_resume-update');
    Route::get('/preview/{user}', 'preview')->name('preview')->middleware('can:employee_resume-edit');
    Route::get('/download/{user}', 'download')->name('download')->middleware('can:employee_resume-edit');
});
