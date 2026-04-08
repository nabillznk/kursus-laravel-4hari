# Hari 4 — API, Dashboard, Storan Fail, Events, Testing & Eksport

> **Sistem Pengurusan Zakat Kedah — Kursus Laravel 4 Hari**
> **Tempoh:** 6 jam | **Prasyarat:** Hari 1, 2 & 3 selesai

---

## Pengenalan

Hari ini adalah hari terakhir kursus. Kita akan menambah REST API, paparan dashboard, storan fail, events & listeners, ujian automatik (PHPUnit), dan eksport data CSV. Semua ciri daripada hari sebelumnya sudah wujud — CRUD Pembayar (Hari 1), Routes/Middleware/Hubungan Model (Hari 2), dan Blade/JenisZakat/Pembayaran/Auth (Hari 3).

| # | Topik | Perkara |
|---|-------|---------|
| 1 | REST API | API Controller, JSON, `apiResource`, curl |
| 2 | Dashboard | Statistik, Eloquent aggregates, small-box |
| 3 | Storan Fail | Upload gambar, Storage facade, public disk |
| 4 | Events & Listeners | Event-driven architecture, dispatch, listen |
| 5 | Testing (PHPUnit) | Feature tests, `assertDatabaseHas`, API tests |
| 6 | Eksport Data | CSV export, `StreamedResponse`, `chunk()` |
| 7 | Hubungan Lanjutan | Eager loading, `withCount`, `withSum`, tinker |

**Prasyarat:** Hari 1-3 selesai (CRUD Pembayar/JenisZakat/Pembayaran, Auth, Middleware sudah wujud).

---

## Bahagian A: REST API

### Langkah 1: Cipta PembayarApiController

Jalankan arahan Artisan dengan flag `--api` (mencipta controller tanpa method `create` dan `edit` kerana API tidak memerlukan borang):

```bash
php artisan make:controller Api/PembayarApiController --api
```

Buka fail `app/Http/Controllers/Api/PembayarApiController.php` dan gantikan dengan kod berikut:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayar;
use Illuminate\Http\Request;

class PembayarApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayar::query();

        if ($request->filled('carian')) {
            $query->carian($request->carian);
        }

        $pembayars = $query->latest()->paginate(10);

        return response()->json([
            'status'  => 'success',
            'message' => 'Senarai pembayar berjaya dipaparkan.',
            'data'    => $pembayars,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'                => 'required|string|max:255',
            'no_ic'               => 'required|string|size:12|unique:pembayars,no_ic',
            'alamat'              => 'required|string',
            'no_tel'              => 'required|string|max:15',
            'email'               => 'nullable|email',
            'pekerjaan'           => 'nullable|string|max:255',
            'pendapatan_bulanan'  => 'nullable|numeric|min:0',
        ]);

        $pembayar = Pembayar::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pembayar berjaya ditambah.',
            'data'    => $pembayar,
        ], 201);
    }

    public function show(Pembayar $pembayar)
    {
        $pembayar->load('pembayarans.jenisZakat');

        return response()->json([
            'status'  => 'success',
            'message' => 'Maklumat pembayar berjaya dipaparkan.',
            'data'    => $pembayar,
        ]);
    }

    public function update(Request $request, Pembayar $pembayar)
    {
        $validated = $request->validate([
            'nama'                => 'required|string|max:255',
            'no_ic'               => 'required|string|size:12|unique:pembayars,no_ic,' . $pembayar->id,
            'alamat'              => 'required|string',
            'no_tel'              => 'required|string|max:15',
            'email'               => 'nullable|email',
            'pekerjaan'           => 'nullable|string|max:255',
            'pendapatan_bulanan'  => 'nullable|numeric|min:0',
        ]);

        $pembayar->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Maklumat pembayar berjaya dikemaskini.',
            'data'    => $pembayar,
        ]);
    }

    public function destroy(Pembayar $pembayar)
    {
        $pembayar->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pembayar berjaya dipadam.',
            'data'    => null,
        ]);
    }
}
```

**Penerangan:**

- Controller ini mengembalikan **JSON** dan bukannya paparan Blade — sesuai untuk aplikasi mudah alih atau frontend JavaScript.
- Setiap respons menggunakan struktur konsisten: `status`, `message`, dan `data`.
- `response()->json([...])` — Mengembalikan respons JSON dengan header `Content-Type: application/json`.
- **Kod status HTTP:**
  - `200 OK` — Lalai untuk `index()`, `show()`, `update()`, `destroy()`.
  - `201 Created` — Digunakan dalam `store()` apabila sumber baru berjaya dicipta.
- `index()` — Menyokong parameter `?carian=` menggunakan scope `carian()` pada model. Menggunakan `paginate(10)` untuk paginasi.
- `show()` — Memuatkan pembayar beserta senarai pembayarannya menggunakan eager loading (`pembayarans.jenisZakat`).
- `update()` — Pengecualian `no_ic` unik untuk rekod semasa: `unique:pembayars,no_ic,' . $pembayar->id`.

---

### Langkah 2: Cipta StatistikApiController

Jalankan arahan Artisan:

```bash
php artisan make:controller Api/StatistikApiController
```

Buka fail `app/Http/Controllers/Api/StatistikApiController.php` dan gantikan dengan kod berikut:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisZakat;
use App\Models\Pembayar;
use App\Models\Pembayaran;

class StatistikApiController extends Controller
{
    public function index()
    {
        $jumlahPembayar    = Pembayar::count();
        $jumlahKutipan     = Pembayaran::sah()->sum('jumlah');
        $jumlahTransaksi   = Pembayaran::count();
        $transaksiSah      = Pembayaran::sah()->count();
        $transaksiPending  = Pembayaran::where('status', 'pending')->count();
        $transaksiBatal    = Pembayaran::where('status', 'batal')->count();
        $jenisZakatAktif   = JenisZakat::aktif()->count();

        return response()->json([
            'status'  => 'success',
            'message' => 'Statistik sistem zakat.',
            'data'    => [
                'jumlah_pembayar'     => $jumlahPembayar,
                'jumlah_kutipan'      => (float) $jumlahKutipan,
                'jumlah_transaksi'    => $jumlahTransaksi,
                'transaksi_sah'       => $transaksiSah,
                'transaksi_pending'   => $transaksiPending,
                'transaksi_batal'     => $transaksiBatal,
                'jenis_zakat_aktif'   => $jenisZakatAktif,
            ],
        ]);
    }
}
```

**Penerangan:**

- Controller ini menyediakan satu endpoint ringkas yang mengembalikan statistik keseluruhan sistem.
- Menggunakan query scope `sah()` dan `aktif()` yang ditakrifkan pada model `Pembayaran` dan `JenisZakat` (dari Hari 3).
- `(float) $jumlahKutipan` — Memastikan nilai kutipan dikembalikan sebagai nombor dan bukan string.
- API statistik ini boleh digunakan oleh dashboard luaran, aplikasi mudah alih, atau papan pemuka pengurusan.

---

### Langkah 3: Tetapkan Laluan API

Cipta fail `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\PembayarApiController;
use App\Http\Controllers\Api\StatistikApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('pembayar', PembayarApiController::class);
    Route::get('statistik', [StatistikApiController::class, 'index']);
});
```

**Penerangan:**

- `Route::prefix('v1')` — Semua endpoint API bermula dengan `/api/v1/` untuk menyokong versi API pada masa hadapan.
- `Route::apiResource()` — Mendaftarkan 5 laluan REST secara automatik: `index`, `store`, `show`, `update`, dan `destroy` (tanpa `create` dan `edit` yang hanya diperlukan untuk borang web).

**Jadual endpoint API yang didaftarkan:**

| Kaedah | URI | Penerangan |
|--------|-----|------------|
| `GET` | `/api/v1/pembayar` | Senarai semua pembayar (dengan paginasi) |
| `POST` | `/api/v1/pembayar` | Tambah pembayar baru |
| `GET` | `/api/v1/pembayar/{pembayar}` | Lihat satu pembayar (dengan pembayaran) |
| `PUT/PATCH` | `/api/v1/pembayar/{pembayar}` | Kemaskini pembayar |
| `DELETE` | `/api/v1/pembayar/{pembayar}` | Padam pembayar |
| `GET` | `/api/v1/statistik` | Statistik keseluruhan sistem |

---

### Langkah 4: Daftar Laluan API dalam Bootstrap

Buka fail `bootstrap/app.php` dan tambah baris `api:` dalam bahagian `withRouting`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',       // <-- Tambah baris ini
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

**Penerangan:**

- Dalam Laravel 11, pendaftaran laluan dilakukan di `bootstrap/app.php` dan bukan lagi di `RouteServiceProvider`.
- Baris `api: __DIR__.'/../routes/api.php'` memberitahu Laravel untuk memuatkan laluan API daripada fail `routes/api.php`.
- Laravel secara automatik menambah prefix `/api` pada semua laluan dalam fail ini. Digabungkan dengan `Route::prefix('v1')`, URL lengkap menjadi `/api/v1/...`.

---

### Langkah 5: Uji API dengan curl

Pastikan pelayan berjalan (`php artisan serve`), kemudian buka terminal baru.

**Senarai semua pembayar:**

```bash
curl http://localhost:8000/api/v1/pembayar
```

**Cari pembayar:**

```bash
curl http://localhost:8000/api/v1/pembayar?carian=Ahmad
```

**Tambah pembayar baru:**

```bash
curl -X POST http://localhost:8000/api/v1/pembayar \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "Ali bin Abu",
    "no_ic": "990101015555",
    "alamat": "123 Jalan Putra, Alor Setar, Kedah",
    "no_tel": "0123456789",
    "email": "ali@contoh.com",
    "pekerjaan": "Guru",
    "pendapatan_bulanan": 4500.00
  }'
