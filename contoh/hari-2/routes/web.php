<?php

use App\Http\Controllers\MaklumatController;
use App\Http\Controllers\PembayarController;
use App\Http\Controllers\SemakController;
use Illuminate\Support\Facades\Route;

// Halaman utama — terus ke senarai pembayar
Route::get('/', fn() => redirect()->route('pembayar.index'));

// Semakan sistem
Route::get('/semak', [SemakController::class, 'index'])->name('semak');

// Maklumat laluan — papar semua laluan berdaftar
Route::get('/maklumat/laluan', [MaklumatController::class, 'laluan'])->name('maklumat.laluan');

// Kumpulan laluan dengan middleware log akses
Route::middleware(['log.akses'])->group(function () {
    Route::resource('pembayar', PembayarController::class);
});

// Contoh kumpulan laluan dengan prefix (demonstrasi konsep)
Route::prefix('admin')->name('admin.')->middleware(['log.akses'])->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
});
