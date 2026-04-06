<?php

use App\Http\Controllers\PembayarController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Laluan Web (Web Routes)
|--------------------------------------------------------------------------
|
| Laluan utama untuk Sistem Pengurusan Zakat Kedah.
| Mengandungi laluan untuk laman utama, semakan sistem, dan CRUD pembayar.
|
*/

// Laman utama — hala ke senarai pembayar
Route::get('/', function () {
    return redirect()->route('pembayar.index');
});

// Semakan sistem — papar maklumat persekitaran
Route::get('/semak', function () {
    // Semak sambungan pangkalan data
    $pangkalan_data_ok = false;
    $pangkalan_data_mesej = '';

    try {
        DB::connection()->getPdo();
        $pangkalan_data_ok = true;
        $pangkalan_data_mesej = 'Berjaya disambung ke pangkalan data "' . DB::connection()->getDatabaseName() . '"';
    } catch (\Exception $e) {
        $pangkalan_data_mesej = 'Gagal: ' . $e->getMessage();
    }

    return view('semak', [
        'versi_php' => PHP_VERSION,
        'versi_laravel' => app()->version(),
        'pangkalan_data_ok' => $pangkalan_data_ok,
        'pangkalan_data_mesej' => $pangkalan_data_mesej,
        'pelayan' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP Built-in Server',
        'persekitaran' => app()->environment(),
    ]);
})->name('semak');

// Laluan CRUD untuk pembayar — jana 7 laluan secara automatik
Route::resource('pembayar', PembayarController::class);
