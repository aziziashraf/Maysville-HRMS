<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(EventController::class)->prefix('/event')->as('event.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:event-index');
    Route::get('/create', 'create')->name('create')->middleware('can:event-create');
    Route::post('/store', 'store')->name('store')->middleware('can:event-store');
    Route::get('/show/{event}', 'show')->name('show')->middleware('can:event-show');
    Route::get('/edit/{event}', 'edit')->name('edit')->middleware('can:event-edit');
    Route::post('/update/{event}', 'update')->name('update')->middleware('can:event-update');
    Route::get('/destroy/{event}', 'destroy')->name('destroy')->middleware('can:event-destroy');

    Route::get('/calendarEvent', 'calendarEvent')->name('calendarEvent');
});