```

**Lihat satu pembayar (dengan senarai pembayaran):**

```bash
curl http://localhost:8000/api/v1/pembayar/1
```

**Kemaskini pembayar:**

```bash
curl -X PUT http://localhost:8000/api/v1/pembayar/1 \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "Ahmad bin Ismail",
    "no_ic": "850315085123",
    "alamat": "456 Jalan Baru, Sungai Petani, Kedah",
    "no_tel": "0124567890"
  }'
```

**Padam pembayar:**

```bash
curl -X DELETE http://localhost:8000/api/v1/pembayar/1
```

**Lihat statistik sistem:**

```bash
curl http://localhost:8000/api/v1/statistik
```

Contoh respons statistik:

```json
{
  "status": "success",
  "message": "Statistik sistem zakat.",
  "data": {
    "jumlah_pembayar": 10,
    "jumlah_kutipan": 15750.00,
    "jumlah_transaksi": 20,
    "transaksi_sah": 15,
    "transaksi_pending": 3,
    "transaksi_batal": 2,
    "jenis_zakat_aktif": 4
  }
}
```

---

## Bahagian B: Dashboard & Statistik

### Langkah 6: Cipta DashboardController

Jalankan arahan Artisan:

```bash
php artisan make:controller DashboardController
```

Buka fail `app/Http/Controllers/DashboardController.php` dan gantikan dengan kod berikut:

```php
<?php

namespace App\Http\Controllers;

use App\Models\JenisZakat;
use App\Models\Pembayar;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahPembayar = Pembayar::count();

        $jumlahKutipan = Pembayaran::sah()->sum('jumlah');

        $transaksiBulanIni = Pembayaran::whereMonth('tarikh_bayar', now()->month)
            ->whereYear('tarikh_bayar', now()->year)
            ->count();

        $jenisZakatAktif = JenisZakat::aktif()->count();

        // Jenis zakat paling popular
        $jenisPopular = JenisZakat::withCount('pembayarans')
            ->orderByDesc('pembayarans_count')
            ->first();

        // 5 pembayaran terkini
        $pembayaranTerkini = Pembayaran::with(['pembayar', 'jenisZakat'])
            ->latest('tarikh_bayar')
            ->take(5)
            ->get();

        // Ringkasan bulanan (6 bulan terakhir)
        $ringkasanBulanan = Pembayaran::sah()
            ->where('tarikh_bayar', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(tarikh_bayar, '%Y-%m') as bulan"),
                DB::raw('COUNT(*) as bilangan'),
                DB::raw('SUM(jumlah) as jumlah_kutipan')
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return view('dashboard', compact(
            'jumlahPembayar',
            'jumlahKutipan',
            'transaksiBulanIni',
            'jenisZakatAktif',
            'jenisPopular',
            'pembayaranTerkini',
            'ringkasanBulanan'
        ));
    }
}
```

**Penerangan:**

- `Pembayar::count()` — Mengira jumlah pembayar berdaftar.
- `Pembayaran::sah()->sum('jumlah')` — Mengira jumlah kutipan daripada pembayaran yang berstatus "sah" sahaja. `sah()` ialah query scope yang ditakrifkan pada model `Pembayaran`.
- `whereMonth()` dan `whereYear()` — Menapis transaksi bulan semasa sahaja.
- `JenisZakat::aktif()->count()` — Mengira jenis zakat yang masih aktif menggunakan scope `aktif()`.
- `withCount('pembayarans')` — Mengira bilangan pembayaran bagi setiap jenis zakat untuk menentukan yang paling popular. Laravel secara automatik mencipta atribut `pembayarans_count`.
- `Pembayaran::with(['pembayar', 'jenisZakat'])` — Eager loading untuk mengelakkan masalah N+1 query.
- `DB::raw()` — Menggunakan SQL mentah untuk `DATE_FORMAT` dan aggregate functions dalam ringkasan bulanan.
- `compact()` — Menghantar semua pemboleh ubah ke paparan `dashboard`.

---

### Langkah 7: Cipta Komponen Stat Card

Cipta folder `components` dalam `resources/views/` (jika belum wujud) dan fail `stat-card.blade.php`:

```bash
mkdir -p resources/views/components
```

Cipta fail `resources/views/components/stat-card.blade.php`:

```blade
@props(['icon', 'label', 'value', 'color' => 'green'])

@php
    $bgColor   = "bg-{$color}-50";
    $iconColor = "text-{$color}-600";
    $ringColor = "ring-{$color}-100";
@endphp

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center">
        <div class="flex-shrink-0 p-3 rounded-full {{ $bgColor }} ring-4 {{ $ringColor }}">
            <span class="text-2xl {{ $iconColor }}">{!! $icon !!}</span>
        </div>
        <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
        </div>
    </div>
