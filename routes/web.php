<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Silber\Bouncer\Bouncer;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', function () {
        $bouncer = app(Bouncer::class);
        if ($bouncer->is(Auth::user())->an('superadmin') || $bouncer->is(Auth::user())->an('management')) {
            return redirect()->route('managementIndex');
        } else {
            return redirect()->route('index');
        }
    })->name('home');

    Route::get('/', function () {
        return redirect()->route('home');
    });
});



Auth::routes();

Route::get('/index', [App\Http\Controllers\HomeController::class, 'index'])->name('index')->middleware('menu.access');
Route::get('/managementIndex', [App\Http\Controllers\HomeController::class, 'managementIndex'])->name('managementIndex')->middleware('menu.access');
Route::get('/calendar', [App\Http\Controllers\HomeController::class, 'calendar'])->name('calendar')->middleware('menu.access');
Route::get('/attendance', [App\Http\Controllers\HomeController::class, 'attendance'])->name('attendance')->middleware('menu.access');
Route::get('/daily_scan', [App\Http\Controllers\HomeController::class, 'daily_scan'])->name('daily_scan')->middleware('menu.access');

Route::get('forgot_pass_get', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forgot_pass_get');
Route::post('/forgot_pass_post', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forgot_pass_post'); 
Route::get('reset-password/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset_password_post', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset_password_post');


Route::get('/sendData', 'ReportController@sendData')->name('sendData');