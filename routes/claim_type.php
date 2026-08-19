<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(ClaimTypeController::class)->prefix('/claim_type')->as('claim_type.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:claim_type-index');
    Route::get('/create', 'create')->name('create')->middleware('can:claim_type-create');
    Route::post('/store', 'store')->name('store')->middleware('can:claim_type-store');
    Route::get('/show/{claim_type}', 'show')->name('show')->middleware('can:claim_type-show');
    Route::get('/edit/{claim_type}', 'edit')->name('edit')->middleware('can:claim_type-edit');
    Route::post('/update/{claim_type}', 'update')->name('update')->middleware('can:claim_type-update');
    Route::get('/destroy/{claim_type}', 'destroy')->name('destroy')->middleware('can:claim_type-destroy');
});
