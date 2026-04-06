<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PembayarApiController;

/*
|--------------------------------------------------------------------------
| Laluan API — Sistem Pengurusan Zakat Kedah
|--------------------------------------------------------------------------
| Fail ini mengandungi laluan API untuk Sistem Zakat.
| Salin kandungan ini ke dalam fail routes/api.php projek anda.
|
| Semua laluan API secara automatik mendapat prefix /api
| Contoh: /api/pembayar, /api/pembayar/1
|
*/

// Laluan API awam (tanpa pengesahan)
Route::apiResource('pembayar', PembayarApiController::class);

// Laluan API tambahan
Route::get('/statistik', function () {
    return response()->json([
        'jumlah_pembayar'   => \App\Models\Pembayar::count(),
        'jumlah_kutipan'    => \App\Models\Pembayaran::sah()->sum('jumlah'),
        'pembayaran_bulan'  => \App\Models\Pembayaran::sah()
                                ->whereMonth('tarikh_bayar', now()->month)
                                ->sum('jumlah'),
    ]);
});

// Laluan API dengan pengesahan (Sanctum) — untuk produksi
// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('pembayar', PembayarApiController::class);
//     Route::apiResource('pembayaran', PembayaranApiController::class);
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });
// });
