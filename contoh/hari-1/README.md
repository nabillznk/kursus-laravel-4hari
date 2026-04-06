# Hari 1 — Panduan Bengkel: Sistem Pengurusan Zakat Kedah

Panduan langkah demi langkah untuk membina **Sistem Pengurusan Zakat** menggunakan Laravel. Pada akhir bengkel ini (6 jam), anda akan mempunyai aplikasi CRUD lengkap untuk mengurus maklumat pembayar zakat.

**Apa yang akan dibina:**
- Model & migrasi pangkalan data untuk jadual `pembayars`
- Pengawal sumber (resource controller) dengan 7 kaedah CRUD
- 4 paparan Blade dengan Tailwind CSS (senarai, daftar, lihat, kemaskini)
- Carian, pengesahan borang, dan data contoh (seeder)

---

## Persediaan

### 1. Cipta Pangkalan Data

Buka **HeidiSQL** (atau terminal MySQL) dan jalankan:

```sql
CREATE DATABASE zakat_kedah;
```

### 2. Cipta Projek Laravel

Buka terminal (Command Prompt atau Git Bash) dan jalankan:

```bash
composer create-project laravel/laravel sistem-zakat
cd sistem-zakat
```

### 3. Konfigurasi Fail `.env`

Buka fail `.env` di root projek dan kemaskini tetapan pangkalan data:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zakat_kedah
DB_USERNAME=root
DB_PASSWORD=
```

> **Nota:** Jika menggunakan Laragon, kata laluan MySQL biasanya kosong. Jika menggunakan XAMPP, semak kata laluan MySQL anda.

### 4. Jana Kunci Aplikasi

```bash
php artisan key:generate
```

### 5. Uji Sambungan

Jalankan pelayan pembangunan untuk memastikan semuanya berfungsi:

```bash
php artisan serve
```

Buka pelayar dan layari [http://localhost:8000](http://localhost:8000). Anda sepatutnya nampak halaman selamat datang Laravel.

Tekan `Ctrl+C` di terminal untuk hentikan pelayan buat sementara.

---

## Langkah 1: Cipta Model & Migrasi

Model mewakili jadual dalam pangkalan data. Migrasi pula adalah skrip PHP untuk mencipta/mengubah struktur jadual.

### Jalankan arahan Artisan:

```bash
php artisan make:model Pembayar -m
```

> Bendera `-m` akan mencipta fail migrasi sekali gus bersama model.

Arahan ini mencipta dua fail:
- `app/Models/Pembayar.php` — Model Eloquent
- `database/migrations/xxxx_xx_xx_xxxxxx_create_pembayars_table.php` — Fail migrasi

### Edit fail migrasi

Buka fail migrasi di `database/migrations/xxxx_xx_xx_xxxxxx_create_pembayars_table.php`.

Gantikan **keseluruhan kandungan** fail dengan kod berikut:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk jadual pembayars
 * Menyimpan maklumat pembayar zakat
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi — cipta jadual pembayars.
     */
    public function up(): void
    {
        Schema::create('pembayars', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_ic', 12)->unique();
            $table->text('alamat');
            $table->string('no_tel', 15);
            $table->string('email')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->decimal('pendapatan_bulanan', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Undur migrasi — padam jadual pembayars.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayars');
    }
};
```

**Penerangan medan:**

| Medan | Jenis | Keterangan |
|-------|-------|------------|
| `id` | Auto-increment | Kunci utama, dijana automatik |
| `nama` | `string` | Nama penuh pembayar (wajib) |
| `no_ic` | `string(12)` | No. kad pengenalan, 12 digit, unik |
| `alamat` | `text` | Alamat penuh (wajib) |
| `no_tel` | `string(15)` | Nombor telefon (wajib) |
| `email` | `string` | E-mel (pilihan) |
| `pekerjaan` | `string` | Pekerjaan (pilihan) |
| `pendapatan_bulanan` | `decimal(10,2)` | Pendapatan bulanan dalam RM (pilihan) |
| `timestamps` | — | `created_at` dan `updated_at`, dijana automatik |

### Edit fail model

Buka `app/Models/Pembayar.php` dan gantikan **keseluruhan kandungan** dengan:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Pembayar
 *
 * Mewakili pembayar zakat dalam sistem.
 * Setiap pembayar mempunyai maklumat peribadi seperti
 * nama, no IC, alamat, dan pendapatan bulanan.
 */
class Pembayar extends Model
{
    use HasFactory;

    /**
     * Nama jadual dalam pangkalan data.
     */
    protected $table = 'pembayars';

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
     * Penukaran jenis data (casting).
     */
    protected $casts = [
        'pendapatan_bulanan' => 'decimal:2',
    ];

    /**
     * Skop carian — cari mengikut nama atau no IC.
     */
    public function scopeCarian($query, $katakunci)
    {
        return $query->where('nama', 'like', "%{$katakunci}%")
                     ->orWhere('no_ic', 'like', "%{$katakunci}%");
    }

    /**
     * Aksesor: Format no IC dengan sengkang (850101-14-5678).
     */
    public function getNoIcBerformatAttribute(): string
    {
        $ic = $this->no_ic;

        if (strlen($ic) === 12) {
            return substr($ic, 0, 6) . '-' . substr($ic, 6, 2) . '-' . substr($ic, 8, 4);
        }

        return $ic;
    }