</div>
```

**Penerangan:**

- `@props` — Mentakrifkan prop yang diterima oleh komponen: `icon`, `label`, `value`, dan `color` (lalai: `green`).
- Kelas CSS dijana secara dinamik berdasarkan `$color` — contohnya `bg-blue-50`, `text-blue-600`, `ring-blue-100`.
- `{!! $icon !!}` — Memaparkan ikon SVG tanpa escape HTML (kerana mengandungi tag HTML).
- Komponen ini boleh digunakan semula di mana-mana paparan dengan `<x-stat-card>`.

---

### Langkah 8: Cipta Paparan Dashboard

Cipta fail `resources/views/dashboard.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500 mt-1">Selamat Datang, {{ Auth::user()->name }}!</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
            label="Jumlah Pembayar"
            :value="$jumlahPembayar"
            color="blue"
        />

        <x-stat-card
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            label="Jumlah Kutipan"
            :value="'RM ' . number_format($jumlahKutipan, 2)"
            color="green"
        />

        <x-stat-card
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
            label="Transaksi Bulan Ini"
            :value="$transaksiBulanIni"
            color="yellow"
        />

        <x-stat-card
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
            label="Jenis Zakat Aktif"
            :value="$jenisZakatAktif"
            color="purple"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pembayaran Terkini --}}
        <x-card title="Pembayaran Terkini">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 text-gray-600 font-medium">Pembayar</th>
                            <th class="text-left py-2 text-gray-600 font-medium">Jenis</th>
                            <th class="text-right py-2 text-gray-600 font-medium">Jumlah</th>
                            <th class="text-center py-2 text-gray-600 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayaranTerkini as $p)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2">{{ $p->pembayar->nama ?? '-' }}</td>
                                <td class="py-2">{{ $p->jenisZakat->nama ?? '-' }}</td>
                                <td class="py-2 text-right font-medium">{{ $p->jumlah_format }}</td>
                                <td class="py-2 text-center">
                                    <x-badge :type="$p->status">{{ ucfirst($p->status) }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-gray-400">Tiada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Ringkasan Bulanan --}}
        <x-card title="Ringkasan Bulanan (6 Bulan Terakhir)">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 text-gray-600 font-medium">Bulan</th>
                            <th class="text-center py-2 text-gray-600 font-medium">Bilangan Transaksi</th>
                            <th class="text-right py-2 text-gray-600 font-medium">Jumlah Kutipan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ringkasanBulanan as $r)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2">{{ $r->bulan }}</td>
                                <td class="py-2 text-center">{{ $r->bilangan }}</td>
                                <td class="py-2 text-right font-medium">RM {{ number_format($r->jumlah_kutipan, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-gray-400">Tiada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
```

**Penerangan:**

- `@extends('layouts.app')` — Menggunakan layout utama yang sudah dicipta pada Hari 3 (navbar dan footer).
- `{{ Auth::user()->name }}` — Memaparkan nama pengguna yang sedang log masuk.
- `<x-stat-card>` — Menggunakan komponen stat card yang dicipta di Langkah 7 dengan warna berbeza untuk setiap metrik.
- `<x-card>` dan `<x-badge>` — Komponen Blade sedia ada daripada Hari 3.
- `@forelse` / `@empty` — Memaparkan senarai data atau mesej "Tiada data" jika tiada rekod.
- `$p->jumlah_format` — Accessor pada model `Pembayaran` yang memformat jumlah dengan `RM`.

---

### Langkah 9: Tambah Laluan Dashboard

Buka fail `routes/web.php` dan tambah laluan dashboard dalam kumpulan `auth`:

```php
use App\Http\Controllers\DashboardController;

// Dalam kumpulan Route::middleware('auth')
Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
```

> **Nota:** Fail `routes/web.php` sudah mempunyai laluan auth dan CRUD daripada Hari 2 dan 3. Hanya tambah dua baris di atas dalam kumpulan `middleware('auth')`.

---

## Bahagian C: Storan Fail (File Storage)

### Langkah 10: Tambah Lajur Gambar pada Jadual Pembayar

Jalankan arahan Artisan untuk mencipta migrasi:

```bash
php artisan make:migration add_gambar_to_pembayars_table --table=pembayars
```

Buka fail migrasi yang baru dicipta di `database/migrations/` dan gantikan dengan kod berikut:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah lajur gambar pada jadual pembayars.
     */
    public function up(): void
    {
        Schema::table('pembayars', function (Blueprint $table) {
            $table->string('gambar')->nullable()->after('pendapatan_bulanan');
        });
    }

    /**
     * Buang lajur gambar.
     */
    public function down(): void
    {
        Schema::table('pembayars', function (Blueprint $table) {
            $table->dropColumn('gambar');
        });
    }
};
```

Kemudian jalankan migrasi:

```bash
php artisan migrate
```

**Penerangan:**

- `Schema::table()` — Mengubah jadual sedia ada (bukan `Schema::create()` yang mencipta jadual baru).
- `->nullable()` — Lajur gambar boleh kosong kerana tidak semua pembayar mempunyai gambar.
- `->after('pendapatan_bulanan')` — Meletakkan lajur selepas `pendapatan_bulanan` untuk susunan yang kemas.
- `down()` — Membuang lajur jika migrasi perlu diundur (rollback).

Kemudian tambah `'gambar'` ke dalam `$fillable` pada model `Pembayar` (`app/Models/Pembayar.php`):

```php
protected $fillable = [
    'nama',
    'no_ic',
    'alamat',
    'no_tel',
    'email',
    'pekerjaan',
    'pendapatan_bulanan',
    'gambar',
];
```

---

### Langkah 11: Cipta Pautan Storan

```bash
php artisan storage:link
```

**Penerangan:**

- Ini mencipta symbolic link dari `public/storage` ke `storage/app/public` supaya fail yang dimuat naik boleh diakses melalui URL.
- Selepas arahan ini, fail di `storage/app/public/pembayar/foto.jpg` boleh diakses melalui `http://sistem-zakat.test/storage/pembayar/foto.jpg`.
- Arahan ini hanya perlu dijalankan **sekali sahaja** untuk setiap projek.

---

### Langkah 12: Kemas Kini PembayarController untuk Muat Naik

Buka fail `app/Http/Controllers/PembayarController.php` dan kemas kini.

Pertama, tambah import `Storage` di bahagian atas fail:

```php
use Illuminate\Support\Facades\Storage;
```

Kemudian kemas kini kaedah `store()`:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'nama'                => 'required|string|max:255',
        'no_ic'               => 'required|string|size:12|unique:pembayars,no_ic',
        'alamat'              => 'required|string',
        'no_tel'              => 'required|string|max:15',
        'email'               => 'nullable|email',
        'pekerjaan'           => 'nullable|string|max:255',
        'pendapatan_bulanan'  => 'nullable|numeric|min:0',
        'gambar'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'nama.required'   => 'Sila masukkan nama pembayar.',
        'no_ic.required'  => 'Sila masukkan nombor IC.',
        'no_ic.size'      => 'Nombor IC mestilah 12 digit.',
        'no_ic.unique'    => 'Nombor IC ini telah didaftarkan.',
        'alamat.required' => 'Sila masukkan alamat.',
        'no_tel.required' => 'Sila masukkan nombor telefon.',
        'email.email'     => 'Format e-mel tidak sah.',
        'gambar.image'    => 'Fail mestilah imej.',
        'gambar.mimes'    => 'Format imej: JPEG, PNG, JPG sahaja.',
        'gambar.max'      => 'Saiz imej maksimum 2MB.',
    ]);

    if ($request->hasFile('gambar')) {
        $validated['gambar'] = $request->file('gambar')->store('pembayar', 'public');
    }

    Pembayar::create($validated);

    return redirect()->route('pembayar.index')
        ->with('success', 'Pembayar berjaya ditambah.');
}
```

Dan kemas kini kaedah `update()`:

```php
public function update(Request $request, Pembayar $pembayar)
{
    $validated = $request->validate([
        'nama'                => 'required|string|max:255',
        'no_ic'               => 'required|string|size:12|unique:pembayars,no_ic,' . $pembayar->id,
        'alamat'              => 'required|string',
        'no_tel'              => 'required|string|max:15',
        'email'               => 'nullable|email',
        'pekerjaan'           => 'nullable|string|max:255',
        'pendapatan_bulanan'  => 'nullable|numeric|min:0',
        'gambar'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'nama.required'   => 'Sila masukkan nama pembayar.',
        'no_ic.required'  => 'Sila masukkan nombor IC.',
        'no_ic.size'      => 'Nombor IC mestilah 12 digit.',
        'no_ic.unique'    => 'Nombor IC ini telah didaftarkan.',
        'alamat.required' => 'Sila masukkan alamat.',
        'no_tel.required' => 'Sila masukkan nombor telefon.',
        'email.email'     => 'Format e-mel tidak sah.',
        'gambar.image'    => 'Fail mestilah imej.',
        'gambar.mimes'    => 'Format imej: JPEG, PNG, JPG sahaja.',
        'gambar.max'      => 'Saiz imej maksimum 2MB.',
    ]);

    if ($request->hasFile('gambar')) {
        // Padam gambar lama jika ada
        if ($pembayar->gambar) {
            Storage::disk('public')->delete($pembayar->gambar);
        }
        $validated['gambar'] = $request->file('gambar')->store('pembayar', 'public');
    }

    $pembayar->update($validated);

    return redirect()->route('pembayar.show', $pembayar)
        ->with('success', 'Maklumat pembayar berjaya dikemaskini.');
}
```

**Penerangan:**

- `$request->hasFile('gambar')` — Semak sama ada fail dimuat naik bersama borang.
- `$request->file('gambar')->store('pembayar', 'public')` — Simpan fail dalam `storage/app/public/pembayar/`. Laravel menjana nama fail unik secara automatik.
- `Storage::disk('public')->delete()` — Padam fail lama dari storan sebelum menyimpan yang baru (dalam `update()`).
- Pengesahan: `image|mimes:jpeg,png,jpg|max:2048` — Hanya terima fail imej JPEG/PNG/JPG, saiz maksimum 2MB (2048 KB).

---

### Langkah 13: Kemas Kini Paparan Borang & Butiran

**Borang Tambah (`resources/views/pembayar/create.blade.php`):**

Tambah `enctype="multipart/form-data"` pada tag `<form>` (wajib untuk muat naik fail):

```blade
<form method="POST" action="{{ route('pembayar.store') }}" enctype="multipart/form-data">
```

Kemudian tambah medan gambar sebelum butang hantar:

```blade
<div class="md:col-span-2">
    <label for="gambar" class="block text-sm font-medium text-gray-700 mb-1">Gambar Profil</label>
    <input type="file" id="gambar" name="gambar" accept="image/*"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG. Maksimum 2MB.</p>
    @error('gambar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
</div>
```

**Borang Edit (`resources/views/pembayar/edit.blade.php`):**

Sama seperti borang tambah, tetapi tunjukkan gambar semasa jika ada:

```blade
<form method="POST" action="{{ route('pembayar.update', $pembayar) }}" enctype="multipart/form-data">
```

```blade
<div class="md:col-span-2">
    <label for="gambar" class="block text-sm font-medium text-gray-700 mb-1">Gambar Profil</label>
    @if($pembayar->gambar)
        <div class="mb-2">
            <img src="{{ Storage::url($pembayar->gambar) }}" alt="{{ $pembayar->nama }}" class="w-20 h-20 rounded-full object-cover border-2 border-emerald-200">
            <p class="text-xs text-gray-500 mt-1">Gambar semasa. Pilih fail baru untuk menggantikan.</p>
        </div>
    @endif
    <input type="file" id="gambar" name="gambar" accept="image/*"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG. Maksimum 2MB.</p>
    @error('gambar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
</div>
```

**Paparan Butiran (`resources/views/pembayar/show.blade.php`):**

Tambah paparan gambar di dalam kad profil:

```blade
<x-card title="Profil Pembayar">
    @if($pembayar->gambar)
        <div class="flex justify-center mb-4">
            <img src="{{ Storage::url($pembayar->gambar) }}" alt="{{ $pembayar->nama }}" class="w-32 h-32 rounded-full object-cover border-4 border-emerald-200 shadow">
        </div>
    @endif
    <dl class="space-y-3">
        ...
    </dl>
</x-card>
```

**Penerangan:**

- `enctype="multipart/form-data"` — **Wajib** pada tag `<form>` untuk muat naik fail. Tanpa ini, fail tidak akan dihantar.
- `accept="image/*"` — Memaparkan penapis fail imej pada dialog pemilihan fail.
- `Storage::url($pembayar->gambar)` — Menjana URL penuh untuk mengakses fail (contoh: `/storage/pembayar/abc123.jpg`).
- Kelas Tailwind `w-32 h-32 rounded-full object-cover` — Memaparkan gambar dalam bentuk bulat dengan saiz tetap.

| Konsep | Penerangan |
|--------|------------|
| `Storage::disk('public')` | Storan yang boleh diakses secara awam |
| `store('folder', 'disk')` | Simpan fail ke folder dalam disk |
| `Storage::url($path)` | Jana URL untuk akses fail |
| `Storage::delete($path)` | Padam fail dari storan |
| `storage:link` | Cipta symbolic link ke public/ |
| `enctype="multipart/form-data"` | Wajib pada borang HTML untuk muat naik fail |

---

## Bahagian D: Events & Listeners

### Langkah 14: Cipta Event — PembayaranDibuat

Jalankan arahan Artisan:

```bash
php artisan make:event PembayaranDibuat
```

Buka fail `app/Events/PembayaranDibuat.php` dan gantikan dengan kod berikut:

```php
<?php

namespace App\Events;

use App\Models\Pembayaran;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PembayaranDibuat
{
    use Dispatchable, SerializesModels;

    /**
     * Peristiwa ini dicetuskan apabila pembayaran baru dibuat.
     */
    public function __construct(
        public Pembayaran $pembayaran
    ) {}
}
```

**Penerangan:**

- **Event** ialah "perkara yang berlaku" dalam sistem — dalam kes ini, pembayaran baru telah dibuat.
- `Dispatchable` — Trait yang membolehkan event dicetuskan menggunakan `PembayaranDibuat::dispatch($pembayaran)`.
- `SerializesModels` — Trait yang menyerialisasi model Eloquent untuk digunakan dalam queue (jika listener dijalankan secara asinkron).
- `public Pembayaran $pembayaran` — Constructor promotion (PHP 8.1+) yang secara automatik mencipta property awam.
- Event tidak mengandungi logik — ia hanya membawa data (model `Pembayaran`).

---

### Langkah 15: Cipta Listener — LogPembayaranBaru

Jalankan arahan Artisan:

```bash
php artisan make:listener LogPembayaranBaru --event=PembayaranDibuat
```

Buka fail `app/Listeners/LogPembayaranBaru.php` dan gantikan dengan kod berikut:

```php
<?php

namespace App\Listeners;

use App\Events\PembayaranDibuat;
use Illuminate\Support\Facades\Log;

class LogPembayaranBaru
{
    /**
     * Rekod pembayaran baru ke dalam log.
     */
    public function handle(PembayaranDibuat $event): void
    {
        $p = $event->pembayaran;
        $p->load(['pembayar', 'jenisZakat']);

        Log::channel('single')->info('Pembayaran Baru Diterima', [
            'no_resit' => $p->no_resit,
            'pembayar' => $p->pembayar->nama,
            'jenis_zakat' => $p->jenisZakat->nama,
            'jumlah' => 'RM ' . number_format($p->jumlah, 2),
            'tarikh' => $p->tarikh_bayar->format('d/m/Y'),
            'cara_bayar' => $p->cara_bayar,
        ]);
    }
}
```

**Penerangan:**

- **Listener** ialah "tindakan yang diambil" apabila event berlaku.
- `handle()` — Kaedah yang dipanggil apabila event dicetuskan. Menerima objek event sebagai parameter.
- `$event->pembayaran` — Mengakses model `Pembayaran` yang dihantar melalui event.
- `$p->load(['pembayar', 'jenisZakat'])` — Memuatkan hubungan yang diperlukan untuk log.
- `Log::channel('single')->info()` — Menulis log ke fail `storage/logs/laravel.log`.
- Log ini berguna untuk audit trail dan pemantauan sistem.

---

### Langkah 16: Cipta Listener — HantarNotifikasiPembayaran

Jalankan arahan Artisan:

```bash
php artisan make:listener HantarNotifikasiPembayaran --event=PembayaranDibuat
```

Buka fail `app/Listeners/HantarNotifikasiPembayaran.php` dan gantikan dengan kod berikut:

```php
<?php

namespace App\Listeners;

use App\Events\PembayaranDibuat;
use Illuminate\Support\Facades\Log;

class HantarNotifikasiPembayaran
{
    /**
     * Hantar notifikasi (simulasi) kepada pentadbir.
     */
    public function handle(PembayaranDibuat $event): void
    {
        $p = $event->pembayaran;
        $p->load('pembayar');

        // Simulasi notifikasi — dalam pengeluaran, hantar e-mel/SMS
        Log::channel('single')->info('NOTIFIKASI: Pembayaran baru dari ' . $p->pembayar->nama . ' sebanyak RM ' . number_format($p->jumlah, 2));
    }
}
```

**Penerangan:**

- Listener kedua ini **menunjukkan bahawa satu event boleh mempunyai banyak listener**.
- Dalam persekitaran sebenar, listener ini akan menghantar e-mel atau SMS kepada pentadbir. Di sini kita gunakan `Log` sebagai simulasi.
- Setiap listener dijalankan secara berasingan dan tidak bergantung antara satu sama lain.

---

### Langkah 17: Daftar Event & Listener

Buka fail `app/Providers/AppServiceProvider.php` dan kemas kini:

```php
<?php

namespace App\Providers;

use App\Events\PembayaranDibuat;
use App\Listeners\HantarNotifikasiPembayaran;
use App\Listeners\LogPembayaranBaru;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(PembayaranDibuat::class, LogPembayaranBaru::class);
        Event::listen(PembayaranDibuat::class, HantarNotifikasiPembayaran::class);
    }
}
```

**Penerangan:**

- Dalam Laravel 11, pendaftaran event-listener dilakukan di `AppServiceProvider` menggunakan `Event::listen()`.
- `Event::listen(EventClass, ListenerClass)` — Mendaftarkan listener untuk event tertentu.
- Satu event boleh mempunyai berbilang listener — kedua-dua `LogPembayaranBaru` dan `HantarNotifikasiPembayaran` akan dijalankan apabila `PembayaranDibuat` dicetuskan.

---

### Langkah 18: Cetuskan Event dalam Controller

Buka fail `app/Http/Controllers/PembayaranController.php` dan kemas kini.

Tambah import di bahagian atas:

```php
use App\Events\PembayaranDibuat;
```

Kemudian kemas kini kaedah `store()` untuk mencetuskan event selepas mencipta pembayaran:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        // ... pengesahan sedia ada ...
    ]);

    $pembayaran = Pembayaran::create($validated);

    PembayaranDibuat::dispatch($pembayaran);

    return redirect()->route('pembayaran.index')
        ->with('success', 'Pembayaran berjaya direkodkan.');
}
```

