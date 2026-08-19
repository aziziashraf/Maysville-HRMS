<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(LeaveTypeController::class)->prefix('/leave_type')->as('leave_type.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:leave_type-index');
    Route::get('/create', 'create')->name('create')->middleware('can:leave_type-create');
    Route::post('/store', 'store')->name('store')->middleware('can:leave_type-store');
    Route::get('/show/{leave_type}', 'show')->name('show')->middleware('can:leave_type-show');
    Route::get('/edit/{leave_type}', 'edit')->name('edit')->middleware('can:leave_type-edit');
    Route::post('/update/{leave_type}', 'update')->name('update')->middleware('can:leave_type-update');
    Route::get('/destroy/{leave_type}', 'destroy')->name('destroy')->middleware('can:leave_type-destroy');
});


Route::controller(LeaveBalanceTierController::class)->prefix('/leave_balance_tier')->as('leave_balance_tier.')->middleware('auth')->group(function() {
    Route::get('/create/{leave_type}', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/edit/{leave_balance_tier}', 'edit')->name('edit');
    Route::get('/destroy/{leave_balance_tier}', 'destroy')->name('destroy');
}); 