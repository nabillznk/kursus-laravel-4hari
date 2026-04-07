# Hari 2 — Penghalaan, Pengawal & Middleware

> Sistem Pengurusan Zakat Kedah — Kursus Laravel 4 Hari
> Tempoh: 6 jam | Prasyarat: Hari 1 selesai

---

## Pengenalan

Hari 2 memberi fokus kepada:

| # | Topik | Perkara |
|---|-------|---------|
| 1 | Model Lanjutan | Skop, aksesor, hubungan model (hasMany, belongsTo) |
| 2 | Middleware | Cipta middleware tersuai, daftar, guna |
| 3 | Pengawal (Controllers) | Resource controller, pengawal tambahan |
| 4 | Penghalaan (Routes) | Route::resource, Route::group, prefix, named routes |

Prasyarat: Projek dari Hari 1 sudah siap (model Pembayar, migrasi, seeder, PembayarController, dan paparan CRUD pembayar berfungsi).

---

## Bahagian A: Model Lanjutan

### Langkah 1: Tambah Skop & Aksesor pada Model Pembayar

Buka fail `app/Models/Pembayar.php` yang telah dicipta pada Hari 1. Tambah kod berikut di dalam kelas `Pembayar`:

```php
/**
 * Skop carian — cari mengikut nama atau no IC.
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
```

**Penjelasan:**

| Jenis | Nama | Fungsi |
|-------|------|--------|
| Skop | `scopeCarian` | Membolehkan `Pembayar::carian('Ahmad')` — cari mengikut nama atau no IC |
| Aksesor | `getIcFormatAttribute` | `$pembayar->ic_format` mengembalikan `850101-14-5678` |
| Aksesor | `getPendapatanFormatAttribute` | `$pembayar->pendapatan_format` mengembalikan `RM 3,500.00` |

> **Nota:** Skop dipanggil tanpa perkataan `scope` — contoh: `scopeCarian` dipanggil sebagai `->carian()`. Aksesor dipanggil tanpa `get` dan `Attribute` — contoh: `getIcFormatAttribute` dipanggil sebagai `->ic_format`.

---

### Langkah 2: Cipta Model JenisZakat & Pembayaran

Jalankan arahan berikut dalam terminal:

```bash
php artisan make:model JenisZakat -m
php artisan make:model Pembayaran -m
```

#### Migrasi JenisZakat — `database/migrations/xxxx_create_jenis_zakats_table.php`

```php
public function up(): void
{
    Schema::create('jenis_zakats', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->decimal('kadar', 8, 4);
        $table->text('penerangan')->nullable();
        $table->boolean('is_aktif')->default(true);
        $table->timestamps();
    });
}
```

#### Migrasi Pembayaran — `database/migrations/xxxx_create_pembayarans_table.php`

```php
public function up(): void
{
    Schema::create('pembayarans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pembayar_id')->constrained()->cascadeOnDelete();
        $table->foreignId('jenis_zakat_id')->constrained()->cascadeOnDelete();
        $table->decimal('jumlah', 12, 2);
        $table->date('tarikh_bayar');
        $table->enum('cara_bayar', ['tunai', 'kad', 'fpx', 'online']);
        $table->string('no_resit')->unique();
        $table->enum('status', ['pending', 'sah', 'batal'])->default('pending');
        $table->timestamps();
    });
}
```

**Perhatikan `foreignId(...)->constrained()`** — ini mencipta foreign key yang merujuk kepada jadual lain secara automatik. `cascadeOnDelete()` bermaksud jika pembayar dipadam, semua pembayarannya turut dipadam.

#### Model JenisZakat — `app/Models/JenisZakat.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisZakat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kadar',
        'penerangan',
        'is_aktif',
    ];

    protected $casts = [
        'kadar'    => 'decimal:4',
        'is_aktif' => 'boolean',
    ];

    // Hubungan: Satu jenis zakat mempunyai banyak pembayaran
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    // Skop: Hanya jenis zakat yang aktif
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }
}
```

#### Model Pembayaran — `app/Models/Pembayaran.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'pembayar_id',
        'jenis_zakat_id',
        'jumlah',
        'tarikh_bayar',
        'cara_bayar',
        'no_resit',
        'status',
    ];

    protected $casts = [
        'jumlah'       => 'decimal:2',
        'tarikh_bayar' => 'date',
    ];

    // Hubungan: Pembayaran ini milik seorang pembayar
    public function pembayar(): BelongsTo
    {
        return $this->belongsTo(Pembayar::class);
    }

    // Hubungan: Pembayaran ini milik satu jenis zakat
    public function jenisZakat(): BelongsTo
    {
        return $this->belongsTo(JenisZakat::class);
    }

    // Aksesor: Format jumlah dengan RM (RM 150.00)
    public function getJumlahFormatAttribute(): string
    {
        return 'RM ' . number_format($this->jumlah, 2);
    }

    // Skop: Hanya pembayaran yang sah
    public function scopeSah($query)
    {
        return $query->where('status', 'sah');
    }
}
```

Kemudian tambah hubungan `hasMany` di dalam model `Pembayar` yang sedia ada:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

// Tambah di dalam kelas Pembayar
public function pembayarans(): HasMany
{
    return $this->hasMany(Pembayaran::class);
}

/**
 * Aksesor: Jumlah keseluruhan bayaran sah pembayar.
 */
public function getJumlahBayaranAttribute(): float
{
    return (float) $this->pembayarans()->where('status', 'sah')->sum('jumlah');
}
```

