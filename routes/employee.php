<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(EmployeeController::class)->prefix('/employee')->as('employee.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:employee-list');
    Route::get('/create', 'create')->name('create')->middleware('can:employee-create');
    Route::get('/edit/{user}', 'edit')->name('edit')->middleware('can:employee-edit');
    Route::post('/store', 'store')->name('store');
    Route::get('/destroy/{user}', 'destroy')->name('destroy')->middleware('can:employee-delete');
    Route::get('/reset_password/{user}', 'reset_password')->name('reset_password');
});
