<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::controller(PurchaseRequisitionController::class)->prefix('/purchase_requisition')->as('purchase_requisition.')->middleware('auth')->group(function() {
    Route::get('/index', 'index')->name('index')->middleware('can:purchase_requisition-index');
    Route::get('/create', 'create')->name('create')->middleware('can:purchase_requisition-create');
    Route::post('/store', 'store')->name('store')->middleware('can:purchase_requisition-store');
    Route::get('/show/{purchaseRequisition}', 'show')->name('show')->middleware('can:purchase_requisition-show');
    Route::get('/edit/{purchaseRequisition}', 'edit')->name('edit')->middleware('can:purchase_requisition-edit');
    Route::post('/update/{purchaseRequisition}', 'update')->name('update')->middleware('can:purchase_requisition-update');
    Route::get('/destroy/{purchaseRequisition}', 'destroy')->name('destroy')->middleware('can:purchase_requisition-destroy');

    Route::get('/createClaim/{purchaseRequisition}', 'createClaim')->name('createClaim')->middleware('can:purchase_requisition-createClaim');
    Route::get('/download/{purchaseRequisition}', 'download')->name('download')->middleware('can:purchase_requisition-download');

    Route::get('/requestIndex', 'requestIndex')->name('requestIndex')->middleware('can:purchase_requisition-requestIndex');
    Route::get('/requestEdit/{purchaseRequisition}', 'requestEdit')->name('requestEdit')->middleware('can:purchase_requisition-requestEdit');
    Route::post('/requestUpdate/{purchaseRequisition}', 'requestUpdate')->name('requestUpdate')->middleware('can:purchase_requisition-requestUpdate');
});
