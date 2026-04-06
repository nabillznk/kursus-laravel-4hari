# Hari 2 -- Penghalaan, Pengawal & Middleware (Panduan Bengkel)

> **Sistem Pengurusan Zakat Kedah** -- Kursus Laravel 4 Hari
> Tempoh: 6 jam | Bahasa: Melayu | Tahap: Permulaan

---

## Pengenalan

Pada Hari 1, kita telah mencipta projek `sistem-zakat`, menyediakan pangkalan data `zakat_kedah`, dan membina CRUD asas untuk **Pembayar** (migrasi, model, seeder, dan paparan).

Hari ini kita akan menambah ciri-ciri baharu di atas projek sedia ada:

| Topik | Perkara yang Dipelajari |
|-------|------------------------|
| **Model** | Skop tempatan (local scope) dan aksesor (accessor) |
| **Middleware** | Cipta middleware tersuai untuk log akses dan semak waktu pejabat |
| **Pengawal** | Pengawal baharu untuk semakan sistem dan senarai laluan |
| **Laluan** | Kumpulan laluan, prefix, middleware alias |
| **Paparan** | Halaman semak sistem, senarai laluan, halaman ralat |

Pastikan pelayan pembangunan anda sudah berjalan dan CRUD Pembayar daripada Hari 1 berfungsi sebelum meneruskan.

---

## Langkah 1: Tambah Skop & Aksesor pada Model Pembayar

Buka fail model Pembayar yang telah dicipta pada Hari 1. Kita akan menambah **skop carian** dan **aksesor format** supaya data lebih mudah diurus.

**Fail:** `app/Models/Pembayar.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayar extends Model
{
    use HasFactory;

    /**
     * Medan yang boleh diisi secara mass-assignment.
     */
    protected $fillable = [
        'nama',
        'no_ic',
        'alamat',
        'no_tel',
        'email',
        'pekerjaan',
        'pendapatan_bulanan',
    ];

    /**
     * Penukaran jenis data automatik.
     */
    protected $casts = [
        'pendapatan_bulanan' => 'decimal:2',
    ];

    /**
     * Skop carian -- cari mengikut nama atau no IC.
     */
    public function scopeCarian($query, $carian)
    {
        return $query->where('nama', 'like', "%{$carian}%")
                     ->orWhere('no_ic', 'like', "%{$carian}%");
    }

    /**
     * Aksesor: Format no IC dengan sengkang (850101-14-5678).
     */
    public function getIcFormatAttribute(): string
    {
        $ic = $this->no_ic;

        if (strlen($ic) === 12) {
            return substr($ic, 0, 6) . '-' . substr($ic, 6, 2) . '-' . substr($ic, 8, 4);
        }

        return $ic;
    }

    /**
     * Aksesor: Format pendapatan bulanan (RM 3,500.00).
     */
    public function getPendapatanFormatAttribute(): string
    {
        if ($this->pendapatan_bulanan === null) {
            return 'Tiada maklumat';
        }

        return 'RM ' . number_format($this->pendapatan_bulanan, 2);
    }
}
```

**Penerangan:**

- `scopeCarian($query, $carian)` -- Skop tempatan yang membolehkan carian mengikut nama atau no IC. Penggunaan: `Pembayar::carian('Ahmad')->get()`.
- `getIcFormatAttribute()` -- Aksesor yang memformat no IC 12 digit kepada format bersengkang (contoh: `850101-14-5678`). Penggunaan dalam Blade: `$pembayar->ic_format`.
- `getPendapatanFormatAttribute()` -- Aksesor yang memformat pendapatan dengan simbol RM dan pemisah ribuan. Penggunaan dalam Blade: `$pembayar->pendapatan_format`.

---

## Langkah 2: Cipta Middleware LogAkses

Middleware ini akan merekod setiap permintaan HTTP yang masuk ke dalam fail log khusus.

**Arahan Artisan:**

