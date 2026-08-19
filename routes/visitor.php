<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(VisitorController::class)->prefix('/visitor')->as('visitor.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:visitor-list');
    Route::get('/create', 'create')->name('create')->middleware('can:visitor-create');
    Route::get('/setAccess/{visitorPass}', 'setAccess')->name('setAccess');
    Route::get('/edit/{visitor}', 'edit')->name('edit')->middleware('can:visitor-edit');
    Route::post('/store', 'store')->name('store');
    Route::get('/destroy/{visitor}', 'destroy')->name('destroy')->middleware('can:visitor-delete');
    Route::post('/storeVisitorPassAccess', 'storeVisitorPassAccess')->name('storeVisitorPassAccess');
    Route::post('/storeVisitorPass', 'storeVisitorPass')->name('storeVisitorPass');
    Route::get('/destroyPass/{visitorPass}', 'destroyPass')->name('destroyPass');
    Route::get('/approvePass/{visitorPass}', 'approvePass')->name('approvePass');
});