    /**
     * Aksesor: Format pendapatan dengan awalan RM.
     */
    public function getPendapatanBerformatAttribute(): string
    {
        if ($this->pendapatan_bulanan) {
            return 'RM ' . number_format($this->pendapatan_bulanan, 2);
        }

        return 'Tidak dinyatakan';
    }
}
```

**Konsep penting dalam model:**

- **`$fillable`** — Senarai medan yang boleh diisi melalui `create()` atau `update()`. Ini melindungi daripada serangan mass-assignment.
- **`$casts`** — Menukar jenis data secara automatik. `decimal:2` memastikan pendapatan sentiasa 2 titik perpuluhan.
- **`scopeCarian()`** — Skop query tersuai. Boleh dipanggil sebagai `Pembayar::carian('Ahmad')`.
- **Aksesor** (`getXxxAttribute`) — Menghasilkan atribut maya. Contoh: `$pembayar->no_ic_berformat` mengembalikan `850101-14-5678`.

### Jalankan migrasi

```bash
php artisan migrate
```

Anda sepatutnya nampak output seperti:

```
Running migrations.
2024_01_01_000001_create_pembayars_table .... DONE
```

---

## Langkah 2: Cipta Pengawal

Pengawal (controller) mengandungi logik untuk setiap operasi CRUD.

### Jalankan arahan Artisan:

```bash
php artisan make:controller PembayarController --resource
```

> Bendera `--resource` mencipta pengawal dengan 7 kaedah CRUD sedia ada: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.

### Edit pengawal

Buka `app/Http/Controllers/PembayarController.php` dan gantikan **keseluruhan kandungan** dengan:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pembayar;
use Illuminate\Http\Request;

/**
 * Pengawal untuk pengurusan pembayar zakat.
 * Mengandungi 7 kaedah CRUD (index, create, store, show, edit, update, destroy).
 */
class PembayarController extends Controller
{
    /**
     * Papar senarai semua pembayar.
     * Menyokong carian mengikut nama atau no IC.
     */
    public function index(Request $request)
    {
        $carian = $request->query('carian');

        $pembayars = Pembayar::query()
            ->when($carian, function ($query, $carian) {
                return $query->carian($carian);
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('pembayar.index', compact('pembayars', 'carian'));
    }

    /**
     * Papar borang daftar pembayar baru.
     */
    public function create()
    {
        return view('pembayar.create');
    }

    /**
     * Simpan pembayar baru ke pangkalan data.
     */
    public function store(Request $request)
    {
        $disahkan = $request->validate([
            'nama' => 'required|string|max:255',
            'no_ic' => 'required|string|size:12|unique:pembayars,no_ic',
            'alamat' => 'required|string',
            'no_tel' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'pekerjaan' => 'nullable|string|max:255',
            'pendapatan_bulanan' => 'nullable|numeric|min:0',
        ], [
            'nama.required' => 'Sila masukkan nama penuh.',
            'nama.max' => 'Nama tidak boleh melebihi 255 aksara.',
            'no_ic.required' => 'Sila masukkan nombor kad pengenalan.',
            'no_ic.size' => 'Nombor IC mestilah 12 digit.',
            'no_ic.unique' => 'Nombor IC ini telah didaftarkan.',
            'alamat.required' => 'Sila masukkan alamat.',
            'no_tel.required' => 'Sila masukkan nombor telefon.',
            'no_tel.max' => 'Nombor telefon tidak boleh melebihi 15 aksara.',
            'email.email' => 'Format e-mel tidak sah.',
            'pendapatan_bulanan.numeric' => 'Pendapatan mestilah nombor.',
            'pendapatan_bulanan.min' => 'Pendapatan tidak boleh kurang daripada 0.',
        ]);

        Pembayar::create($disahkan);

        return redirect()
            ->route('pembayar.index')
            ->with('success', 'Pembayar berjaya didaftarkan!');
    }

    /**
     * Papar maklumat terperinci seorang pembayar.
     */
    public function show(Pembayar $pembayar)
    {
        return view('pembayar.show', compact('pembayar'));
    }

    /**
     * Papar borang kemaskini maklumat pembayar.
     */
    public function edit(Pembayar $pembayar)
    {
        return view('pembayar.edit', compact('pembayar'));
    }

    /**
     * Kemaskini maklumat pembayar dalam pangkalan data.
     */
    public function update(Request $request, Pembayar $pembayar)
    {
        $disahkan = $request->validate([
            'nama' => 'required|string|max:255',
            'no_ic' => 'required|string|size:12|unique:pembayars,no_ic,' . $pembayar->id,
            'alamat' => 'required|string',
            'no_tel' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'pekerjaan' => 'nullable|string|max:255',
            'pendapatan_bulanan' => 'nullable|numeric|min:0',
        ], [
            'nama.required' => 'Sila masukkan nama penuh.',
            'nama.max' => 'Nama tidak boleh melebihi 255 aksara.',
            'no_ic.required' => 'Sila masukkan nombor kad pengenalan.',
            'no_ic.size' => 'Nombor IC mestilah 12 digit.',
            'no_ic.unique' => 'Nombor IC ini telah didaftarkan oleh pembayar lain.',
            'alamat.required' => 'Sila masukkan alamat.',
            'no_tel.required' => 'Sila masukkan nombor telefon.',
            'no_tel.max' => 'Nombor telefon tidak boleh melebihi 15 aksara.',
            'email.email' => 'Format e-mel tidak sah.',
            'pendapatan_bulanan.numeric' => 'Pendapatan mestilah nombor.',
            'pendapatan_bulanan.min' => 'Pendapatan tidak boleh kurang daripada 0.',
        ]);

        $pembayar->update($disahkan);

        return redirect()
            ->route('pembayar.show', $pembayar)
            ->with('success', 'Maklumat pembayar berjaya dikemaskini!');
    }

    /**
     * Padam pembayar daripada pangkalan data.
     */
    public function destroy(Pembayar $pembayar)
    {
        $nama = $pembayar->nama;
        $pembayar->delete();

        return redirect()
            ->route('pembayar.index')
            ->with('success', "Pembayar \"{$nama}\" berjaya dipadamkan!");
    }
}
```