```bash
php artisan make:middleware LogAkses
```

**Fail:** `app/Http/Middleware/LogAkses.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogAkses
{
    /**
     * Log setiap permintaan masuk: kaedah, URL, IP, dan masa.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::channel('akses')->info('Akses masuk', [
            'kaedah' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'masa' => now()->format('Y-m-d H:i:s'),
        ]);

        return $next($request);
    }
}
```

**Penerangan:**

- Middleware ini menggunakan `Log::channel('akses')` untuk menulis ke fail log berasingan.
- Setiap permintaan akan direkodkan dengan kaedah HTTP (GET/POST/dll.), URL penuh, alamat IP, dan cap masa.
- `$next($request)` memastikan permintaan diteruskan ke lapisan seterusnya.

**Tambah saluran log `akses` dalam konfigurasi.** Buka `config/logging.php` dan tambah saluran baharu di dalam tatasusunan `channels`:

```php
'akses' => [
    'driver' => 'single',
    'path' => storage_path('logs/akses.log'),
    'level' => 'info',
    'replace_placeholders' => true,
],
```

Fail log akan ditulis ke `storage/logs/akses.log`.

---

## Langkah 3: Cipta Middleware SemakWaktuPejabat

Middleware ini akan menyekat akses ke bahagian tertentu jika permintaan dibuat di luar waktu pejabat.

**Arahan Artisan:**

```bash
php artisan make:middleware SemakWaktuPejabat
```

**Fail:** `app/Http/Middleware/SemakWaktuPejabat.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SemakWaktuPejabat
{
    /**
     * Semak sama ada permintaan dibuat dalam waktu pejabat.
     * Waktu pejabat: Isnin-Jumaat, 8:00 pagi - 5:00 petang.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hari = now()->dayOfWeek; // 0 = Ahad, 6 = Sabtu
        $jam = now()->hour;

        // Hari bekerja: Isnin (1) hingga Jumaat (5)
        $hariBekerja = $hari >= 1 && $hari <= 5;

        // Waktu pejabat: 8:00 pagi hingga 5:00 petang
        $waktuPejabat = $jam >= 8 && $jam < 17;

        if (!$hariBekerja || !$waktuPejabat) {
            return response()->view('errors.luar-waktu', [], 403);
        }

        return $next($request);
    }
}
```

**Penerangan:**

- `now()->dayOfWeek` mengembalikan hari dalam minggu (0 = Ahad, 1 = Isnin, ..., 6 = Sabtu).
- `now()->hour` mengembalikan jam semasa (0-23).
- Jika di luar hari bekerja (Isnin-Jumaat) atau di luar jam 8 pagi hingga 5 petang, halaman ralat `errors.luar-waktu` akan dipaparkan dengan kod status 403.
- Jika dalam waktu pejabat, permintaan diteruskan seperti biasa.

---

## Langkah 4: Daftar Middleware dalam bootstrap/app.php

Dalam Laravel 11+, middleware didaftarkan di dalam `bootstrap/app.php` menggunakan alias. Ini membolehkan kita merujuk middleware dengan nama pendek dalam laluan.

**Fail:** `bootstrap/app.php`

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'waktu.pejabat' => \App\Http\Middleware\SemakWaktuPejabat::class,
            'log.akses'     => \App\Http\Middleware\LogAkses::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

**Penerangan:**

- `$middleware->alias([...])` mendaftarkan alias supaya kita boleh guna nama pendek seperti `'log.akses'` dan `'waktu.pejabat'` dalam definisi laluan.
- Selepas ini, kita boleh tulis `Route::middleware(['log.akses'])` dan bukannya nama kelas penuh.

---

## Langkah 5: Cipta SemakController

Pengawal ini memaparkan halaman semakan sistem -- maklumat versi PHP, Laravel, sambungan pangkalan data, dan pelayan web.

**Arahan Artisan:**

