<?php

use Illuminate\Support\Facades\Route;
use OrderManagement\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Order Management API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the OrderManagementServiceProvider
| and are assigned the configured middleware.
|
*/

Route::prefix(config('order-management.routes.prefix', 'api'))
    ->middleware(config('order-management.routes.middleware', ['api', 'auth:sanctum']))
    ->group(function () {

        // Order CRUD operations
        Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::put('orders/{id}', [OrderController::class, 'update'])->name('orders.update');
        Route::delete('orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

        // Order actions
        Route::post('orders/{id}/approve', [OrderController::class, 'approve'])->name('orders.approve');
        Route::post('orders/{id}/ship', [OrderController::class, 'ship'])->name('orders.ship');
        Route::post('orders/{id}/approve-and-ship', [OrderController::class, 'approveAndShip'])->name('orders.approve-and-ship');
        Route::post('orders/{id}/unapprove', [OrderController::class, 'unapprove'])->name('orders.unapprove');
        Route::post('orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('orders/{id}/restore', [OrderController::class, 'restore'])->name('orders.restore');
        Route::post('orders/{id}/recall-shipment', [OrderController::class, 'recallShipment'])->name('orders.recall-shipment');

        // Order permissions
        Route::get('orders/{id}/permissions', [OrderController::class, 'permissions'])->name('orders.permissions');
    });
