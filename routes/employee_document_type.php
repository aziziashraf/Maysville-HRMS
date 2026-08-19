<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::controller(EmployeeDocumentTypeController::class)->prefix('/employee_document_type')->as('employee_document_type.')->middleware('auth')->group(function () {
    Route::get('/index', 'index')->name('index')->middleware('can:employee_document_type-index');
    Route::get('/create', 'create')->name('create')->middleware('can:employee_document_type-create');
    Route::post('/store', 'store')->name('store')->middleware('can:employee_document_type-store');
    Route::get('/edit/{employee_document_type}', 'edit')->name('edit')->middleware('can:employee_document_type-edit');
    Route::get('/destroy/{employee_document_type}', 'destroy')->name('destroy')->middleware('can:employee_document_type-destroy');
});