```bash
php artisan make:controller SemakController
```

**Fail:** `app/Http/Controllers/SemakController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SemakController extends Controller
{
    /**
     * Papar halaman semakan sistem.
     */
    public function index()
    {
        $semakan = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP Built-in Server',
            'pangkalan_data' => $this->semakPangkalanData(),
        ];

        return view('semak', compact('semakan'));
    }

    /**
     * Semak sambungan pangkalan data.
     */
    private function semakPangkalanData(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => true,
                'nama' => config('database.connections.' . config('database.default') . '.database'),
                'pemacu' => config('database.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mesej' => $e->getMessage(),
            ];
        }
    }
}
```

**Penerangan:**

- `PHP_VERSION` dan `app()->version()` mendapatkan versi PHP dan Laravel.
- `$_SERVER['SERVER_SOFTWARE']` mendapatkan maklumat pelayan web.
- `semakPangkalanData()` cuba menyambung ke pangkalan data menggunakan `DB::connection()->getPdo()`. Jika berjaya, ia mengembalikan nama dan pemacu pangkalan data. Jika gagal, ia mengembalikan mesej ralat.

---

## Langkah 6: Cipta MaklumatController

Pengawal ini memaparkan senarai semua laluan yang didaftarkan dalam aplikasi -- berguna untuk rujukan semasa pembangunan.

**Arahan Artisan:**

```bash
php artisan make:controller MaklumatController
```

**Fail:** `app/Http/Controllers/MaklumatController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

class MaklumatController extends Controller
{
    /**
     * Papar senarai semua laluan berdaftar.
     */
    public function laluan()
    {
        $laluans = collect(Route::getRoutes())->map(function ($route) {
            return [
                'nama' => $route->getName() ?? '-',
                'kaedah' => $route->methods(),
                'uri' => $route->uri(),
                'pengawal' => $route->getActionName(),
            ];
        });

        return view('maklumat.laluan', compact('laluans'));
    }
}
```

**Penerangan:**

- `Route::getRoutes()` mengembalikan semua laluan yang didaftarkan dalam aplikasi.
- Setiap laluan dipetakan kepada tatasusunan yang mengandungi nama, kaedah HTTP, URI, dan nama pengawal.
- Hasilnya dihantar ke paparan `maklumat.laluan` untuk dipaparkan dalam bentuk jadual.

---

## Langkah 7: Kemas Kini Laluan (web.php)

Sekarang kita gabungkan semua komponen baharu ke dalam fail laluan. Fail ini menentukan semua URL yang boleh diakses dalam aplikasi.

**Fail:** `routes/web.php`

```php
<?php

use App\Http\Controllers\MaklumatController;
use App\Http\Controllers\PembayarController;
use App\Http\Controllers\SemakController;
use Illuminate\Support\Facades\Route;

// Halaman utama -- terus ke senarai pembayar
Route::get('/', fn() => redirect()->route('pembayar.index'));

// Semakan sistem
Route::get('/semak', [SemakController::class, 'index'])->name('semak');

// Maklumat laluan -- papar semua laluan berdaftar
Route::get('/maklumat/laluan', [MaklumatController::class, 'laluan'])->name('maklumat.laluan');

// Kumpulan laluan dengan middleware log akses
Route::middleware(['log.akses'])->group(function () {
    Route::resource('pembayar', PembayarController::class);
});

// Contoh kumpulan laluan dengan prefix (demonstrasi konsep)
Route::prefix('admin')->name('admin.')->middleware(['log.akses'])->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
});
```

**Penerangan baris demi baris:**