**Penerangan 7 kaedah CRUD:**

| Kaedah | HTTP | URL | Fungsi |
|--------|------|-----|--------|
| `index()` | GET | `/pembayar` | Papar senarai semua pembayar dengan carian & paginasi |
| `create()` | GET | `/pembayar/create` | Papar borang daftar baru |
| `store()` | POST | `/pembayar` | Simpan data pembayar baru (dengan pengesahan) |
| `show()` | GET | `/pembayar/{id}` | Papar maklumat terperinci seorang pembayar |
| `edit()` | GET | `/pembayar/{id}/edit` | Papar borang kemaskini |
| `update()` | PUT | `/pembayar/{id}` | Kemaskini data pembayar (dengan pengesahan) |
| `destroy()` | DELETE | `/pembayar/{id}` | Padam pembayar |

**Konsep penting:**

- **Pengesahan (`validate`)** — Laravel menyemak data borang secara automatik. Jika gagal, pengguna dikembalikan ke borang dengan mesej ralat.
- **Route Model Binding** — Parameter `Pembayar $pembayar` dalam `show()`, `edit()`, `update()`, dan `destroy()` akan mencari rekod secara automatik berdasarkan ID dalam URL.
- **Mesej flash (`with('success', ...)`)** — Mesej sementara yang dipaparkan sekali sahaja selepas operasi berjaya.

---

## Langkah 3: Tetapkan Laluan (Routes)

Laluan menghubungkan URL dengan kaedah dalam pengawal.

### Edit fail laluan

Buka `routes/web.php` dan gantikan **keseluruhan kandungan** dengan:

```php
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
```

**Penerangan:**

- `Route::get('/')` — Halakan laman utama terus ke senarai pembayar.
- `Route::get('/semak')` — Laluan khas untuk semak status sistem (sambungan DB, versi PHP/Laravel).
- `Route::resource('pembayar', ...)` — **Satu baris ini menjana 7 laluan CRUD secara automatik!**

Untuk melihat semua laluan yang dijana, jalankan:

```bash
php artisan route:list
```

---

## Langkah 4: Cipta Layout Blade

Layout adalah templat induk yang dikongsi oleh semua halaman. Ini mengelakkan pertindihan kod HTML.

### Cipta folder dan fail layout

```bash
mkdir -p resources/views/layouts
```

### Edit fail layout

Cipta fail `resources/views/layouts/app.blade.php` dan tampal kod berikut:

```blade
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('tajuk', 'Sistem Zakat Kedah')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    {{-- Bar Navigasi --}}
    <nav class="bg-emerald-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo & Nama Sistem --}}
                <div class="flex items-center space-x-3">
                    <div class="bg-white rounded-lg p-1.5">
                        <svg class="w-7 h-7 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <a href="{{ route('pembayar.index') }}" class="text-white font-bold text-lg tracking-wide">
                        Sistem Zakat Kedah
                    </a>
                </div>

                {{-- Pautan Navigasi --}}
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('pembayar.index') }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                              {{ request()->routeIs('pembayar.*') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Pembayar
                    </a>
                    <a href="{{ route('semak') }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                              {{ request()->routeIs('semak') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Semak Sistem
                    </a>
                </div>

                {{-- Menu Mudah Alih --}}
                <div class="md:hidden">
                    <button onclick="document.getElementById('menu-mudah-alih').classList.toggle('hidden')"
                            class="text-emerald-100 hover:text-white p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Menu Mudah Alih (tersembunyi) --}}
        <div id="menu-mudah-alih" class="hidden md:hidden bg-emerald-800 pb-3 px-4">
            <a href="{{ route('pembayar.index') }}" class="block px-3 py-2 rounded-lg text-sm text-emerald-100 hover:bg-emerald-600 hover:text-white">
                Pembayar
            </a>
            <a href="{{ route('semak') }}" class="block px-3 py-2 rounded-lg text-sm text-emerald-100 hover:bg-emerald-600 hover:text-white">
                Semak Sistem
            </a>
        </div>
    </nav>

    {{-- Kandungan Utama --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Mesej Flash --}}
        @if (session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-emerald-800 text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-800 text-sm font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Kaki Halaman --}}
    <footer class="bg-emerald-800 text-emerald-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row items-center justify-between space-y-2 md:space-y-0">
                <p class="text-sm">&copy; 2024 Pusat Zakat Negeri Kedah. Hak cipta terpelihara.</p>
                <p class="text-xs text-emerald-400">Sistem Pengurusan Zakat — Kursus Laravel Hari 1</p>
            </div>
        </div>
    </footer>

</body>
</html>
```

