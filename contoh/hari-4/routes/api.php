<?php

use App\Http\Controllers\Api\PembayarApiController;
use App\Http\Controllers\Api\StatistikApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('pembayar', PembayarApiController::class);
    Route::get('statistik', [StatistikApiController::class, 'index']);
});