- **Baris 9:** Halaman utama (`/`) mengalihkan pengguna terus ke senarai pembayar menggunakan `redirect()`.
- **Baris 12:** Laluan `/semak` dipetakan ke `SemakController@index` untuk halaman semakan sistem.
- **Baris 15:** Laluan `/maklumat/laluan` dipetakan ke `MaklumatController@laluan` untuk melihat semua laluan berdaftar.
- **Baris 18-20:** `Route::middleware(['log.akses'])->group(...)` mengelompokkan laluan CRUD Pembayar supaya setiap permintaan dilog secara automatik. `Route::resource()` mencipta 7 laluan CRUD secara automatik (index, create, store, show, edit, update, destroy).
- **Baris 23-25:** `Route::prefix('admin')` menambah awalan `/admin` pada semua laluan dalam kumpulan. `name('admin.')` menambah awalan `admin.` pada nama laluan. Dashboard pentadbir di `/admin/dashboard` menggunakan middleware `log.akses` juga.

### Jadual Laluan yang Dihasilkan

| Kaedah | URI | Nama | Pengawal |
|--------|-----|------|----------|
| GET | `/` | - | Alih ke pembayar.index |
| GET | `/pembayar` | pembayar.index | PembayarController@index |
| GET | `/pembayar/create` | pembayar.create | PembayarController@create |
| POST | `/pembayar` | pembayar.store | PembayarController@store |
| GET | `/pembayar/{pembayar}` | pembayar.show | PembayarController@show |
| GET | `/pembayar/{pembayar}/edit` | pembayar.edit | PembayarController@edit |
| PUT/PATCH | `/pembayar/{pembayar}` | pembayar.update | PembayarController@update |
| DELETE | `/pembayar/{pembayar}` | pembayar.destroy | PembayarController@destroy |
| GET | `/semak` | semak | SemakController@index |
| GET | `/maklumat/laluan` | maklumat.laluan | MaklumatController@laluan |
| GET | `/admin/dashboard` | admin.dashboard | Closure (view) |

---

## Langkah 8: Cipta Paparan Semak Sistem

Paparan ini memaparkan maklumat semakan sistem dalam bentuk kad grid.

**Fail:** `resources/views/semak.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Semakan Sistem')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Semakan Sistem</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- PHP --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-emerald-700 mb-3">Versi PHP</h2>
            <p class="text-3xl font-bold text-gray-800">{{ $semakan['php_version'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Versi PHP yang sedang berjalan</p>
        </div>

        {{-- Laravel --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-emerald-700 mb-3">Versi Laravel</h2>
            <p class="text-3xl font-bold text-gray-800">{{ $semakan['laravel_version'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Versi rangka kerja Laravel</p>
        </div>

        {{-- Pangkalan Data --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-emerald-700 mb-3">Pangkalan Data</h2>
            @if ($semakan['pangkalan_data']['status'])
                <p class="text-xl font-bold text-emerald-600">Bersambung</p>
                <p class="text-sm text-gray-500 mt-1">
                    Pangkalan data: <strong>{{ $semakan['pangkalan_data']['nama'] }}</strong>
                    ({{ $semakan['pangkalan_data']['pemacu'] }})
                </p>
            @else
                <p class="text-xl font-bold text-red-600">Tidak Bersambung</p>
                <p class="text-sm text-red-500 mt-1">{{ $semakan['pangkalan_data']['mesej'] ?? 'Ralat tidak diketahui' }}</p>
            @endif
        </div>

        {{-- Pelayan --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-emerald-700 mb-3">Pelayan Web</h2>
            <p class="text-xl font-bold text-gray-800">{{ $semakan['server'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Pelayan web yang sedang berjalan</p>
        </div>
    </div>
@endsection
```

**Penerangan:**

- Paparan ini menggunakan `@extends('layouts.app')` untuk mewarisi susun atur utama.
- Empat kad grid memaparkan: versi PHP, versi Laravel, status pangkalan data, dan pelayan web.
- Arahan `@if` digunakan untuk memaparkan status sambungan pangkalan data -- hijau jika bersambung, merah jika gagal.

---

## Langkah 9: Cipta Paparan Senarai Laluan