**Penerangan:**

- `Pembayaran::create($validated)` — Kini disimpan ke pemboleh ubah `$pembayaran` supaya boleh dihantar ke event.
- `PembayaranDibuat::dispatch($pembayaran)` — Mencetuskan event dan menghantar model pembayaran yang baru dicipta.
- Apabila event dicetuskan, kedua-dua listener (`LogPembayaranBaru` dan `HantarNotifikasiPembayaran`) akan dijalankan secara automatik.

Untuk menguji, cipta pembayaran baru melalui borang web, kemudian semak fail log:

```bash
cat storage/logs/laravel.log | tail -20
```

Anda akan melihat dua entri log — satu untuk setiap listener.

| Konsep | Penerangan |
|--------|------------|
| `Event` | Perkara yang berlaku dalam sistem |
| `Listener` | Tindakan yang diambil apabila event berlaku |
| `dispatch()` | Cetuskan event |
| `Event::listen()` | Daftar listener untuk event |
| Satu event, banyak listener | Satu event boleh mempunyai berbilang listener |

---

## Bahagian E: Testing (PHPUnit)

### Langkah 19: Pengenalan PHPUnit

Laravel menggunakan **PHPUnit** untuk ujian automatik. Ujian automatik memastikan aplikasi berfungsi seperti yang dijangka selepas setiap perubahan kod.

