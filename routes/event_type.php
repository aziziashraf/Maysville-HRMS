<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(EventTypeController::class)->prefix('/event_type')->as('event_type.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:event_type-index');
    Route::get('/create', 'create')->name('create')->middleware('can:event_type-create');
    Route::post('/store', 'store')->name('store')->middleware('can:event_type-store');
    Route::get('/show/{event_type}', 'show')->name('show')->middleware('can:event_type-show');
    Route::get('/edit/{event_type}', 'edit')->name('edit')->middleware('can:event_type-edit');
    Route::post('/update/{event_type}', 'update')->name('update')->middleware('can:event_type-update');
    Route::get('/destroy/{event_type}', 'destroy')->name('destroy')->middleware('can:event_type-destroy');
});
