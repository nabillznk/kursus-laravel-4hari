<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MaklumatController;
use App\Http\Controllers\PembayarController;
use App\Http\Controllers\SemakController;
use Illuminate\Support\Facades\Route;

// =============================================
// Laluan Auth (Tetamu sahaja)
// =============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/daftar', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// =============================================
// Laluan Dilindungi (Auth Required)
// =============================================
Route::middleware(['auth', 'log.akses'])->group(function () {
    // Halaman utama → senarai pembayar
    Route::get('/', fn() => redirect()->route('pembayar.index'));

    // CRUD Pembayar
    Route::resource('pembayar', PembayarController::class);

    // Semakan sistem
    Route::get('/semak', [SemakController::class, 'index'])->name('semak');

    // Maklumat laluan
    Route::get('/maklumat/laluan', [MaklumatController::class, 'laluan'])->name('maklumat.laluan');

    // Admin dashboard
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    });
});