**Jenis ujian dalam Laravel:**

| Jenis | Lokasi | Penerangan |
|-------|--------|------------|
| **Feature Test** | `tests/Feature/` | Ujian yang melibatkan HTTP request — menguji keseluruhan aliran (route, controller, view, database) |
| **Unit Test** | `tests/Unit/` | Ujian untuk fungsi/kelas individu — menguji logik kecil secara terpencil |

**Konsep penting:**

- `RefreshDatabase` — Trait yang mereset pangkalan data sebelum setiap ujian. Ini memastikan setiap ujian bermula dengan keadaan bersih.
- `actingAs($user)` — Mensimulasikan log masuk pengguna tanpa perlu melalui borang login.
- `assertDatabaseHas()` / `assertDatabaseMissing()` — Menyemak sama ada rekod wujud/tidak wujud dalam pangkalan data.

---

### Langkah 20: Cipta Ujian Feature — PembayarTest

Jalankan arahan Artisan:

```bash
php artisan make:test PembayarTest
```

Buka fail `tests/Feature/PembayarTest.php` dan gantikan dengan kod berikut:

```php
<?php

namespace Tests\Feature;

use App\Models\Pembayar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengguna yang belum log masuk diarah ke halaman login.
     */
    public function test_tetamu_tidak_boleh_akses_pembayar(): void
    {
        $response = $this->get('/pembayar');
        $response->assertRedirect('/login');
    }

    /**
     * Pengguna yang sudah log masuk boleh melihat senarai pembayar.
     */
    public function test_pengguna_boleh_lihat_senarai_pembayar(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/pembayar');
        $response->assertStatus(200);
        $response->assertSee('Senarai Pembayar');
    }

    /**
     * Pengguna boleh mendaftar pembayar baru.
     */
    public function test_pengguna_boleh_daftar_pembayar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/pembayar', [
            'nama' => 'Ahmad bin Test',
            'no_ic' => '901215145678',
            'alamat' => 'Jalan Ujian, Alor Setar',
            'no_tel' => '0123456789',
            'email' => 'ahmad@test.com',
            'pekerjaan' => 'Jurutera',
            'pendapatan_bulanan' => 5000,
        ]);

        $response->assertRedirect(route('pembayar.index'));
        $this->assertDatabaseHas('pembayars', [
            'nama' => 'Ahmad bin Test',
            'no_ic' => '901215145678',
        ]);
    }

    /**
     * No IC mestilah 12 digit.
     */
    public function test_no_ic_mestilah_12_digit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/pembayar', [
            'nama' => 'Test',
            'no_ic' => '12345', // Kurang 12 digit
            'alamat' => 'Alamat',
            'no_tel' => '012345',
        ]);

        $response->assertSessionHasErrors('no_ic');
    }

    /**
     * Pengguna boleh melihat maklumat pembayar.
     */
    public function test_pengguna_boleh_lihat_pembayar(): void
    {
        $user = User::factory()->create();
        $pembayar = Pembayar::create([
            'nama' => 'Siti Aminah',
            'no_ic' => '880101145678',
            'alamat' => 'Kedah',
            'no_tel' => '0198765432',
        ]);

        $response = $this->actingAs($user)->get("/pembayar/{$pembayar->id}");
        $response->assertStatus(200);
        $response->assertSee('Siti Aminah');
    }

    /**
     * Pengguna boleh mengemaskini pembayar.
     */
    public function test_pengguna_boleh_kemaskini_pembayar(): void
    {
        $user = User::factory()->create();
        $pembayar = Pembayar::create([
            'nama' => 'Asal Nama',
            'no_ic' => '770101145678',
            'alamat' => 'Kedah',
            'no_tel' => '0112345678',
        ]);

        $response = $this->actingAs($user)->put("/pembayar/{$pembayar->id}", [
            'nama' => 'Nama Baharu',
            'no_ic' => '770101145678',
            'alamat' => 'Alamat Baharu',
            'no_tel' => '0112345678',
        ]);

        $response->assertRedirect(route('pembayar.show', $pembayar));
        $this->assertDatabaseHas('pembayars', ['nama' => 'Nama Baharu']);
    }

    /**
     * Pengguna boleh memadamkan pembayar.
     */
    public function test_pengguna_boleh_padam_pembayar(): void
    {
        $user = User::factory()->create();
        $pembayar = Pembayar::create([
            'nama' => 'Untuk Dipadam',
            'no_ic' => '660101145678',
            'alamat' => 'Kedah',
            'no_tel' => '0131234567',
        ]);

        $response = $this->actingAs($user)->delete("/pembayar/{$pembayar->id}");
        $response->assertRedirect(route('pembayar.index'));
        $this->assertDatabaseMissing('pembayars', ['id' => $pembayar->id]);
    }
}
```

