<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(DepartmentController::class)->prefix('/department')->as('department.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:department-list');
    Route::get('/create', 'create')->name('create')->middleware('can:department-create');
    Route::get('/edit/{department}', 'edit')->name('edit')->middleware('can:department-edit');
    Route::post('/store', 'store')->name('store');
    Route::get('/destroy/{department}', 'destroy')->name('destroy')->middleware('can:department-delete');
});
