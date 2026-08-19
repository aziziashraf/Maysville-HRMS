<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(OverTimeController::class)->prefix('/overtime')->as('overtime.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:overtime-index');
    Route::get('/create', 'create')->name('create')->middleware('can:overtime-create');
    Route::post('/store', 'store')->name('store')->middleware('can:overtime-store');
    Route::get('/show/{overtime}', 'show')->name('show')->middleware('can:overtime-show');
    Route::get('/edit/{overtime}', 'edit')->name('edit')->middleware('can:overtime-edit');
    Route::post('/update/{overtime}', 'update')->name('update')->middleware('can:overtime-update');
    Route::get('/destroy/{overtime}', 'destroy')->name('destroy')->middleware('can:overtime-destroy');

    Route::get('/requestIndex', 'requestIndex')->name('requestIndex')->middleware('can:overtime-requestIndex');
    Route::get('/requestEdit/{overtime}', 'requestEdit')->name('requestEdit')->middleware('can:overtime-requestEdit');
    Route::post('/requestUpdate/{overtime}', 'requestUpdate')->name('requestUpdate')->middleware('can:overtime-requestUpdate');
});