**Penerangan setiap ujian:**

| Kaedah | Perkara yang diuji |
|--------|--------------------|
| `test_tetamu_tidak_boleh_akses_pembayar` | Pengguna yang belum log masuk diarah ke halaman login |
| `test_pengguna_boleh_lihat_senarai_pembayar` | Halaman senarai pembayar boleh diakses dan memaparkan tajuk yang betul |
| `test_pengguna_boleh_daftar_pembayar` | Borang tambah pembayar berfungsi dan data disimpan dalam pangkalan data |
| `test_no_ic_mestilah_12_digit` | Pengesahan no IC gagal jika kurang daripada 12 digit |
| `test_pengguna_boleh_lihat_pembayar` | Halaman butiran pembayar memaparkan nama pembayar |
| `test_pengguna_boleh_kemaskini_pembayar` | Borang kemaskini berfungsi dan data dikemas kini dalam pangkalan data |
| `test_pengguna_boleh_padam_pembayar` | Pembayar berjaya dipadam dari pangkalan data |

---

### Langkah 21: Cipta Ujian API — ApiPembayarTest

Jalankan arahan Artisan:

```bash
php artisan make:test ApiPembayarTest
```

Buka fail `tests/Feature/ApiPembayarTest.php` dan gantikan dengan kod berikut:

```php
<?php

namespace Tests\Feature;

use App\Models\Pembayar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPembayarTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_senarai_pembayar(): void
    {
        Pembayar::create([
            'nama' => 'Test API',
            'no_ic' => '950101145678',
            'alamat' => 'Kedah',
            'no_tel' => '0123456789',
        ]);

        $response = $this->getJson('/api/v1/pembayar');
        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'data']);
    }

    public function test_api_cipta_pembayar(): void
    {
        $response = $this->postJson('/api/v1/pembayar', [
            'nama' => 'API Pembayar',
            'no_ic' => '940101145678',
            'alamat' => 'Jalan API',
            'no_tel' => '0191234567',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('pembayars', ['nama' => 'API Pembayar']);
    }

    public function test_api_lihat_pembayar(): void
    {
        $pembayar = Pembayar::create([
            'nama' => 'Lihat API',
            'no_ic' => '930101145678',
            'alamat' => 'Kedah',
            'no_tel' => '0171234567',
        ]);

        $response = $this->getJson("/api/v1/pembayar/{$pembayar->id}");
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }

    public function test_api_pengesahan_gagal(): void
    {
        $response = $this->postJson('/api/v1/pembayar', []);
        $response->assertStatus(422);
    }
}
```

**Penerangan setiap ujian:**

| Kaedah | Perkara yang diuji |
|--------|--------------------|
| `test_api_senarai_pembayar` | Endpoint GET `/api/v1/pembayar` mengembalikan status 200 dengan struktur JSON yang betul |
| `test_api_cipta_pembayar` | Endpoint POST `/api/v1/pembayar` mencipta pembayar dan mengembalikan status 201 |
| `test_api_lihat_pembayar` | Endpoint GET `/api/v1/pembayar/{id}` mengembalikan maklumat pembayar |
| `test_api_pengesahan_gagal` | Endpoint POST tanpa data mengembalikan status 422 (Unprocessable Entity) |

> **Nota:** Ujian API menggunakan `getJson()` dan `postJson()` dan bukannya `get()` dan `post()`. Ini menambah header `Accept: application/json` secara automatik supaya Laravel mengembalikan respons JSON.

---

### Langkah 22: Jalankan Ujian

```bash
# Jalankan semua ujian
php artisan test

# Jalankan satu fail ujian
php artisan test --filter=PembayarTest

# Jalankan satu kaedah ujian
php artisan test --filter=test_pengguna_boleh_daftar_pembayar

# Jalankan dengan output terperinci
php artisan test --verbose
```

Contoh output:

```
PASS  Tests\Feature\PembayarTest
  ✓ tetamu tidak boleh akses pembayar
  ✓ pengguna boleh lihat senarai pembayar
  ✓ pengguna boleh daftar pembayar
  ✓ no ic mestilah 12 digit
  ✓ pengguna boleh lihat pembayar
  ✓ pengguna boleh kemaskini pembayar
  ✓ pengguna boleh padam pembayar

PASS  Tests\Feature\ApiPembayarTest
  ✓ api senarai pembayar
  ✓ api cipta pembayar
  ✓ api lihat pembayar
  ✓ api pengesahan gagal

Tests:    11 passed (18 assertions)
Duration: 2.5s
```

**Rujukan pantas kaedah assert:**

| Kaedah Assert | Penerangan |
|---------------|------------|
| `assertStatus(200)` | Semak kod status HTTP |
| `assertRedirect('/url')` | Semak redirect |
| `assertSee('teks')` | Semak teks ada dalam response |
| `assertDatabaseHas('table', [...])` | Semak rekod wujud dalam DB |
| `assertDatabaseMissing('table', [...])` | Semak rekod tiada dalam DB |
| `assertJson([...])` | Semak respons JSON |
| `assertJsonStructure([...])` | Semak struktur JSON |
| `assertSessionHasErrors('field')` | Semak ralat pengesahan |
| `actingAs($user)` | Simulasi log masuk pengguna |

---

## Bahagian F: Eksport Data (CSV)

### Langkah 23: Cipta ExportController

Jalankan arahan Artisan:

```bash
php artisan make:controller ExportController
```

