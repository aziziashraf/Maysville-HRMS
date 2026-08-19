<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(ApiController::class)->group(function () {

    Route::post('/app_login', 'app_login')->name('app_login');
    Route::post('/forgot_password', 'forgot_password')->name('forgot_password');
    Route::post('/app_forgotPassword', 'app_forgotPassword')->name('app_forgotPassword');
    Route::post('/createVisitor', 'createVisitor')->name('createVisitor');
    Route::post('/receivedData', 'receivedData')->name('receivedData');
    Route::post('/sendNotificationsss', 'sendNotificationsss')->name('sendNotificationsss')->middleware('client');
    Route::post('/getEmployeeTenant', 'getEmployeeTenant')->name('getEmployeeTenant')->middleware('client');
    Route::get('/getEmployee', 'getEmployee')->name('getEmployee');


    Route::middleware('auth:api')->group(function () {
        Route::post('/saveFirebaseToken', 'saveFirebaseToken')->name('saveFirebaseToken');
        Route::post('/getQRcode', 'getQRcode')->name('getQRcode');
        Route::post('/getClockInHistory', 'getClockInHistory')->name('getClockInHistory');
        Route::post('/getAttendance', 'getAttendance')->name('getAttendance');
        Route::post('/getNotificationByUser', 'getNotificationByUser')->name('getNotificationByUser');
        Route::post('/getNotificationByID', 'getNotificationByID')->name('getNotificationByID');
        Route::post('/submitAttendanceRemark', 'submitAttendanceRemark')->name('submitAttendanceRemark');
        Route::post('/updateProfile', 'updateProfile')->name('updateProfile');
        Route::post('/workAtHomeSubmit', 'workAtHomeSubmit')->name('workAtHomeSubmit');
        Route::post('/workAtHomeStatus', 'workAtHomeStatus')->name('workAtHomeStatus');
        Route::post('/changeNewPassword', 'changeNewPassword')->name('changeNewPassword');
        Route::post('/addNewVisitTime', 'addNewVisitTime')->name('addNewVisitTime');
        Route::post('/getVisitorPass', 'getVisitorPass')->name('getVisitorPass');

        Route::post('/getLeaveType',         'getLeaveType')->name('getLeaveType');
        Route::post('/getLeaveTypeDetail',  'getLeaveTypeDetail')->name('getLeaveTypeDetail');

        Route::post('/storeLeave',   'storeLeave')->name('storeLeave');
        Route::post('/getLeave',     'getLeave')->name('getLeave');
        Route::post('/getLeaveDetail',     'getLeaveDetail')->name('getLeaveDetail');

        Route::post('/getLeaveBalance',     'getLeaveBalance')->name('getLeaveBalance');

        Route::get('/getStatus',     'getStatus')->name('getStatus');

        Route::post('/getCalendar',  'getCalendar')->name('getCalendar');

        Route::post('/approveLeave',  'approveLeave')->name('approveLeave');
        Route::post('/approveLeaveIndex',  'approveLeaveIndex')->name('approveLeaveIndex');
        Route::post('/approveLeaveHistory',  'approveLeaveHistory')->name('approveLeaveHistory');
        Route::post('/uploadProfilePic',  'uploadProfilePic')->name('uploadProfilePic');

        Route::get('/getClaimType',         'getClaimType')->name('getClaimType');
        Route::post('/getClaimTypeDetail',  'getClaimTypeDetail')->name('getClaimTypeDetail');
        Route::get('/getClaimAmountType',     'getClaimAmountType')->name('getClaimAmountType');

        Route::get('/getHandBookCategory',   'getHandBookCategory')->name('getHandBookCategory');

        Route::post('/storeClaim',   'storeClaim')->name('storeClaim');
        Route::post('/getClaim',     'getClaim')->name('getClaim');
        Route::post('/getClaimDetail',     'getClaimDetail')->name('getClaimDetail');

        Route::post('/approveClaim',  'approveClaim')->name('approveClaim');
        Route::post('/approveClaimIndex',  'approveClaimIndex')->name('approveClaimIndex');
        Route::post('/approveClaimHistory',  'approveClaimHistory')->name('approveClaimHistory');

        Route::post('/showAttachment',     'showAttachment')->name('showAttachment');
        Route::post('/destroyAttachment',     'destroyAttachment')->name('destroyAttachment');

        Route::post('/remoteWorkingSubmit', 'remoteWorkingSubmit')->name('remoteWorkingSubmit');
        Route::post('/remoteWorkingStatus', 'remoteWorkingStatus')->name('remoteWorkingStatus');
        Route::post('/remoteWorkingHistory', 'remoteWorkingHistory')->name('remoteWorkingHistory');

        Route::post('/storeOvertime',           'storeOvertime')->name('storeOvertime');
        Route::post('/getOvertime',             'getOvertime')->name('getOvertime');
        Route::post('/getOvertimeDetail',       'getOvertimeDetail')->name('getOvertimeDetail');
        Route::post('/approveOvertimeIndex',    'approveOvertimeIndex')->name('approveOvertimeIndex');
        Route::post('/approveOvertimeHistory',  'approveOvertimeHistory')->name('approveOvertimeHistory');
        Route::post('/approveOvertime',         'approveOvertime')->name('approveOvertime');

        Route::post('/storePurchaseRequisition',   'storePurchaseRequisition')->name('storePurchaseRequisition');
        Route::post('/getPurchaseRequisition',     'getPurchaseRequisition')->name('getPurchaseRequisition');
        Route::post('/getPurchaseRequisitionDetail',     'getPurchaseRequisitionDetail')->name('getPurchaseRequisitionDetail');
        Route::post('/approvePurchaseRequisition',  'approvePurchaseRequisition')->name('approvePurchaseRequisition');
        Route::post('/approvePurchaseRequisitionIndex',  'approvePurchaseRequisitionIndex')->name('approvePurchaseRequisitionIndex');
        Route::post('/approvePurchaseRequisitionHistory',  'approvePurchaseRequisitionHistory')->name('approvePurchaseRequisitionHistory');
    });
});