**Konsep Blade yang digunakan:**

- `@yield('tajuk', 'Sistem Zakat Kedah')` — Menentukan bahagian yang boleh diisi oleh halaman anak. Nilai kedua adalah nilai lalai.
- `@if (session('success'))` — Memaparkan mesej flash hijau selepas operasi berjaya.
- `{{ csrf_token() }}` — Token keselamatan untuk melindungi daripada serangan CSRF.
- `{{ request()->routeIs('pembayar.*') }}` — Menyemak laluan semasa untuk menyerlahkan pautan navigasi aktif.

---

## Langkah 5: Cipta Paparan CRUD

Kita perlu mencipta 4 paparan Blade untuk operasi CRUD, serta 1 paparan semakan sistem.

### Cipta folder paparan

```bash
mkdir -p resources/views/pembayar
```

### 5a. Paparan Senarai — `index.blade.php`

Cipta fail `resources/views/pembayar/index.blade.php`:

```blade
@extends('layouts.app')

@section('tajuk', 'Senarai Pembayar — Sistem Zakat Kedah')

@section('content')
<div class="space-y-6">

    {{-- Pengepala Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Senarai Pembayar Zakat</h1>
            <p class="text-sm text-gray-500 mt-1">Menunjukkan {{ $pembayars->total() }} pembayar</p>
        </div>
        <a href="{{ route('pembayar.create') }}"
           class="inline-flex items-center px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-emerald-700 transition-colors duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Daftar Pembayar Baru
        </a>
    </div>

    {{-- Bar Carian --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form action="{{ route('pembayar.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="carian" value="{{ $carian }}"
                       placeholder="Cari mengikut nama atau nombor IC..."
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors duration-200">
                    Cari
                </button>
                @if ($carian)
                    <a href="{{ route('pembayar.index') }}"
                       class="px-5 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200">
                        Padam Carian
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Jadual Pembayar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. IC</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Tel</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">E-mel</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($pembayars as $pembayar)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $loop->iteration + ($pembayars->currentPage() - 1) * $pembayars->perPage() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $pembayar->nama }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                                {{ $pembayar->no_ic_berformat }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $pembayar->no_tel }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $pembayar->email ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('pembayar.show', $pembayar) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-100 transition-colors duration-200">
                                        Lihat
                                    </a>
                                    <a href="{{ route('pembayar.edit', $pembayar) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-amber-50 text-amber-700 text-xs font-medium rounded-lg hover:bg-amber-100 transition-colors duration-200">
                                        Kemaskini
                                    </a>
                                    <form action="{{ route('pembayar.destroy', $pembayar) }}" method="POST"
                                          onsubmit="return confirm('Adakah anda pasti ingin memadamkan pembayar {{ $pembayar->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors duration-200">
                                            Padam
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="mt-4 text-sm font-medium text-gray-500">Tiada rekod pembayar</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    @if ($carian)
                                        Tiada hasil ditemui untuk carian "{{ $carian }}".
                                    @else
                                        Klik butang "Daftar Pembayar Baru" untuk menambah pembayar.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pautan Halaman --}}
        @if ($pembayars->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $pembayars->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
```

**Konsep Blade dalam paparan ini:**

- `@extends('layouts.app')` — Mewarisi layout induk.
- `@section('content')` — Mengisi bahagian `@yield('content')` dalam layout.
- `@forelse ... @empty ... @endforelse` — Gelung dengan keadaan kosong.
- `$loop->iteration` — Nombor iterasi semasa dalam gelung.
- `$pembayar->no_ic_berformat` — Memanggil aksesor yang kita cipta dalam model.
- `@csrf` dan `@method('DELETE')` — Token keselamatan dan kaedah HTTP tersembunyi untuk borang padam.

---

### 5b. Paparan Daftar Baru — `create.blade.php`

Cipta fail `resources/views/pembayar/create.blade.php`:

```blade
@extends('layouts.app')

@section('tajuk', 'Daftar Pembayar Baru — Sistem Zakat Kedah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('pembayar.index') }}" class="hover:text-emerald-600 transition-colors">Pembayar</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Daftar Baru</span>
    </nav>

    {{-- Pengepala --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Daftar Pembayar Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Isi maklumat pembayar zakat di bawah. Medan bertanda <span class="text-red-500">*</span> wajib diisi.</p>
    </div>

    {{-- Borang --}}
    <form action="{{ route('pembayar.store') }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf

        {{-- Nama Penuh --}}
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">
                Nama Penuh <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" id="nama" value="{{ old('nama') }}"
                   class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                          {{ $errors->has('nama') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                          focus:ring-2"
                   placeholder="cth: Ahmad bin Ibrahim">
            @error('nama')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- No. Kad Pengenalan --}}
        <div>
            <label for="no_ic" class="block text-sm font-medium text-gray-700 mb-1">
                No. Kad Pengenalan <span class="text-red-500">*</span>
            </label>
            <input type="text" name="no_ic" id="no_ic" value="{{ old('no_ic') }}"
                   maxlength="12"
                   class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                          {{ $errors->has('no_ic') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                          focus:ring-2"
                   placeholder="850101145678">
            @error('no_ic')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Alamat --}}
        <div>
            <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">
                Alamat <span class="text-red-500">*</span>
            </label>
            <textarea name="alamat" id="alamat" rows="3"
                      class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                             {{ $errors->has('alamat') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                             focus:ring-2"
                      placeholder="Alamat penuh termasuk poskod dan negeri">{{ old('alamat') }}</textarea>
            @error('alamat')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- No. Telefon & E-mel (sebelah-menyebelah) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="no_tel" class="block text-sm font-medium text-gray-700 mb-1">
                    No. Telefon <span class="text-red-500">*</span>
                </label>
                <input type="text" name="no_tel" id="no_tel" value="{{ old('no_tel') }}"
                       maxlength="15"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                              {{ $errors->has('no_tel') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                              focus:ring-2"
                       placeholder="0124567890">
                @error('no_tel')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    E-mel
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                              {{ $errors->has('email') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                              focus:ring-2"
                       placeholder="contoh@email.com">
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Pekerjaan & Pendapatan (sebelah-menyebelah) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-1">
                    Pekerjaan
                </label>
                <input type="text" name="pekerjaan" id="pekerjaan" value="{{ old('pekerjaan') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                       placeholder="cth: Guru, Jurutera, Peniaga">
                @error('pekerjaan')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pendapatan_bulanan" class="block text-sm font-medium text-gray-700 mb-1">
                    Pendapatan Bulanan (RM)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm">RM</span>
                    </div>
                    <input type="number" name="pendapatan_bulanan" id="pendapatan_bulanan"
                           value="{{ old('pendapatan_bulanan') }}"
                           step="0.01" min="0"
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                           placeholder="0.00">
                </div>
                @error('pendapatan_bulanan')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Butang --}}
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('pembayar.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700 transition-colors duration-200">
                Simpan
            </button>
        </div>
    </form>

</div>
@endsection
```

**Konsep borang Blade:**

- `@csrf` — Wajib dalam setiap borang POST. Melindungi daripada serangan CSRF.
- `{{ old('nama') }}` — Mengekalkan nilai input jika pengesahan gagal.
- `@error('nama') ... @enderror` — Memaparkan mesej ralat untuk medan tertentu.
- `$errors->has('nama')` — Menukar warna sempadan input jika ada ralat.

---

### 5c. Paparan Terperinci — `show.blade.php`

Cipta fail `resources/views/pembayar/show.blade.php`:

```blade
@extends('layouts.app')

@section('tajuk', $pembayar->nama . ' — Sistem Zakat Kedah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('pembayar.index') }}" class="hover:text-emerald-600 transition-colors">Pembayar</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">{{ $pembayar->nama }}</span>
    </nav>

    {{-- Pengepala --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $pembayar->nama }}</h1>
            <p class="text-sm text-gray-500 mt-1">Didaftarkan pada {{ $pembayar->created_at->format('d/m/Y, h:i A') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('pembayar.edit', $pembayar) }}"
               class="inline-flex items-center px-4 py-2 bg-amber-50 text-amber-700 text-sm font-medium rounded-lg hover:bg-amber-100 transition-colors duration-200 border border-amber-200">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Kemaskini
            </a>
            <form action="{{ route('pembayar.destroy', $pembayar) }}" method="POST"
                  onsubmit="return confirm('Adakah anda pasti ingin memadamkan pembayar ini?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors duration-200 border border-red-200">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Padam
                </button>
            </form>
        </div>
    </div>

    {{-- Kad Maklumat --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-emerald-50 border-b border-emerald-100 px-6 py-4">
            <h2 class="text-sm font-semibold text-emerald-800 uppercase tracking-wider">Maklumat Peribadi</h2>
        </div>
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Penuh</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $pembayar->nama }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">No. Kad Pengenalan</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $pembayar->no_ic_berformat }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->alamat }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">No. Telefon</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->no_tel }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">E-mel</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->email ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pekerjaan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->pekerjaan ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan Bulanan</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $pembayar->pendapatan_berformat }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tarikh Daftar</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->created_at->format('d/m/Y') }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Dikemaskini</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->updated_at->format('d/m/Y, h:i A') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Butang Kembali --}}
    <div>
        <a href="{{ route('pembayar.index') }}"
           class="inline-flex items-center text-sm text-gray-600 hover:text-emerald-600 transition-colors duration-200">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Senarai
        </a>
    </div>

</div>
@endsection
```

**Konsep dalam paparan ini:**

- `$pembayar->no_ic_berformat` — Aksesor yang memformat IC sebagai `850101-14-5678`.
- `$pembayar->pendapatan_berformat` — Aksesor yang memformat pendapatan sebagai `RM 4,500.00`.
- `$pembayar->email ?? '—'` — Operator null coalescing; papar `—` jika e-mel kosong.
- `@method('DELETE')` — Menjana input tersembunyi `_method=DELETE` kerana borang HTML hanya menyokong GET dan POST.

---

### 5d. Paparan Kemaskini — `edit.blade.php`

Cipta fail `resources/views/pembayar/edit.blade.php`:

