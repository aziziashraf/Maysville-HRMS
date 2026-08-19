<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

Route::controller(NotificationController::class)->prefix('/notification')->as('notification.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:notification-list');
    Route::get('/indexUser', 'indexUser')->name('indexUser')->middleware('menu.access:announcement');
    Route::get('/create', 'create')->name('create')->middleware('can:notification-create');
    Route::get('/edit/{notification}', 'edit')->name('edit')->middleware('can:notification-edit');
    Route::get('/show/{notification}', 'show')->name('show')->middleware('can:notification-show');
    Route::post('/store', 'store')->name('store');
    Route::get('/destroy/{notification}', 'destroy')->name('destroy')->middleware('can:notification-delete');
});
