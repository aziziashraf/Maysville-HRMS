<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::controller(EmployeeMenuAccessController::class)->prefix('/employee_menu_access')->as('employee_menu_access.')->middleware('auth')->group(function () {
    Route::get('/edit/{user}', 'edit')->name('edit')->middleware('can:employee_menu_access-edit');
    Route::post('/update/{user}', 'update')->name('update')->middleware('can:employee_menu_access-update');
    Route::get('/reset/{user}', 'reset')->name('reset')->middleware('can:employee_menu_access-update');
});