---

### Langkah 3: Memahami Hubungan Model

Tiga model dalam sistem ini dihubungkan seperti berikut:

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│   Pembayar   │──1:M──│  Pembayaran  │──M:1──│  JenisZakat  │
│              │       │              │       │              │
│ hasMany      │       │ belongsTo    │       │ hasMany      │
│ pembayarans()│       │ pembayar()   │       │ pembayarans()│
│              │       │ jenisZakat() │       │              │
└──────────────┘       └──────────────┘       └──────────────┘
```

#### Jadual Hubungan

| Model | Kaedah | Jenis | Penerangan |
|-------|--------|-------|------------|
| `Pembayar` | `pembayarans()` | `hasMany` | Seorang pembayar mempunyai banyak pembayaran |
| `JenisZakat` | `pembayarans()` | `hasMany` | Satu jenis zakat mempunyai banyak pembayaran |
| `Pembayaran` | `pembayar()` | `belongsTo` | Setiap pembayaran milik seorang pembayar |
| `Pembayaran` | `jenisZakat()` | `belongsTo` | Setiap pembayaran milik satu jenis zakat |

#### Bila Guna `hasMany` vs `belongsTo`?

| Soalan | Jawapan | Hubungan |
|--------|---------|----------|
| Model ini **mempunyai** banyak rekod lain? | Ya | `hasMany` |
| Model ini **milik** satu rekod lain? | Ya | `belongsTo` |
| Jadual ini ada lajur `xxx_id`? | Ya — guna `belongsTo` | Foreign key di jadual ini |
| Jadual ini **tiada** lajur `xxx_id`? | Ya — guna `hasMany` | Foreign key di jadual lawan |

#### Masalah N+1 Query

Apabila kita memuatkan senarai pembayar dan setiap pembayaran mereka:

```php
// BURUK — N+1 query (1 query untuk pembayar + N query untuk setiap pembayaran)
$pembayars = Pembayar::all();
foreach ($pembayars as $p) {
    echo $p->pembayarans->count(); // Setiap iterasi buat 1 query baru
}

// BAIK — Eager loading (hanya 2 query)
$pembayars = Pembayar::with('pembayarans')->get();
foreach ($pembayars as $p) {
    echo $p->pembayarans->count(); // Tiada query tambahan
}

// Eager load bersarang — muatkan pembayaran DAN jenis zakat sekaligus
$pembayar = Pembayar::with('pembayarans.jenisZakat')->find(1);
```

> **Tip:** Dalam `PembayarController@show`, kita gunakan `$pembayar->load('pembayarans.jenisZakat')` untuk eager load selepas model sudah dimuatkan.

---

### Langkah 4: Cipta Seeder untuk Data Ujian

#### JenisZakatSeeder — `database/seeders/JenisZakatSeeder.php`

```bash
php artisan make:seeder JenisZakatSeeder
```

```php
<?php

namespace Database\Seeders;

use App\Models\JenisZakat;
use Illuminate\Database\Seeder;