Paparan ini memaparkan semua laluan berdaftar dalam bentuk jadual dengan warna mengikut kaedah HTTP.

Cipta direktori `maklumat` terlebih dahulu:

```bash
mkdir -p resources/views/maklumat
```

**Fail:** `resources/views/maklumat/laluan.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Senarai Laluan Berdaftar')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Senarai Laluan Berdaftar</h1>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Kaedah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">URI</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Pengawal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($laluans as $laluan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $laluan['nama'] }}</td>
                            <td class="px-4 py-3 text-sm">
                                @foreach ($laluan['kaedah'] as $kaedah)
                                    @php
                                        $warna = match($kaedah) {
                                            'GET' => 'bg-emerald-100 text-emerald-800',
                                            'POST' => 'bg-blue-100 text-blue-800',
                                            'PUT', 'PATCH' => 'bg-amber-100 text-amber-800',
                                            'DELETE' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold {{ $warna }}">
                                        {{ $kaedah }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">/{{ $laluan['uri'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 font-mono text-xs">{{ $laluan['pengawal'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="mt-4 text-sm text-gray-500">Jumlah laluan: {{ count($laluans) }}</p>
@endsection
```

**Penerangan:**

- Jadual menyenaraikan semua laluan dengan nama, kaedah HTTP, URI, dan pengawal.
- Kaedah HTTP diwarnakan mengikut jenis: GET (hijau), POST (biru), PUT/PATCH (kuning), DELETE (merah).
- `match()` digunakan untuk menentukan kelas warna Tailwind CSS mengikut kaedah HTTP.
- Jumlah laluan dipaparkan di bawah jadual.

---

## Langkah 10: Cipta Halaman Ralat

Cipta tiga halaman ralat tersuai: luar waktu pejabat (digunakan oleh middleware SemakWaktuPejabat), 404, dan 403.

Cipta direktori `errors` terlebih dahulu:

```bash
mkdir -p resources/views/errors
```

### 10a. Halaman Luar Waktu Pejabat

Halaman ini dipaparkan apabila middleware `SemakWaktuPejabat` menyekat akses.

**Fail:** `resources/views/errors/luar-waktu.blade.php`

```blade
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luar Waktu Pejabat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-emerald-50 min-h-screen flex items-center justify-center">
    <div class="max-w-lg mx-auto text-center px-6">
        <div class="bg-white rounded-2xl shadow-lg p-10">
            <div class="text-6xl mb-4">&#128336;</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Luar Waktu Pejabat</h1>
            <p class="text-gray-600 mb-6">
                Sistem hanya boleh diakses pada waktu pejabat:<br>
                <strong>Isnin - Jumaat, 8:00 pagi - 5:00 petang</strong>
            </p>
            <div class="bg-emerald-50 rounded-lg p-4 text-sm text-emerald-700">
                <p>Sila cuba lagi dalam waktu pejabat.</p>
                <p class="mt-1">Untuk urusan segera, sila hubungi pentadbir sistem.</p>
            </div>
        </div>
        <p class="text-sm text-gray-400 mt-6">Pusat Zakat Negeri Kedah &copy; 2024</p>
    </div>
</body>
</html>
```

### 10b. Halaman 404 (Tidak Dijumpai)

**Fail:** `resources/views/errors/404.blade.php`

```blade
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Dijumpai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-lg mx-auto text-center px-6">
        <div class="bg-white rounded-2xl shadow-lg p-10">
            <p class="text-8xl font-bold text-emerald-200 mb-2">404</p>
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Halaman Tidak Dijumpai</h1>
            <p class="text-gray-600 mb-6">
                Maaf, halaman yang anda cari tidak wujud atau telah dialihkan.
            </p>
            <a href="/"
               class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-6 rounded-lg transition">
                Kembali ke Laman Utama
            </a>
        </div>
        <p class="text-sm text-gray-400 mt-6">Pusat Zakat Negeri Kedah &copy; 2024</p>
    </div>
</body>
</html>
```

