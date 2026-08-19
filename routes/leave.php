<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(LeaveController::class)->prefix('/leave')->as('leave.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:leave-index');
    Route::get('/create', 'create')->name('create')->middleware('can:leave-create');
    Route::post('/store', 'store')->name('store')->middleware('can:leave-store');
    Route::get('/show/{leave}', 'show')->name('show')->middleware('can:leave-show');
    Route::get('/edit/{leave}', 'edit')->name('edit')->middleware('can:leave-edit');
    Route::post('/update/{leave}', 'update')->name('update')->middleware('can:leave-update');
    Route::get('/destroy/{leave}', 'destroy')->name('destroy')->middleware('can:leave-destroy');

    Route::get('/requestIndex', 'requestIndex')->name('requestIndex')->middleware('can:leave-requestIndex');
    Route::get('/requestEdit/{leave}', 'requestEdit')->name('requestEdit')->middleware('can:leave-requestEdit');
    Route::post('/requestUpdate/{leave}', 'requestUpdate')->name('requestUpdate')->middleware('can:leave-requestUpdate');

    Route::get('/requestCreate', 'requestCreate')->name('requestCreate')->middleware('can:leave-requestCreate');
    Route::post('/requestStore', 'requestStore')->name('requestStore')->middleware('can:leave-requestStore');
});

Route::controller(LeaveController::class)->prefix('/leave')->as('leave.')->group(function() {
    // Route::get('/cronRenewLeaveBalanceMonthly', 'renewLeaveBalanceMonthly')->name('cronRenewLeaveBalanceMonthly');
    // Route::get('/cronRenewLeaveBalanceYearly', 'renewLeaveBalanceYearly')->name('cronRenewLeaveBalanceYearly');
    // Route::get('/cronClearExpiredLeave', 'clearExpiredLeave')->name('cronClearExpiredLeave');
});