class JenisZakatSeeder extends Seeder
{
    public function run(): void
    {
        $jenisZakats = [
            [
                'nama'       => 'Zakat Fitrah',
                'kadar'      => 7.0000,
                'penerangan' => 'Zakat yang wajib dibayar oleh setiap individu Muslim pada bulan Ramadan.',
                'is_aktif'   => true,
            ],
            [
                'nama'       => 'Zakat Pendapatan',
                'kadar'      => 2.5000,
                'penerangan' => 'Zakat yang dikenakan ke atas pendapatan penggajian yang telah mencapai nisab.',
                'is_aktif'   => true,
            ],
            [
                'nama'       => 'Zakat Perniagaan',
                'kadar'      => 2.5000,
                'penerangan' => 'Zakat yang dikenakan ke atas harta perniagaan yang telah cukup haul dan nisab.',
                'is_aktif'   => true,
            ],
            [
                'nama'       => 'Zakat Wang Simpanan',
                'kadar'      => 2.5000,
                'penerangan' => 'Zakat yang dikenakan ke atas wang simpanan yang telah cukup haul dan mencapai nisab.',
                'is_aktif'   => true,
            ],
            [
                'nama'       => 'Zakat Emas',
                'kadar'      => 2.5000,
                'penerangan' => 'Zakat yang dikenakan ke atas emas yang disimpan yang telah cukup haul dan nisab.',
                'is_aktif'   => true,
            ],
        ];

        foreach ($jenisZakats as $jenis) {
            JenisZakat::create($jenis);
        }
    }
}
```

#### PembayaranSeeder — `database/seeders/PembayaranSeeder.php`

```bash
php artisan make:seeder PembayaranSeeder
```

```php
<?php

namespace Database\Seeders;

use App\Models\Pembayaran;
use Illuminate\Database\Seeder;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $pembayarans = [
            [
                'pembayar_id'    => 1,
                'jenis_zakat_id' => 1,
                'jumlah'         => 7.00,
                'tarikh_bayar'   => '2024-03-28',
                'cara_bayar'     => 'tunai',
                'no_resit'       => 'ZK-2024-0001',
                'status'         => 'sah',
            ],
            [
                'pembayar_id'    => 1,
                'jenis_zakat_id' => 2,
                'jumlah'         => 1350.00,
                'tarikh_bayar'   => '2024-06-15',
                'cara_bayar'     => 'fpx',
                'no_resit'       => 'ZK-2024-0002',
                'status'         => 'sah',
            ],
            [
                'pembayar_id'    => 2,
                'jenis_zakat_id' => 1,
                'jumlah'         => 7.00,
                'tarikh_bayar'   => '2024-03-29',
                'cara_bayar'     => 'tunai',
                'no_resit'       => 'ZK-2024-0004',
                'status'         => 'sah',
            ],
            [
                'pembayar_id'    => 3,
                'jenis_zakat_id' => 2,
                'jumlah'         => 1950.00,
                'tarikh_bayar'   => '2024-05-20',
                'cara_bayar'     => 'fpx',
                'no_resit'       => 'ZK-2024-0007',
                'status'         => 'sah',
            ],
            [
                'pembayar_id'    => 5,
                'jenis_zakat_id' => 3,
                'jumlah'         => 4500.00,
                'tarikh_bayar'   => '2024-06-01',
                'cara_bayar'     => 'fpx',
                'no_resit'       => 'ZK-2024-0010',
                'status'         => 'sah',
            ],
        ];

        foreach ($pembayarans as $pembayaran) {
            Pembayaran::create($pembayaran);
        }
    }
}
```

#### Kemaskini DatabaseSeeder — `database/seeders/DatabaseSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PembayarSeeder::class,
            JenisZakatSeeder::class,
            PembayaranSeeder::class,
        ]);
    }
}
```

---

## Bahagian B: Middleware

Middleware memproses permintaan HTTP **sebelum** ia sampai ke pengawal. Ia digunakan untuk tugasan seperti pengesahan, logging, dan kawalan akses.

```
Permintaan → Middleware 1 → Middleware 2 → Pengawal → Respons
```

### Langkah 5: Cipta Middleware LogAkses

```bash
php artisan make:middleware LogAkses
```

Buka `app/Http/Middleware/LogAkses.php` dan gantikan dengan:

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

**Penjelasan:**
- `$request->method()` — kaedah HTTP (GET, POST, dll.)
- `$request->fullUrl()` — URL penuh termasuk query string
- `$request->ip()` — alamat IP pelawat
- `$next($request)` — teruskan permintaan ke middleware/pengawal seterusnya
- `Log::channel('akses')` — log ke saluran `akses` (boleh dikonfigurasi dalam `config/logging.php`)

---

### Langkah 6: Cipta Middleware SemakWaktuPejabat

```bash
php artisan make:middleware SemakWaktuPejabat
```

Buka `app/Http/Middleware/SemakWaktuPejabat.php` dan gantikan dengan:

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

**Penjelasan:**
- Middleware ini menyekat akses di luar waktu pejabat
- Jika di luar waktu, ia mengembalikan paparan `errors.luar-waktu` dengan kod status 403
- Jika dalam waktu pejabat, permintaan diteruskan seperti biasa

---

### Langkah 7: Daftar Middleware

Buka `bootstrap/app.php` dan daftarkan kedua-dua middleware sebagai alias:

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

**Nota:** Dalam Laravel 11, middleware didaftarkan dalam `bootstrap/app.php` menggunakan `$middleware->alias()`. Alias membolehkan kita merujuk middleware menggunakan nama pendek dalam laluan.

---

## Bahagian C: Pengawal & Penghalaan

### Langkah 8: Cipta SemakController

```bash
php artisan make:controller SemakController
```

Buka `app/Http/Controllers/SemakController.php` dan gantikan dengan:

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

---

### Langkah 9: Cipta MaklumatController

```bash
php artisan make:controller MaklumatController
```

Buka `app/Http/Controllers/MaklumatController.php` dan gantikan dengan:

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

---

### Langkah 10: Tetapkan Laluan

Buka `routes/web.php` dan gantikan keseluruhan kandungan dengan:

```php
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