```blade
@extends('layouts.app')

@section('tajuk', 'Kemaskini ' . $pembayar->nama . ' — Sistem Zakat Kedah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('pembayar.index') }}" class="hover:text-emerald-600 transition-colors">Pembayar</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('pembayar.show', $pembayar) }}" class="hover:text-emerald-600 transition-colors">{{ $pembayar->nama }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Kemaskini</span>
    </nav>

    {{-- Pengepala --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kemaskini Pembayar</h1>
        <p class="text-sm text-gray-500 mt-1">Kemaskini maklumat untuk <span class="font-medium text-gray-700">{{ $pembayar->nama }}</span></p>
    </div>

    {{-- Borang --}}
    <form action="{{ route('pembayar.update', $pembayar) }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Nama Penuh --}}
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">
                Nama Penuh <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" id="nama" value="{{ old('nama', $pembayar->nama) }}"
                   class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                          {{ $errors->has('nama') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                          focus:ring-2">
            @error('nama')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- No. Kad Pengenalan --}}
        <div>
            <label for="no_ic" class="block text-sm font-medium text-gray-700 mb-1">
                No. Kad Pengenalan <span class="text-red-500">*</span>
            </label>
            <input type="text" name="no_ic" id="no_ic" value="{{ old('no_ic', $pembayar->no_ic) }}"
                   maxlength="12"
                   class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                          {{ $errors->has('no_ic') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                          focus:ring-2"
                   placeholder="850101145678">
            @error('no_ic')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Alamat --}}
        <div>
            <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">
                Alamat <span class="text-red-500">*</span>
            </label>
            <textarea name="alamat" id="alamat" rows="3"
                      class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                             {{ $errors->has('alamat') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                             focus:ring-2">{{ old('alamat', $pembayar->alamat) }}</textarea>
            @error('alamat')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- No. Telefon & E-mel --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="no_tel" class="block text-sm font-medium text-gray-700 mb-1">
                    No. Telefon <span class="text-red-500">*</span>
                </label>
                <input type="text" name="no_tel" id="no_tel" value="{{ old('no_tel', $pembayar->no_tel) }}"
                       maxlength="15"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                              {{ $errors->has('no_tel') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                              focus:ring-2"
                       placeholder="0124567890">
                @error('no_tel')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    E-mel
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $pembayar->email) }}"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                              {{ $errors->has('email') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                              focus:ring-2"
                       placeholder="contoh@email.com">
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Pekerjaan & Pendapatan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-1">
                    Pekerjaan
                </label>
                <input type="text" name="pekerjaan" id="pekerjaan" value="{{ old('pekerjaan', $pembayar->pekerjaan) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                       placeholder="cth: Guru, Jurutera, Peniaga">
                @error('pekerjaan')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pendapatan_bulanan" class="block text-sm font-medium text-gray-700 mb-1">
                    Pendapatan Bulanan (RM)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm">RM</span>
                    </div>
                    <input type="number" name="pendapatan_bulanan" id="pendapatan_bulanan"
                           value="{{ old('pendapatan_bulanan', $pembayar->pendapatan_bulanan) }}"
                           step="0.01" min="0"
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                           placeholder="0.00">
                </div>
                @error('pendapatan_bulanan')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Butang --}}
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('pembayar.show', $pembayar) }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700 transition-colors duration-200">
                Kemaskini
            </button>
        </div>
    </form>

</div>
@endsection
```

**Perbezaan utama antara `create.blade.php` dan `edit.blade.php`:**

- Borang edit menggunakan `@method('PUT')` kerana HTTP PUT digunakan untuk kemaskini.
- Nilai input menggunakan `old('nama', $pembayar->nama)` — mengekalkan input lama jika pengesahan gagal, atau papar nilai semasa daripada pangkalan data.
- Butang hantar berlabel "Kemaskini" dan bukannya "Simpan".

---

### 5e. Paparan Semakan Sistem — `semak.blade.php`

Cipta fail `resources/views/semak.blade.php`:

```blade
@extends('layouts.app')

@section('tajuk', 'Semakan Sistem — Sistem Zakat Kedah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Pengepala --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Semakan Sistem</h1>
        <p class="text-sm text-gray-500 mt-1">Maklumat persekitaran dan status sistem semasa.</p>
    </div>

    {{-- Kad Semakan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Versi PHP --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Versi PHP</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $versi_php }}</p>
                </div>
            </div>
        </div>

        {{-- Versi Laravel --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Versi Laravel</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $versi_laravel }}</p>
                </div>
            </div>
        </div>

        {{-- Pangkalan Data --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center
                            {{ $pangkalan_data_ok ? 'bg-emerald-100' : 'bg-red-100' }}">
                    @if ($pangkalan_data_ok)
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pangkalan Data</p>
                    <p class="text-sm font-medium mt-0.5 {{ $pangkalan_data_ok ? 'text-emerald-700' : 'text-red-700' }}">
                        {{ $pangkalan_data_ok ? 'Berjaya' : 'Gagal' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ $pangkalan_data_mesej }}</p>
                </div>
            </div>
        </div>

        {{-- Pelayan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pelayan</p>
                    <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $pelayan }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Maklumat Tambahan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Maklumat Tambahan</h2>
        </div>
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Persekitaran</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     {{ $persekitaran === 'production' ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ ucfirst($persekitaran) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Masa Semakan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ now()->format('d/m/Y, h:i:s A') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sistem Operasi</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ PHP_OS }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sambungan PHP</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @foreach (['pdo_mysql', 'mbstring', 'openssl', 'tokenizer'] as $ext)
                            <span class="inline-flex items-center mr-1 mb-1 px-2 py-0.5 rounded text-xs
                                         {{ extension_loaded($ext) ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                {{ $ext }}
                                @if (extension_loaded($ext))
                                    &#10003;
                                @else
                                    &#10007;
                                @endif
                            </span>
                        @endforeach
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Butang Kembali --}}
    <div>
        <a href="{{ route('pembayar.index') }}"
           class="inline-flex items-center text-sm text-gray-600 hover:text-emerald-600 transition-colors duration-200">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Laman Utama
        </a>
    </div>

</div>
@endsection
```

