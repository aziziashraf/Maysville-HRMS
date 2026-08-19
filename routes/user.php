<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->prefix('/user')->as('user.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:user-list');
    Route::get('/create', 'create')->name('create')->middleware('can:user-create');
    Route::get('/edit/{user}', 'edit')->name('edit')->middleware('can:user-edit');
    Route::get('/getName/{email}', 'getName')->name('getName');
    Route::post('/store', 'store')->name('store');
    Route::get('/destroy/{user}', 'destroy')->name('destroy')->middleware('can:user-delete');

    Route::get('/role_index', 'role_index')->name('role_index')->middleware('can:role-list');
    Route::get('/role_create', 'role_create')->name('role_create')->middleware('can:role-create');
    Route::get('/role_edit/{role_id}', 'role_edit')->name('role_edit')->middleware('can:role-edit');
    Route::post('/role_store', 'role_store')->name('role_store');
    Route::get('/role_destroy/{role_id}', 'role_destroy')->name('role_destroy')->middleware('can:role-delete');

    
    Route::get('/profile', 'profile')->name('profile');
    Route::post('/saveProfile', 'saveProfile')->name('saveProfile');
    Route::post('/uploadProfilePic', 'uploadProfilePic')->name('uploadProfilePic');
    Route::post('/changePassword', 'changePassword')->name('changePassword');
});