### 10c. Halaman 403 (Akses Ditolak)

**Fail:** `resources/views/errors/403.blade.php`

```blade
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-lg mx-auto text-center px-6">
        <div class="bg-white rounded-2xl shadow-lg p-10">
            <p class="text-8xl font-bold text-red-200 mb-2">403</p>
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Akses Ditolak</h1>
            <p class="text-gray-600 mb-6">
                Maaf, anda tidak mempunyai kebenaran untuk mengakses halaman ini.
            </p>
            <a href="/"
               class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-6 rounded-lg transition">
                Kembali ke Laman Utama
            </a>
        </div>
        <p class="text-sm text-gray-400 mt-6">Pusat Zakat Negeri Kedah &copy; 2024</p>
    </div>
</body>
</html>
```

**Penerangan:**

- Ketiga-tiga halaman ralat menggunakan susun atur kendiri (tidak `@extends('layouts.app')`) supaya ia boleh dipaparkan walaupun susun atur utama bermasalah.
- Setiap halaman memuatkan Tailwind CSS secara langsung melalui CDN.
- Halaman `luar-waktu` digunakan secara khusus oleh middleware `SemakWaktuPejabat`.
- Halaman `404` dan `403` adalah halaman ralat standard Laravel yang boleh dipaparkan secara automatik.

---

## Langkah 11: Kemas Kini Navigasi

Kemas kini susun atur utama supaya bar navigasi mempunyai pautan ke semua halaman baharu.

**Fail:** `resources/views/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Zakat Kedah')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    {{-- Bar navigasi --}}
    <nav class="bg-emerald-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('pembayar.index') }}" class="text-white font-bold text-xl">
                        Sistem Zakat Kedah
                    </a>
                </div>
                <div class="flex items-center space-x-1">
                    <a href="{{ route('pembayar.index') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pembayar.*') ? 'bg-emerald-900 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Pembayar
                    </a>
                    <a href="{{ route('maklumat.laluan') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('maklumat.*') ? 'bg-emerald-900 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Maklumat Laluan
                    </a>
                    <a href="{{ route('semak') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('semak') ? 'bg-emerald-900 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Semak Sistem
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Mesej kilat --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Kandungan utama --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    {{-- Kaki halaman --}}
    <footer class="bg-emerald-800 text-emerald-100 text-center py-4 mt-8">
        <p class="text-sm">Pusat Zakat Negeri Kedah &copy; 2024</p>
    </footer>

</body>
</html>
```

**Penerangan:**

- Bar navigasi mengandungi tiga pautan: **Pembayar**, **Maklumat Laluan**, dan **Semak Sistem**.
- `request()->routeIs('pembayar.*')` digunakan untuk menyerlahkan pautan aktif -- pautan semasa akan mempunyai latar belakang gelap (`bg-emerald-900`).
- Bahagian mesej kilat (`session('success')` dan `session('error')`) memaparkan mesej maklum balas selepas operasi CRUD.
- `@yield('content')` adalah tempat kandungan setiap halaman akan dimasukkan.

---

## Langkah 12: Uji Aplikasi

Sekarang kita uji semua ciri baharu yang telah ditambah.

### 12a. Mulakan pelayan pembangunan

```bash
php artisan serve
```

Atau jika menggunakan Laragon, akses melalui `http://sistem-zakat.test`.

### 12b. Uji setiap URL

Buka pelayar web dan uji URL berikut satu per satu:

| URL | Jangkaan |
|-----|----------|
| `http://localhost:8000/` | Dialihkan ke senarai pembayar |
| `http://localhost:8000/pembayar` | Senarai pembayar dengan fungsi carian |
| `http://localhost:8000/pembayar/create` | Borang daftar pembayar baru |
| `http://localhost:8000/semak` | Halaman semakan sistem (versi PHP, Laravel, pangkalan data) |
| `http://localhost:8000/maklumat/laluan` | Jadual semua laluan berdaftar |
| `http://localhost:8000/admin/dashboard` | Dashboard pentadbir dengan kiraan pembayar |
| `http://localhost:8000/halaman-tiada` | Halaman ralat 404 |