---

## Langkah 6: Cipta Seeder

Seeder digunakan untuk mengisi data contoh ke dalam pangkalan data. Ini memudahkan ujian tanpa perlu memasukkan data secara manual.

### Jalankan arahan Artisan:

```bash
php artisan make:seeder PembayarSeeder
```

### Edit fail seeder

Buka `database/seeders/PembayarSeeder.php` dan gantikan **keseluruhan kandungan** dengan:

```php
<?php

namespace Database\Seeders;

use App\Models\Pembayar;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk jadual pembayars.
 * Mencipta 10 rekod contoh pembayar zakat.
 */
class PembayarSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $pembayars = [
            [
                'nama' => 'Ahmad bin Ibrahim',
                'no_ic' => '850315085123',
                'alamat' => 'No. 12, Jalan Putra, Taman Putra, 05150 Alor Setar, Kedah',
                'no_tel' => '0124567890',
                'email' => 'ahmad.ibrahim@gmail.com',
                'pekerjaan' => 'Guru',
                'pendapatan_bulanan' => 4500.00,
            ],
            [
                'nama' => 'Siti Aminah binti Abdullah',
                'no_ic' => '900722025678',
                'alamat' => 'Lot 45, Kampung Baru, 06000 Jitra, Kedah',
                'no_tel' => '0139876543',
                'email' => 'siti.aminah@yahoo.com',
                'pekerjaan' => 'Jururawat',
                'pendapatan_bulanan' => 3800.00,
            ],
            [
                'nama' => 'Mohd Faizal bin Hassan',
                'no_ic' => '880405145234',
                'alamat' => 'No. 78, Lorong Cempaka 3, 08000 Sungai Petani, Kedah',
                'no_tel' => '0171234567',
                'email' => 'faizal.hassan@hotmail.com',
                'pekerjaan' => 'Jurutera',
                'pendapatan_bulanan' => 6500.00,
            ],
            [
                'nama' => 'Norazlina binti Yusof',
                'no_ic' => '920118025890',
                'alamat' => 'Blok C-3-5, Pangsapuri Seri Kedah, 05400 Alor Setar, Kedah',
                'no_tel' => '0168765432',
                'email' => null,
                'pekerjaan' => 'Kerani',
                'pendapatan_bulanan' => 2800.00,
            ],
            [
                'nama' => 'Ismail bin Osman',
                'no_ic' => '780930025345',
                'alamat' => 'No. 5, Jalan Pekan, 06600 Kuala Kedah, Kedah',
                'no_tel' => '0192345678',
                'email' => 'ismail.osman@gmail.com',
                'pekerjaan' => 'Nelayan',
                'pendapatan_bulanan' => 2200.00,
            ],
            [
                'nama' => 'Fatimah binti Zakaria',
                'no_ic' => '950625085567',
                'alamat' => 'No. 33, Taman Harmoni, 09000 Kulim, Kedah',
                'no_tel' => '0143456789',
                'email' => 'fatimah.z@gmail.com',
                'pekerjaan' => 'Pegawai Bank',
                'pendapatan_bulanan' => 5200.00,
            ],
            [
                'nama' => 'Razak bin Mohd Nor',
                'no_ic' => '830212145789',
                'alamat' => 'Kampung Sungai Daun, 06300 Kuala Nerang, Kedah',
                'no_tel' => '0185678901',
                'email' => null,
                'pekerjaan' => 'Petani',
                'pendapatan_bulanan' => 1800.00,
            ],
            [
                'nama' => 'Nur Hidayah binti Ramli',
                'no_ic' => '970803025901',
                'alamat' => 'No. 67, Jalan Sultan Badlishah, 05000 Alor Setar, Kedah',
                'no_tel' => '0116789012',
                'email' => 'hidayah.ramli@outlook.com',
                'pekerjaan' => 'Pereka Grafik',
                'pendapatan_bulanan' => 3500.00,
            ],
            [
                'nama' => 'Kamaruddin bin Mat Zin',
                'no_ic' => '750519085234',
                'alamat' => 'No. 101, Pekan Pokok Sena, 06400 Pokok Sena, Kedah',
                'no_tel' => '0197890123',
                'email' => 'kamaruddin.mz@gmail.com',
                'pekerjaan' => 'Peniaga',
                'pendapatan_bulanan' => 7000.00,
            ],
            [
                'nama' => 'Zainab binti Ahmad',
                'no_ic' => '880916025456',
                'alamat' => 'No. 22, Taman Desa Permai, 09100 Baling, Kedah',
                'no_tel' => '0158901234',
                'email' => null,
                'pekerjaan' => 'Suri Rumah',
                'pendapatan_bulanan' => null,
            ],
        ];

        foreach ($pembayars as $data) {
            Pembayar::create($data);
        }
    }
}
```

