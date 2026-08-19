<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(AttachmentController::class)->prefix('/attachment')->as('attachment.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    // Route::get('/show/{attachment}', 'show')->name('show');
    Route::get('/edit/{attachment}', 'edit')->name('edit');
    Route::post('/update/{attachment}', 'update')->name('update');
    Route::get('/destroy/{attachment}', 'destroy')->name('destroy');
});

Route::controller(AttachmentController::class)->prefix('/attachment')->as('attachment.')->group(function() {
    Route::get('/show/{attachment}', 'show')->name('show');
});
