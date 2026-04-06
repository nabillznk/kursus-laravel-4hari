<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayarController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\JenisZakatController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Laluan Web — Sistem Pengurusan Zakat Kedah
|--------------------------------------------------------------------------
| Fail ini mengandungi semua laluan web untuk Sistem Zakat.
| Salin laluan yang berkenaan ke dalam fail routes/web.php projek anda.
|
*/

// ============================================
// LALUAN ASAS (Hari 1)
// ============================================

Route::get('/', function () {
    return view('welcome');
});

Route::get('/salam', function () {
    return 'Assalamualaikum! Selamat datang ke Sistem Zakat Kedah.';
});

// ============================================
// LALUAN PEMBAYAR — CRUD (Hari 2)
// ============================================

// Resource route — mencipta 7 laluan CRUD secara automatik:
// GET    /pembayar              → PembayarController@index    (Senarai)
// GET    /pembayar/create       → PembayarController@create   (Borang daftar)
// POST   /pembayar              → PembayarController@store    (Simpan)
// GET    /pembayar/{id}         → PembayarController@show     (Lihat satu)
// GET    /pembayar/{id}/edit    → PembayarController@edit     (Borang edit)
// PUT    /pembayar/{id}         → PembayarController@update   (Kemaskini)
// DELETE /pembayar/{id}         → PembayarController@destroy  (Padam)
Route::resource('pembayar', PembayarController::class);

// ============================================
// LALUAN PEMBAYARAN & JENIS ZAKAT (Hari 3-4)
// ============================================

Route::resource('pembayaran', PembayaranController::class);
Route::resource('jenis-zakat', JenisZakatController::class);

// ============================================
// LALUAN DASHBOARD (Hari 4)
// ============================================

// Semua laluan di bawah memerlukan log masuk
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

// ============================================
// LALUAN DENGAN PARAMETER (Contoh Pembelajaran)
// ============================================

// Cari pembayar mengikut No. IC
Route::get('/cari-ic/{no_ic}', function ($no_ic) {
    return "Mencari pembayar dengan IC: {$no_ic}";
});

// Laluan dengan parameter pilihan
Route::get('/laporan/{tahun?}', function ($tahun = null) {
    $tahun = $tahun ?? date('Y');
    return "Laporan Kutipan Zakat Tahun {$tahun}";
})->where('tahun', '[0-9]{4}');

// ============================================
// KUMPULAN LALUAN ADMIN
// ============================================

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return 'Panel Admin — Pusat Zakat Negeri Kedah';
    })->name('home');

    Route::get('/tetapan', function () {
        return 'Tetapan Sistem Zakat';
    })->name('tetapan');
});