// Contoh kumpulan laluan dengan prefix
Route::prefix('admin')->name('admin.')->middleware(['log.akses'])->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
});
```

#### Jadual Laluan Yang Dihasilkan

| Kaedah | URI | Nama | Pengawal |
|--------|-----|------|----------|
| GET | `/` | - | Redirect ke `pembayar.index` |
| GET | `/pembayar` | `pembayar.index` | `PembayarController@index` |
| GET | `/pembayar/create` | `pembayar.create` | `PembayarController@create` |
| POST | `/pembayar` | `pembayar.store` | `PembayarController@store` |
| GET | `/pembayar/{pembayar}` | `pembayar.show` | `PembayarController@show` |
| GET | `/pembayar/{pembayar}/edit` | `pembayar.edit` | `PembayarController@edit` |
| PUT/PATCH | `/pembayar/{pembayar}` | `pembayar.update` | `PembayarController@update` |
| DELETE | `/pembayar/{pembayar}` | `pembayar.destroy` | `PembayarController@destroy` |
| GET | `/semak` | `semak` | `SemakController@index` |
| GET | `/maklumat/laluan` | `maklumat.laluan` | `MaklumatController@laluan` |
| GET | `/admin/dashboard` | `admin.dashboard` | Closure (view) |

#### Konsep Penting Penghalaan

**`Route::resource`** — mencipta 7 laluan CRUD secara automatik:

```php
Route::resource('pembayar', PembayarController::class);
// Sama seperti menulis 7 laluan secara manual
```

**`Route::group`** — mengumpulkan laluan yang berkongsi tetapan:

```php
Route::middleware(['log.akses'])->group(function () {
    // Semua laluan di sini akan melalui middleware log.akses
});
```

**`Route::prefix`** — menambah awalan URI:

```php
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', ...); // URI sebenar: /admin/dashboard
});
```

**`->name()`** — menamakan laluan untuk rujukan dalam kod:

```php
route('pembayar.index')  // Menghasilkan URL /pembayar
route('admin.dashboard') // Menghasilkan URL /admin/dashboard
```

---

## Bahagian D: Paparan Tambahan

### Langkah 11: Cipta Paparan Semak Sistem

Cipta fail `resources/views/semak.blade.php`:

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

---

### Langkah 12: Cipta Paparan Senarai Laluan

Cipta fail `resources/views/maklumat/laluan.blade.php`:

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

---

### Langkah 13: Cipta Halaman Ralat

#### `resources/views/errors/luar-waktu.blade.php`

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

#### `resources/views/errors/404.blade.php`

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

#### `resources/views/errors/403.blade.php`

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

---

### Langkah 14: Kemas Kini Navigasi Layout

Buka `resources/views/layouts/app.blade.php` dan pastikan bahagian navigasi mempunyai pautan berikut:

```blade
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
```

Navigasi ini menggunakan `request()->routeIs()` untuk menyerlahkan pautan yang aktif.

---

## Bahagian E: Jalankan & Uji

### Langkah 15: Migrasi & Seed

```bash
php artisan migrate:fresh --seed
```

Arahan ini akan:
1. Padam semua jadual sedia ada
2. Jalankan semula semua migrasi (pembayars, jenis_zakats, pembayarans)
3. Jalankan semua seeder (PembayarSeeder, JenisZakatSeeder, PembayaranSeeder)

---

### Langkah 16: Uji Hubungan dalam Tinker

```bash
php artisan tinker
```

Cuba arahan berikut satu per satu:

```php
// Dapatkan pembayar pertama dengan pembayarannya
$pembayar = App\Models\Pembayar::with('pembayarans')->first();
$pembayar->nama;
$pembayar->ic_format;             // Aksesor: 850101-14-5678
$pembayar->pendapatan_format;     // Aksesor: RM 4,500.00
$pembayar->pembayarans->count();  // Bilangan pembayaran

