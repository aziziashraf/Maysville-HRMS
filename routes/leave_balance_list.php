<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalanceList;
use Illuminate\Support\Facades\Route;

Route::controller(LeaveBalanceListController::class)->prefix('/leave_balance_list')->as('leave_balance_list.')->middleware('auth')->group(function() {
    Route::get('/index/{leaveBalance}', 'index')->name('index')->middleware('can:leave_balance_list-index');
    Route::get('/create/{leaveBalance}', 'create')->name('create')->middleware('can:leave_balance_list-create');
    Route::post('/store', 'store')->name('store')->middleware('can:leave_balance_list-store');
    Route::get('/show/{leaveBalanceList}', 'show')->name('show')->middleware('can:leave_balance_list-show');
    Route::get('/edit/{leaveBalanceList}', 'edit')->name('edit')->middleware('can:leave_balance_list-edit');
    Route::post('/update/{leaveBalanceList}', 'update')->name('update')->middleware('can:leave_balance_list-update');
    Route::get('/destroy/{leaveBalanceList}', 'destroy')->name('destroy')->middleware('can:leave_balance_list-destroy');
});
