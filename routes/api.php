<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\DeliveryApiController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/token', [AuthTokenController::class, 'store'])->middleware('throttle:10,1');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('/auth/token', [AuthTokenController::class, 'destroy']);
    Route::get('/deliveries/today', [DeliveryApiController::class, 'today'])->middleware('abilities:deliveries:read');
    Route::patch('/deliveries/{delivery}', [DeliveryApiController::class, 'update'])->middleware('abilities:deliveries:update');
    Route::post('/deliveries/{delivery}/payments', [DeliveryApiController::class, 'recordPayment'])->middleware('abilities:payments:create');
});
