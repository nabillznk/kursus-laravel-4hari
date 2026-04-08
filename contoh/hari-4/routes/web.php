<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisZakatController;
use App\Http\Controllers\PembayarController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PembayaranController;
use Illuminate\Support\Facades\Route;

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/daftar', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('pembayar', PembayarController::class);
    Route::resource('jenis-zakat', JenisZakatController::class);
    Route::resource('pembayaran', PembayaranController::class);

    // Eksport CSV
    Route::get('/eksport/pembayar', [ExportController::class, 'pembayar'])->name('eksport.pembayar');
    Route::get('/eksport/pembayaran', [ExportController::class, 'pembayaran'])->name('eksport.pembayaran');
});
