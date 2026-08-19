<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(HandBookCategoryController::class)->prefix('/handbook_category')->as('handbook_category.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:handbook_category-index');
    Route::get('/create', 'create')->name('create')->middleware('can:handbook_category-create');
    Route::post('/store', 'store')->name('store')->middleware('can:handbook_category-store');
    Route::get('/show/{handbook_category}', 'show')->name('show')->middleware('can:handbook_category-show');
    Route::get('/edit/{handbook_category}', 'edit')->name('edit')->middleware('can:handbook_category-edit');
    Route::post('/update/{handbook_category}', 'update')->name('update')->middleware('can:handbook_category-update');
    Route::get('/destroy/{handbook_category}', 'destroy')->name('destroy')->middleware('can:handbook_category-destroy');
});
