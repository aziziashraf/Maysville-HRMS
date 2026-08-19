<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HandBookController;

Route::controller(HandBookController::class)->prefix('/handbook')->as('handbook.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:handbook-list');
    Route::get('/indexUser', 'indexUser')->name('indexUser');
    Route::get('/create', 'create')->name('create')->middleware('can:handbook-create');
    Route::get('/create/{handbook_category}', 'create')->name('createHandbookItem')->middleware('can:handbook-create');
    Route::get('/edit/{handbook}', 'edit')->name('edit')->middleware('can:handbook-edit');
    Route::get('/edit/handbookItem/{handbook}', 'editHandbookItem')->name('editHandbookItem')->middleware('can:handbook-edit');
    Route::get('/show/{handbook}', 'show')->name('show')->middleware('can:handbook-show');
    Route::post('/store', 'store')->name('store');
    Route::get('/get-handbook-content/{id}', 'HandbookController@getHandbookContent');
    Route::get('/destroy/{handbook}', 'destroy')->name('destroy')->middleware('can:handbook-delete');
});
