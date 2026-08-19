<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::controller(EmployeeDocumentController::class)->prefix('/employee_document')->as('employee_document.')->middleware('auth')->group(function () {
    Route::get('/index', 'index')->name('index')->middleware('can:employee_document-index');
    Route::get('/create', 'create')->name('create')->middleware('can:employee_document-create');
    Route::post('/store', 'store')->name('store')->middleware('can:employee_document-store');
    Route::get('/edit/{employee_document}', 'edit')->name('edit')->middleware('can:employee_document-edit');
    Route::get('/employee/{user}', 'employee')->name('employee')->middleware('can:employee_document-index');
    Route::get('/destroy/{employee_document}', 'destroy')->name('destroy')->middleware('can:employee_document-destroy');
});