// Dapatkan pembayaran dengan hubungan
$bayaran = App\Models\Pembayaran::with(['pembayar', 'jenisZakat'])->first();
$bayaran->pembayar->nama;         // Nama pembayar
$bayaran->jenisZakat->nama;       // Nama jenis zakat
$bayaran->jumlah_format;          // Aksesor: RM 7.00

// Guna skop
App\Models\Pembayar::carian('Ahmad')->get();
App\Models\JenisZakat::aktif()->get();
App\Models\Pembayaran::sah()->count();

// Eager load bersarang
$pembayar = App\Models\Pembayar::with('pembayarans.jenisZakat')->find(1);
$pembayar->pembayarans->each(function ($b) {
    echo $b->jenisZakat->nama . ': ' . $b->jumlah_format . "\n";
});
```

---

### Langkah 17: Uji Semua URL

Jalankan pelayan pembangunan:

```bash
php artisan serve
```

Kemudian layari URL berikut:

| URL | Jangkaan |
|-----|----------|
| `http://localhost:8000/` | Redirect ke senarai pembayar |
| `http://localhost:8000/pembayar` | Senarai pembayar (dengan carian) |
| `http://localhost:8000/pembayar/create` | Borang daftar baru |
| `http://localhost:8000/pembayar/1` | Maklumat pembayar #1 |
| `http://localhost:8000/semak` | Semakan sistem |
| `http://localhost:8000/maklumat/laluan` | Senarai laluan berdaftar |
| `http://localhost:8000/admin/dashboard` | Dashboard pentadbir |

Semak juga fail log untuk melihat kesan middleware LogAkses:

```bash
cat storage/logs/laravel.log | tail -20
```

---

## Struktur Fail Hari 2 (Fail Baru/Diubah Sahaja)

```
sistem-zakat/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── MaklumatController.php      ← BARU
│   │   │   └── SemakController.php         ← BARU
│   │   └── Middleware/
│   │       ├── LogAkses.php                ← BARU
│   │       └── SemakWaktuPejabat.php       ← BARU
│   └── Models/
│       ├── JenisZakat.php                  ← BARU
│       ├── Pembayar.php                    ← DIUBAH (skop, aksesor, hubungan)
│       └── Pembayaran.php                  ← BARU
├── bootstrap/
│   └── app.php                             ← DIUBAH (daftar middleware)
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_jenis_zakats_table.php    ← BARU
│   │   └── xxxx_create_pembayarans_table.php     ← BARU
│   └── seeders/
│       ├── DatabaseSeeder.php              ← DIUBAH
│       ├── JenisZakatSeeder.php            ← BARU
│       └── PembayaranSeeder.php            ← BARU
├── resources/views/
│   ├── admin/
│   │   └── dashboard.blade.php             ← BARU
│   ├── errors/
│   │   ├── 403.blade.php                   ← BARU
│   │   ├── 404.blade.php                   ← BARU
│   │   └── luar-waktu.blade.php            ← BARU
│   ├── maklumat/
│   │   └── laluan.blade.php                ← BARU
│   └── semak.blade.php                     ← BARU
└── routes/
    └── web.php                             ← DIUBAH
```

---

## Rumusan

Pada Hari 2, kita telah belajar:

| Topik | Apa yang dipelajari |
|-------|---------------------|
| **Model Lanjutan** | Skop (`scopeCarian`, `scopeAktif`, `scopeSah`), aksesor (`ic_format`, `pendapatan_format`, `jumlah_format`), hubungan model (`hasMany`, `belongsTo`), eager loading (`with()`), dan masalah N+1 |
| **Middleware** | Cipta middleware tersuai (`LogAkses`, `SemakWaktuPejabat`), daftar alias dalam `bootstrap/app.php`, guna dalam laluan |
| **Pengawal** | Pengawal tambahan (`SemakController`, `MaklumatController`) selain resource controller |
| **Penghalaan** | `Route::resource`, `Route::group`, `Route::prefix`, `->name()`, `->middleware()` |

Pada **Hari 3**, kita akan menambah:
- Pengesahan pengguna (Login & Daftar)
- Paparan Blade lanjutan (komponen, layout)
- Pengesahan input (validation) terperinci
- Dashboard dengan statistik sebenar
