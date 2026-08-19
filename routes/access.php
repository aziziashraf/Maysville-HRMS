<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(AccessController::class)->prefix('/access')->as('access.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:access-list');
    Route::get('/create', 'create')->name('create');
    Route::get('/edit/{access}', 'edit')->name('edit');
    Route::post('/store', 'store')->name('store');
    Route::get('/destroy/{access}', 'destroy')->name('destroy');
});
