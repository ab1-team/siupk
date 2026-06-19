<?php

use App\Http\Controllers\Api\HoldingLaporanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Holding API — untuk aplikasi holding pusat (autentikasi via X-Holding-Token + X-Holding-Tenant).
Route::middleware('holding.license')->prefix('v1/holding')->group(function () {
    Route::get('laporan/neraca',              [HoldingLaporanController::class, 'neraca']);
    Route::get('laporan/laba-rugi',           [HoldingLaporanController::class, 'labaRugi']);
    Route::get('laporan/arus-kas',            [HoldingLaporanController::class, 'arusKas']);
    Route::get('laporan/perubahan-ekuitas',   [HoldingLaporanController::class, 'perubahanEkuitas']);
    Route::get('laporan/calk',                [HoldingLaporanController::class, 'calk']);
});