### 12c. Semak fail log

Selepas mengakses beberapa halaman Pembayar, semak fail log akses:

```bash
cat storage/logs/akses.log
```

Anda sepatutnya melihat entri log seperti ini:

```
[2024-01-15 10:30:45] local.INFO: Akses masuk {"kaedah":"GET","url":"http://localhost:8000/pembayar","ip":"127.0.0.1","masa":"2024-01-15 10:30:45"}
```

### 12d. Uji skop carian

Pada halaman senarai pembayar (`/pembayar`), gunakan kotak carian untuk mencari pembayar mengikut nama atau no IC. Skop `scopeCarian` yang ditambah pada Langkah 1 akan digunakan secara automatik.

### 12e. Uji aksesor

Pada halaman butiran pembayar (`/pembayar/{id}`), pastikan:
- No IC dipaparkan dengan sengkang (contoh: `850101-14-5678`) menggunakan `$pembayar->ic_format`
- Pendapatan dipaparkan dengan format RM (contoh: `RM 3,500.00`) menggunakan `$pembayar->pendapatan_format`

### 12f. Senarai laluan melalui Artisan

Anda juga boleh melihat senarai laluan melalui terminal:

```bash
php artisan route:list
```

---

## Struktur Fail Akhir Hari 2

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── MaklumatController.php
│   │   ├── PembayarController.php
│   │   └── SemakController.php
│   └── Middleware/
│       ├── LogAkses.php
│       └── SemakWaktuPejabat.php
├── Models/
│   └── Pembayar.php
bootstrap/
│   └── app.php              ← middleware alias didaftarkan di sini
config/
│   └── logging.php           ← saluran 'akses' ditambah di sini
routes/
│   └── web.php
resources/views/
├── layouts/
│   └── app.blade.php         ← navigasi dikemaskini
├── pembayar/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
│   └── edit.blade.php
├── maklumat/
│   └── laluan.blade.php      ← baharu
├── admin/
│   └── dashboard.blade.php   ← baharu
├── semak.blade.php            ← baharu
└── errors/
    ├── luar-waktu.blade.php   ← baharu
    ├── 404.blade.php          ← baharu
    └── 403.blade.php          ← baharu
storage/logs/
    └── akses.log              ← dijana secara automatik
```

---

## Rumusan Hari 2

Pada hari ini, kita telah belajar:

1. **Skop & Aksesor** -- Menambah `scopeCarian` untuk carian, dan aksesor `ic_format` serta `pendapatan_format` untuk memformat data secara automatik.
2. **Middleware LogAkses** -- Merekod setiap permintaan HTTP ke fail log berasingan (`storage/logs/akses.log`).
3. **Middleware SemakWaktuPejabat** -- Menyekat akses di luar waktu pejabat (Isnin-Jumaat, 8am-5pm).
4. **Pendaftaran Middleware** -- Mendaftarkan alias middleware dalam `bootstrap/app.php` (cara Laravel 11+).
5. **Pengawal Baharu** -- `SemakController` untuk semakan sistem dan `MaklumatController` untuk senarai laluan.
6. **Kumpulan Laluan** -- Menggunakan `Route::middleware()`, `Route::prefix()`, dan `Route::name()` untuk mengorganisasi laluan.
7. **Paparan Baharu** -- Halaman semak sistem, senarai laluan, dan halaman ralat tersuai.

Esok pada **Hari 3**, kita akan mendalami Blade templat, hubungan Eloquent (relationships), migrasi untuk `JenisZakat` dan `Pembayaran`, serta pengesahan borang (validation) yang lebih mendalam.