Buka fail `app/Http/Controllers/ExportController.php` dan gantikan dengan kod berikut:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pembayar;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Eksport senarai pembayar ke CSV.
     */
    public function pembayar(): StreamedResponse
    {
        $fileName = 'pembayar_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Tajuk lajur
            fputcsv($handle, [
                'ID', 'Nama', 'No. IC', 'Alamat', 'No. Tel',
                'E-mel', 'Pekerjaan', 'Pendapatan Bulanan', 'Tarikh Daftar',
            ]);

            // Data
            Pembayar::orderBy('nama')->chunk(100, function ($pembayars) use ($handle) {
                foreach ($pembayars as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->nama,
                        $p->no_ic,
                        $p->alamat,
                        $p->no_tel,
                        $p->email ?? '',
                        $p->pekerjaan ?? '',
                        $p->pendapatan_bulanan ?? 0,
                        $p->created_at->format('d/m/Y'),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Eksport senarai pembayaran ke CSV.
     */
    public function pembayaran(Request $request): StreamedResponse
    {
        $fileName = 'pembayaran_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No. Resit', 'Pembayar', 'No. IC', 'Jenis Zakat',
                'Jumlah (RM)', 'Tarikh Bayar', 'Cara Bayar', 'Status',
            ]);

            $query = Pembayaran::with(['pembayar', 'jenisZakat'])->orderBy('tarikh_bayar', 'desc');

            // Tapis mengikut status jika ada
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            $query->chunk(100, function ($pembayarans) use ($handle) {
                foreach ($pembayarans as $b) {
                    fputcsv($handle, [
                        $b->no_resit,
                        $b->pembayar->nama,
                        $b->pembayar->no_ic,
                        $b->jenisZakat->nama,
                        number_format($b->jumlah, 2),
                        $b->tarikh_bayar->format('d/m/Y'),
                        ucfirst($b->cara_bayar),
                        ucfirst($b->status),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
```

**Penerangan:**

- `StreamedResponse` — Menjana fail secara streaming. Ini bermakna data dihantar ke pelayar secara berperingkat tanpa perlu menyimpan keseluruhan fail dalam memori.
- `response()->streamDownload()` — Kaedah Laravel untuk mencipta respons muat turun secara streaming.
- `chunk(100, ...)` — Memproses data secara berkumpulan (100 rekod sekali). Ini mengelakkan masalah kehabisan memori untuk data yang besar.
- `fputcsv()` — Fungsi PHP terbina yang menulis satu baris ke format CSV (dengan pemisah koma dan petikan automatik).
- `fopen('php://output', 'w')` — Membuka output stream PHP untuk penulisan terus ke pelayar.
- `now()->format('Ymd_His')` — Menambah cap masa pada nama fail (contoh: `pembayar_20240115_143022.csv`).
- Kaedah `pembayaran()` menyokong penapisan mengikut status — jika parameter `?status=sah` dihantar, hanya pembayaran sah akan dieksport.

---

### Langkah 24: Tambah Laluan Eksport

Buka fail `routes/web.php` dan tambah laluan berikut di dalam kumpulan `middleware('auth')`:

```php
use App\Http\Controllers\ExportController;

// Eksport CSV
Route::get('/eksport/pembayar', [ExportController::class, 'pembayar'])->name('eksport.pembayar');
Route::get('/eksport/pembayaran', [ExportController::class, 'pembayaran'])->name('eksport.pembayaran');
```

Fail `routes/web.php` lengkap:

```php
<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JenisZakatController;
use App\Http\Controllers\PembayarController;
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
```

---

### Langkah 25: Tambah Butang Eksport pada Paparan

**Senarai Pembayar (`resources/views/pembayar/index.blade.php`):**

Tambah butang "Eksport CSV" di sebelah butang "Tambah Pembayar":

```blade
<div class="flex space-x-2 mt-2 sm:mt-0">
    <a href="{{ route('eksport.pembayar') }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Eksport CSV
    </a>
    <a href="{{ route('pembayar.create') }}"
       class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Pembayar
    </a>
</div>
```

**Senarai Pembayaran (`resources/views/pembayaran/index.blade.php`):**

Tambah butang "Eksport CSV" yang menghantar penapis status semasa:

```blade
<div class="flex space-x-2 mt-2 sm:mt-0">
    <a href="{{ route('eksport.pembayaran', ['status' => request('status')]) }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Eksport CSV
    </a>
    <a href="{{ route('pembayaran.create') }}"
       class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Pembayaran
    </a>
</div>
```

**Penerangan:**

- `request('status')` — Menghantar parameter status semasa ke laluan eksport. Jika pengguna sedang menapis pembayaran berstatus "sah", eksport CSV juga hanya mengandungi pembayaran sah.
- Butang menggunakan warna biru (`bg-blue-600`) untuk membezakan daripada butang tambah (hijau `bg-emerald-600`).

---

## Bahagian G: Hubungan Model Lanjutan

### Langkah 26: Eager Loading dalam Controller

Eager loading mengelakkan masalah **N+1 query** — di mana setiap rekod mencetuskan query tambahan untuk memuatkan hubungan. Berikut adalah contoh yang digunakan dalam controller Hari 4:

**Dalam PembayarApiController (`show`):**

```php
// Memuatkan pembayar beserta semua pembayaran dan jenis zakat setiap pembayaran
$pembayar->load('pembayarans.jenisZakat');
```

- `load()` — Memuatkan hubungan secara lazy eager loading (selepas model sudah diambil).
- `pembayarans.jenisZakat` — Nested relationship: memuatkan `pembayarans` dan untuk setiap pembayaran, turut memuatkan `jenisZakat`.

**Dalam DashboardController:**

```php
// Memuatkan pembayaran terkini beserta pembayar dan jenis zakat sekaligus
$pembayaranTerkini = Pembayaran::with(['pembayar', 'jenisZakat'])
    ->latest('tarikh_bayar')
    ->take(5)
    ->get();
```

- `with()` — Memuatkan hubungan secara eager loading semasa query dibuat.
- `['pembayar', 'jenisZakat']` — Memuatkan dua hubungan sekaligus dalam satu query.

**Perbandingan: Tanpa vs Dengan Eager Loading**

```php
// TANPA eager loading — N+1 query (1 + 5 + 5 = 11 query)
$pembayarans = Pembayaran::latest()->take(5)->get();
foreach ($pembayarans as $p) {
    echo $p->pembayar->nama;     // Query baru setiap kali
    echo $p->jenisZakat->nama;   // Query baru setiap kali
}

// DENGAN eager loading — 3 query sahaja
$pembayarans = Pembayaran::with(['pembayar', 'jenisZakat'])->latest()->take(5)->get();
foreach ($pembayarans as $p) {
    echo $p->pembayar->nama;     // Tiada query baru
    echo $p->jenisZakat->nama;   // Tiada query baru
}
```

---

### Langkah 27: Query Lanjutan dalam Tinker

Buka Tinker untuk meneroka query hubungan lanjutan:

```bash
php artisan tinker
```

**`withCount` — Mengira bilangan hubungan:**

```php
// Berapa pembayaran setiap pembayar?
$pembayars = App\Models\Pembayar::withCount('pembayarans')->get();
$pembayars->first()->pembayarans_count; // 3

// Jenis zakat paling popular
App\Models\JenisZakat::withCount('pembayarans')
    ->orderByDesc('pembayarans_count')
    ->first();
```

**`withSum` — Menjumlahkan nilai dalam hubungan:**

```php
// Jumlah bayaran setiap pembayar
$pembayars = App\Models\Pembayar::withSum('pembayarans', 'jumlah')->get();
$pembayars->first()->pembayarans_sum_jumlah; // 1500.00
```

**`has` dan `doesntHave` — Menapis berdasarkan kewujudan hubungan:**

```php
// Pembayar yang PERNAH membuat pembayaran
App\Models\Pembayar::has('pembayarans')->get();

// Pembayar yang BELUM membuat sebarang pembayaran
App\Models\Pembayar::doesntHave('pembayarans')->get();
```

**`whereHas` — Menapis berdasarkan syarat dalam hubungan:**

```php
// Pembayar yang mempunyai pembayaran berstatus "sah"
App\Models\Pembayar::whereHas('pembayarans', function ($query) {
    $query->where('status', 'sah');
})->get();

// Pembayar yang membayar Zakat Fitrah
App\Models\Pembayar::whereHas('pembayarans', function ($query) {
    $query->whereHas('jenisZakat', function ($q) {
        $q->where('nama', 'Zakat Fitrah');
    });
})->get();
```

**`loadMissing` — Memuatkan hubungan yang belum dimuatkan:**

```php
$pembayar = App\Models\Pembayar::first();
$pembayar->loadMissing('pembayarans'); // Hanya muat jika belum ada
```

---

## Bahagian H: Jalankan & Uji

### Langkah 28: Jalankan Pelayan

```bash
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Pelayan akan berjalan di `http://localhost:8000`.

---

### Langkah 29: Uji Dashboard

1. Buka pelayar web dan layari `http://localhost:8000`
2. Log masuk dengan akaun admin (dicipta pada Hari 3):

| Medan | Nilai |
|-------|-------|
| E-mel | `admin@zakat.test` |
| Kata Laluan | `password` |

3. Selepas log masuk, semak paparan Dashboard:
   - 4 kad statistik (Jumlah Pembayar, Jumlah Kutipan, Transaksi Bulan Ini, Jenis Zakat Aktif)
   - Jadual Pembayaran Terkini (5 rekod terkini)
   - Jadual Ringkasan Bulanan (6 bulan terakhir)

---

### Langkah 30: Uji Muat Naik Gambar

1. Pergi ke **Senarai Pembayar** > **Tambah Pembayar**
2. Isi borang dan pilih fail gambar (JPEG/PNG, < 2MB)
3. Klik **Simpan**
4. Lihat halaman butiran pembayar — gambar profil dipaparkan dalam bentuk bulat
5. Cuba **Edit** pembayar dan tukar gambar — gambar lama akan dipadam secara automatik

---

### Langkah 31: Uji Events & Listeners

1. Pergi ke **Senarai Pembayaran** > **Tambah Pembayaran**
2. Isi borang dan klik **Simpan**
3. Semak fail log:

```bash
cat storage/logs/laravel.log | tail -20
```

Anda akan melihat dua entri:
- `Pembayaran Baru Diterima` — daripada `LogPembayaranBaru`
- `NOTIFIKASI: Pembayaran baru dari ...` — daripada `HantarNotifikasiPembayaran`

---

### Langkah 32: Uji PHPUnit

```bash
# Jalankan semua ujian
php artisan test

# Jalankan ujian web sahaja
php artisan test --filter=PembayarTest

# Jalankan ujian API sahaja
php artisan test --filter=ApiPembayarTest
```

---

### Langkah 33: Uji Eksport CSV

1. Pergi ke **Senarai Pembayar** dan klik **Eksport CSV** — fail `pembayar_XXXXXXXX_XXXXXX.csv` dimuat turun
2. Pergi ke **Senarai Pembayaran** dan klik **Eksport CSV** — fail `pembayaran_XXXXXXXX_XXXXXX.csv` dimuat turun
3. Cuba tapis pembayaran mengikut status "Sah", kemudian klik **Eksport CSV** — hanya pembayaran sah dieksport
4. Buka fail CSV dengan Excel atau Google Sheets untuk menyemak data

---

### Langkah 34: Uji API dengan curl

Buka terminal baru dan uji setiap endpoint:

```bash
# Senarai pembayar
curl http://localhost:8000/api/v1/pembayar

# Cari pembayar
curl http://localhost:8000/api/v1/pembayar?carian=Ahmad

# Tambah pembayar baru
curl -X POST http://localhost:8000/api/v1/pembayar \
  -H "Content-Type: application/json" \
  -d '{"nama":"Ali bin Abu","no_ic":"990101015555","alamat":"123 Jalan Putra, Alor Setar","no_tel":"0123456789"}'

# Lihat pembayar #1 (dengan pembayaran)
curl http://localhost:8000/api/v1/pembayar/1

# Kemaskini pembayar #1
curl -X PUT http://localhost:8000/api/v1/pembayar/1 \
  -H "Content-Type: application/json" \
  -d '{"nama":"Ahmad bin Ismail","no_ic":"850315085123","alamat":"456 Jalan Baru, Sungai Petani","no_tel":"0124567890"}'

# Padam pembayar #1
curl -X DELETE http://localhost:8000/api/v1/pembayar/1

# Statistik sistem
curl http://localhost:8000/api/v1/statistik
```

> **Tip:** Anda juga boleh menggunakan **Thunder Client** (extension VS Code) untuk menguji API dengan antaramuka grafik.

---

## Struktur Fail Hari 4 (Fail Baru Sahaja)

```
sistem-zakat/
├── app/
│   ├── Events/
│   │   └── PembayaranDibuat.php               ← Event pembayaran baru
│   ├── Http/Controllers/
│   │   ├── Api/
│   │   │   ├── PembayarApiController.php      ← REST API pembayar
│   │   │   └── StatistikApiController.php     ← API statistik
│   │   ├── DashboardController.php            ← Paparan dashboard
│   │   └── ExportController.php               ← Eksport CSV
│   ├── Listeners/
│   │   ├── HantarNotifikasiPembayaran.php     ← Listener notifikasi
│   │   └── LogPembayaranBaru.php              ← Listener log
│   └── Providers/
│       └── AppServiceProvider.php             ← Dikemaskini (event registration)
├── database/migrations/
│   └── 2024_01_01_000004_add_gambar_to_...    ← Migrasi lajur gambar
├── resources/views/
│   ├── components/
│   │   └── stat-card.blade.php                ← Komponen kad statistik
│   ├── dashboard.blade.php                    ← Paparan dashboard
│   ├── pembayar/
│   │   ├── create.blade.php                   ← Dikemaskini (gambar upload)
│   │   ├── edit.blade.php                     ← Dikemaskini (gambar upload)
│   │   ├── index.blade.php                    ← Dikemaskini (butang eksport)
│   │   └── show.blade.php                     ← Dikemaskini (papar gambar)
│   └── pembayaran/
│       └── index.blade.php                    ← Dikemaskini (butang eksport)
├── routes/
│   ├── api.php                                ← Laluan API
│   └── web.php                                ← Dikemaskini (laluan eksport)
├── tests/Feature/
│   ├── ApiPembayarTest.php                    ← Ujian API
│   └── PembayarTest.php                       ← Ujian feature
└── bootstrap/
    └── app.php                                ← Dikemaskini (tambah api route)
```

---

## Rumusan

| Topik | Perkara Yang Dipelajari |
|-------|------------------------|
| **REST API** | `response()->json()`, kod status HTTP (200/201), `apiResource`, struktur respons konsisten `{status, message, data}` |
| **Dashboard** | Eloquent aggregates (`count`, `sum`), `whereMonth`, `whereYear`, `DB::raw` untuk ringkasan bulanan |
| **Storan Fail** | `Storage::disk('public')`, `store()`, `Storage::url()`, `storage:link`, `enctype="multipart/form-data"` |
| **Events & Listeners** | `Event::listen()`, `dispatch()`, satu event dengan berbilang listener, audit log |
| **Testing (PHPUnit)** | `RefreshDatabase`, `actingAs()`, `assertDatabaseHas()`, `assertJson()`, `getJson()` |
| **Eksport CSV** | `StreamedResponse`, `streamDownload()`, `fputcsv()`, `chunk()` untuk jimat memori |
| **Hubungan Lanjutan** | Eager loading (`with`, `load`), `withCount`, `withSum`, `has`, `doesntHave`, `whereHas` |

Dengan ini, Sistem Pengurusan Zakat Kedah sudah lengkap sebagai aplikasi web dan API yang berfungsi sepenuhnya — termasuk muat naik fail, events, ujian automatik, dan eksport data.