### Daftar seeder dalam DatabaseSeeder

Buka `database/seeders/DatabaseSeeder.php` dan gantikan **keseluruhan kandungan** dengan:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Jalankan seeder pangkalan data.
     */
    public function run(): void
    {
        $this->call([
            PembayarSeeder::class,
        ]);
    }
}
```

### Jalankan seeder

```bash
php artisan db:seed
```

Anda sepatutnya nampak output:

```
Seeding database.
Running Database\Seeders\PembayarSeeder ..... DONE
```

---

## Langkah 7: Jalankan & Uji

### Hidupkan pelayan pembangunan

```bash
php artisan serve
```

### Uji setiap fungsi

Buka pelayar dan uji URL berikut satu persatu:

| # | URL | Apa yang dijangka |
|---|-----|-------------------|
| 1 | [http://localhost:8000](http://localhost:8000) | Dihala ke senarai pembayar |
| 2 | [http://localhost:8000/pembayar](http://localhost:8000/pembayar) | Senarai 10 pembayar daripada seeder |
| 3 | [http://localhost:8000/pembayar/create](http://localhost:8000/pembayar/create) | Borang daftar pembayar baru |
| 4 | [http://localhost:8000/pembayar/1](http://localhost:8000/pembayar/1) | Maklumat terperinci Ahmad bin Ibrahim |
| 5 | [http://localhost:8000/pembayar/1/edit](http://localhost:8000/pembayar/1/edit) | Borang kemaskini Ahmad bin Ibrahim |
| 6 | [http://localhost:8000/semak](http://localhost:8000/semak) | Semakan sistem (versi PHP, Laravel, status DB) |

### Uji fungsi CRUD

1. **Cipta** — Klik "Daftar Pembayar Baru", isi borang, klik "Simpan". Anda sepatutnya nampak mesej hijau "Pembayar berjaya didaftarkan!"
2. **Baca** — Klik "Lihat" pada mana-mana pembayar untuk melihat maklumat terperinci.
3. **Kemaskini** — Klik "Kemaskini", ubah mana-mana medan, klik "Kemaskini". Anda sepatutnya nampak mesej "Maklumat pembayar berjaya dikemaskini!"
4. **Padam** — Klik "Padam" pada mana-mana pembayar, sahkan dialog, dan pembayar akan dipadamkan.
5. **Carian** — Taip nama atau no IC dalam kotak carian dan klik "Cari".

### Uji pengesahan borang

Cuba hantar borang kosong (tanpa mengisi apa-apa). Anda sepatutnya nampak mesej ralat dalam Bahasa Melayu di bawah setiap medan wajib.

---

## Ringkasan Fail Yang Dicipta

```
sistem-zakat/
├── app/
│   ├── Http/Controllers/
│   │   └── PembayarController.php      ← Pengawal CRUD (7 kaedah)
│   └── Models/
│       └── Pembayar.php                ← Model Eloquent (fillable, casts, skop, aksesor)
├── database/
│   ├── migrations/
│   │   └── xxxx_create_pembayars_table.php  ← Migrasi jadual
│   └── seeders/
│       ├── DatabaseSeeder.php          ← Pendaftar seeder
│       └── PembayarSeeder.php          ← 10 rekod contoh
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php              ← Templat induk (navigasi, footer, mesej flash)
│   ├── pembayar/
│   │   ├── index.blade.php            ← Senarai + carian + jadual + paginasi
│   │   ├── create.blade.php           ← Borang daftar baru
│   │   ├── show.blade.php             ← Paparan terperinci
│   │   └── edit.blade.php             ← Borang kemaskini
│   └── semak.blade.php                ← Semakan sistem
└── routes/
    └── web.php                        ← 3 laluan (redirect, semak, resource CRUD)
```

## Konsep Laravel Yang Dipelajari

| Konsep | Penerangan |
|--------|------------|
| **Model Eloquent** | Kelas PHP yang mewakili jadual pangkalan data |
| **Migrasi** | Skrip PHP untuk mencipta/mengubah struktur jadual |
| **Resource Controller** | Pengawal dengan 7 kaedah CRUD sedia ada |
| **Route Model Binding** | Laravel cari rekod secara automatik berdasarkan ID dalam URL |
| **Blade Templates** | Enjin templat Laravel dengan arahan seperti `@extends`, `@section`, `@foreach` |
| **Pengesahan (Validation)** | Semakan data borang secara automatik dengan mesej tersuai |
| **Mass Assignment** | Mengisi berbilang medan sekaligus melalui `$fillable` |
| **Seeder** | Mengisi data contoh ke pangkalan data untuk ujian |
| **Paginasi** | Memaparkan data dalam halaman (10 rekod setiap halaman) |
| **Mesej Flash** | Mesej sementara yang dipaparkan sekali selepas operasi berjaya |

---

**Tahniah!** Anda telah berjaya membina aplikasi CRUD lengkap menggunakan Laravel. Pada Hari 2, kita akan belajar tentang Routes lanjutan, Middleware, dan menambah lebih banyak ciri ke sistem ini.
