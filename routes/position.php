<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(PositionController::class)->prefix('/position')->as('position.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:position-index');
    Route::get('/create', 'create')->name('create')->middleware('can:position-create');
    Route::post('/store', 'store')->name('store')->middleware('can:position-store');
    Route::get('/show/{position}', 'show')->name('show')->middleware('can:position-show');
    Route::get('/edit/{position}', 'edit')->name('edit')->middleware('can:position-edit');
    Route::post('/update/{position}', 'update')->name('update')->middleware('can:position-update');
    Route::get('/destroy/{position}', 'destroy')->name('destroy')->middleware('can:position-destroy');

    Route::get('/get-positions/{department}', 'getPosition')->name('get-position');
});
