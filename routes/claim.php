<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(ClaimController::class)->prefix('/claim')->as('claim.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware(['can:claim-index','menu.access']);
    Route::get('/create', 'create')->name('create')->middleware('can:claim-create');
    Route::post('/store', 'store')->name('store')->middleware('can:claim-store');
    Route::get('/show/{claim}', 'show')->name('show')->middleware('can:claim-show');
    Route::get('/edit/{claim}', 'edit')->name('edit')->middleware('can:claim-edit');
    Route::post('/update/{claim}', 'update')->name('update')->middleware('can:claim-update');
    Route::get('/destroy/{claim}', 'destroy')->name('destroy')->middleware('can:claim-destroy');

    Route::get('/requestIndex', 'requestIndex')->name('requestIndex')->middleware('can:claim-requestIndex');
    Route::get('/requestEdit/{claim}', 'requestEdit')->name('requestEdit')->middleware('can:claim-requestEdit');
    Route::post('/requestUpdate/{claim}', 'requestUpdate')->name('requestUpdate')->middleware('can:claim-requestUpdate');
});
