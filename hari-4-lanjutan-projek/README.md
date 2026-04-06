# Kursus Laravel 4 Hari | Pusat Zakat Negeri Kedah

## Hari 4: Lanjutan Projek - Pengesahan, Hubungan Model, API & Dashboard

Selamat datang di Hari 4 (hari terakhir) Kursus Laravel 4 Hari kami! Pada hari ini, kami akan menyempurnakan Sistem Pengurusan Zakat dengan menambahkan:

- **Pengesahan Pengguna (Authentication)** - melindungi sistem dengan login/logout
- **Hubungan Model (Relationships)** - menghubungkan data Pembayar, Pembayaran, dan Jenis Zakat
- **API RESTful** - menyediakan akses data melalui API JSON
- **Dashboard & Laporan** - paparan ringkasan statistik zakat
- **Penyebaran (Deployment)** - persiapan untuk production

---

## Modul 4.1: Pengesahan Pengguna (Authentication)

### Pengenalan

Pengesahan pengguna adalah langkah penting untuk melindungi data zakat yang sensitif. Hanya staf berdaftar yang sepatutnya dapat mengakses sistem. Laravel menyediakan alat lengkap untuk menguruskan pengesahan pengguna.

### Pasang Laravel Breeze

Laravel Breeze ialah paket yang menyediakan authentication scaffolding yang mudah dan cepat.

#### Langkah 1: Pasang Package

```bash
composer require laravel/breeze --dev
```

#### Langkah 2: Jalankan Arisan Breeze

```bash
php artisan breeze:install blade
```

Ini akan:
- Menjana fail-fail authentication (login, register, forgot password)
- Menjana migration untuk jadual `users`
- Menjana middleware authentication

#### Langkah 3: Jalankan Migrasi

```bash
php artisan migrate
```

Sekarang jadual `users` telah dicipta di pangkalan data kami.

### Struktur Fail Authentication

Selepas memasang Breeze, struktur fail anda akan berbentuk:

```
resources/views/auth/
├── login.blade.php          # Halaman Login
├── register.blade.php       # Halaman Daftar
├── forgot-password.blade.php
└── reset-password.blade.php

resources/views/layouts/
├── app.blade.php           # Layout dengan navigation

app/Http/Controllers/
├── Auth/AuthenticatedSessionController.php
├── Auth/RegisteredUserController.php
└── Auth/PasswordResetLinkController.php
```

### Menggunakan Authentication dalam Rute

#### Lindungi Rute dengan Middleware `auth`

Dalam `routes/web.php`, tambahkan middleware `auth` untuk melindungi rute zakat:

```php
<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PembayarController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Rute Publik
Route::get('/', function () {
    return view('welcome');
});

// Rute Zakat (Dilindungi oleh Authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pembayar CRUD
    Route::resource('pembayar', PembayarController::class);

    // Pembayaran CRUD
    Route::resource('pembayaran', PembayaranController::class);

    // Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rute Authentication (Login, Register, Logout)
require __DIR__.'/auth.php';
```

### Proses Login/Logout

#### Login

Pengguna melawati `/login` dan memasukkan email dan kata laluan mereka:

```blade
<!-- resources/views/auth/login.blade.php -->
<form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Email Address -->
    <div>
        <label for="email">{{ __('Email') }}</label>
        <input id="email" class="block mt-1 w-full" type="email" name="email" required autofocus>
    </div>

    <!-- Password -->
    <div class="mt-4">
        <label for="password">{{ __('Password') }}</label>
        <input id="password" class="block mt-1 w-full" type="password" name="password" required>
    </div>

    <!-- Remember Me -->
    <div class="block mt-4">
        <label for="remember_me" class="inline-flex items-center">
            <input id="remember_me" type="checkbox" name="remember" value="on">
            <span class="ml-2 text-sm">{{ __('Remember me') }}</span>
        </label>
    </div>

    <div class="flex items-center justify-end mt-4">
        <button type="submit">{{ __('Log in') }}</button>
    </div>
</form>
```

#### Logout

Dalam navigasi atau menu, tambahkan pautan logout:

```blade
<!-- resources/views/layouts/navigation.blade.php -->
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">{{ __('Log Out') }}</button>
</form>
```

### Menyemak Status Authentication

Dalam kod atau view, anda boleh menyemak sama ada pengguna sudah login:

```php
// Dalam Controller
if (Auth::check()) {
    $user = Auth::user(); // Mendapatkan pengguna semasa
}

// Dalam Blade View
@auth
    <p>Selamat datang, {{ auth()->user()->name }}!</p>
@endauth

@guest
    <p>Sila login terlebih dahulu.</p>
@endguest
```

### Daftar Pengguna Baru

Dalam halaman `/register`, pengguna baru boleh mendaftar:

```blade
<!-- resources/views/auth/register.blade.php -->
<form method="POST" action="{{ route('register') }}">
    @csrf

    <!-- Name -->
    <div>
        <label for="name">{{ __('Name') }}</label>
        <input id="name" class="block mt-1 w-full" type="text" name="name" required>
    </div>

    <!-- Email Address -->
    <div class="mt-4">
        <label for="email">{{ __('Email') }}</label>
        <input id="email" class="block mt-1 w-full" type="email" name="email" required>
    </div>

    <!-- Password -->
    <div class="mt-4">
        <label for="password">{{ __('Password') }}</label>
        <input id="password" class="block mt-1 w-full" type="password" name="password" required>
    </div>

    <!-- Confirm Password -->
    <div class="mt-4">
        <label for="password_confirmation">{{ __('Confirm Password') }}</label>
        <input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required>
    </div>

    <button type="submit">{{ __('Register') }}</button>
</form>
```

---

## Modul 4.2: Hubungan Model (Eloquent Relationships)

### Pengenalan Hubungan

Dalam sistem zakat, data-data berkaitan antara satu sama lain. Sebagai contoh:
- Seorang **Pembayar** boleh membuat banyak **Pembayaran**
- Satu **Pembayaran** milik seorang **Pembayar**
- Satu **Pembayaran** adalah untuk satu jenis **Jenis Zakat**
- Satu **Jenis Zakat** mempunyai banyak **Pembayaran**

Laravel Eloquent membuat menguruskan hubungan ini menjadi mudah.

### Jenis-jenis Hubungan

| Hubungan | Deskripsi | Contoh |
|----------|-----------|--------|
| **One-to-Many** | Satu model mempunyai banyak model lain | Pembayar hasMany Pembayaran |
| **Belongs-To** | Model ini milik model lain | Pembayaran belongsTo Pembayar |
| **Many-to-Many** | Dua model boleh mempunyai banyak satu sama lain | (tidak digunakan dalam projek ini) |

### Mentakrifkan Hubungan dalam Model

#### Model Pembayar

```php
<?php
// app/Models/Pembayar.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembayar extends Model
{
    protected $table = 'pembayar';
    protected $fillable = ['nama', 'no_id', 'no_telefon', 'alamat', 'bandar', 'negeri'];
    public $timestamps = true;

    /**
     * Hubungan: Pembayar mempunyai banyak Pembayaran
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'pembayar_id', 'id');
    }

    /**
     * Mendapatkan jumlah pembayaran pembayar ini
     */
    public function jumlahPembayaran()
    {
        return $this->pembayarans()->sum('jumlah');
    }
}
```

#### Model Pembayaran

```php
<?php
// app/Models/Pembayaran.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $fillable = [
        'pembayar_id',
        'jenis_zakat_id',
        'jumlah',
        'no_resit',
        'cara_bayar',
        'tarikh_bayar'
    ];
    public $timestamps = true;

    /**
     * Hubungan: Pembayaran milik seorang Pembayar
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pembayar(): BelongsTo
    {
        return $this->belongsTo(Pembayar::class, 'pembayar_id', 'id');
    }

    /**
     * Hubungan: Pembayaran adalah untuk satu Jenis Zakat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jenisZakat(): BelongsTo
    {
        return $this->belongsTo(JenisZakat::class, 'jenis_zakat_id', 'id');
    }
}
```

#### Model JenisZakat

```php
<?php
// app/Models/JenisZakat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisZakat extends Model
{
    protected $table = 'jenis_zakat';
    protected $fillable = ['nama', 'deskripsi'];
    public $timestamps = true;

    /**
     * Hubungan: Jenis Zakat mempunyai banyak Pembayaran
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'jenis_zakat_id', 'id');
    }

    /**
     * Mendapatkan jumlah kutipan untuk jenis zakat ini
     */
    public function jumlahKutipan()
    {
        return $this->pembayarans()->sum('jumlah');
    }
}
```

### Menggunakan Hubungan

#### Mengakses Data Berkaitan

```php
<?php
// Dalam Controller atau Model

// Mendapatkan pembayar dengan ID 1
$pembayar = Pembayar::find(1);

// Mendapatkan semua pembayaran pembayar ini
$pembayarans = $pembayar->pembayarans; // Menggunakan hubungan

// Atau dengan eager loading (lebih cepat)
$pembayar = Pembayar::with('pembayarans')->find(1);

// Mendapatkan pembayaran dengan ID 5
$pembayaran = Pembayaran::find(5);

// Mendapatkan pembayar yang membuat pembayaran ini
$pembayar = $pembayaran->pembayar;
echo $pembayar->nama; // Output: Nama pembayar

// Mendapatkan jenis zakat untuk pembayaran ini
$jenisZakat = $pembayaran->jenisZakat;
echo $jenisZakat->nama; // Output: Zakat Fitrah (contoh)
```

#### Eager Loading (Meningkatkan Prestasi)

Apabila mengambil data berkaitan, gunakan `with()` untuk mengelakkan "N+1 queries":

```php
<?php
// Tidak optimum (N+1 queries)
$pembayarans = Pembayaran::all();
foreach ($pembayarans as $pembayaran) {
    echo $pembayaran->pembayar->nama; // Query baru setiap kali!
}

// Optimum (2 queries sahaja)
$pembayarans = Pembayaran::with('pembayar', 'jenisZakat')->get();
foreach ($pembayarans as $pembayaran) {
    echo $pembayaran->pembayar->nama; // Tidak ada query tambahan
}
```

#### Mencari dengan Hubungan

```php
<?php
// Mendapatkan semua pembayaran untuk jenis zakat "Zakat Fitrah"
$fitrah = JenisZakat::where('nama', 'Zakat Fitrah')->first();
$pembayarans = $fitrah->pembayarans;

// Atau dengan has() untuk filter
$jenisZakat = JenisZakat::has('pembayarans')->get(); // JenisZakat yang ada pembayaran

// Mendapatkan pembayar yang mempunyai lebih dari 5 pembayaran
$pembayars = Pembayar::whereHas('pembayarans', function ($query) {
    $query->where('jumlah', '>', 5000); // Pembayaran lebih dari RM5000
})->get();
```

---

## Modul 4.3: API RESTful

### Pengenalan API

API (Application Programming Interface) membolehkan aplikasi lain mengakses data sistem kami dalam format JSON. Ini berguna untuk aplikasi mobile atau integrasi pihak ketiga.

### RESTful Principles

REST (Representational State Transfer) menggunakan HTTP verbs untuk operasi CRUD:

| HTTP Verb | Operasi | Rute Contoh |
|-----------|---------|-------------|
| **GET** | Baca | `/api/pembayar` (semua) atau `/api/pembayar/1` (satu) |
| **POST** | Cipta | `/api/pembayar` (dengan data JSON dalam body) |
| **PUT** | Kemaskini | `/api/pembayar/1` (dengan data JSON dalam body) |
| **DELETE** | Padam | `/api/pembayar/1` |

### Mentakrifkan Rute API

Dalam `routes/api.php`, tambahkan rute API:

```php
<?php

use App\Http\Controllers\Api\PembayarApiController;
use Illuminate\Support\Facades\Route;

// Rute API Pembayar
Route::apiResource('pembayar', PembayarApiController::class);

// Opsional: Rute custom API
Route::get('pembayar/{id}/pembayarans', [PembayarApiController::class, 'getPembayarans']);
```

### Membuat API Controller

Buat controller khusus untuk API:

```bash
php artisan make:controller Api/PembayarApiController --api
```

Isi `app/Http/Controllers/Api/PembayarApiController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayar;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PembayarApiController extends Controller
{
    /**
     * GET /api/pembayar
     * Mendapatkan semua pembayar
     */
    public function index(): JsonResponse
    {
        try {
            $pembayars = Pembayar::with('pembayarans')->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Senarai pembayar berjaya diambil',
                'data' => $pembayars
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ralat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/pembayar
     * Cipta pembayar baru
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validasi data
            $validated = $request->validate([
                'nama' => 'required|string|max:100',
                'no_id' => 'required|string|unique:pembayar,no_id',
                'no_telefon' => 'required|string',
                'alamat' => 'required|string',
                'bandar' => 'required|string',
                'negeri' => 'required|string'
            ]);

            // Buat pembayar baru
            $pembayar = Pembayar::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Pembayar berjaya ditambah',
                'data' => $pembayar
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ralat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/pembayar/{id}
     * Mendapatkan pembayar tertentu
     */
    public function show(Pembayar $pembayar): JsonResponse
    {
        try {
            $pembayar->load('pembayarans.jenisZakat');

            return response()->json([
                'success' => true,
                'message' => 'Pembayar berjaya diambil',
                'data' => $pembayar
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ralat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/pembayar/{id}
     * Kemaskini pembayar
     */
    public function update(Request $request, Pembayar $pembayar): JsonResponse
    {
        try {
            // Validasi data
            $validated = $request->validate([
                'nama' => 'sometimes|required|string|max:100',
                'no_id' => 'sometimes|required|string|unique:pembayar,no_id,' . $pembayar->id,
                'no_telefon' => 'sometimes|required|string',
                'alamat' => 'sometimes|required|string',
                'bandar' => 'sometimes|required|string',
                'negeri' => 'sometimes|required|string'
            ]);

            // Kemaskini pembayar
            $pembayar->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Pembayar berjaya dikemaskini',
                'data' => $pembayar
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ralat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/pembayar/{id}
     * Padam pembayar
     */
    public function destroy(Pembayar $pembayar): JsonResponse
    {
        try {
            $pembayar->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pembayar berjaya dipadam'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ralat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/pembayar/{id}/pembayarans
     * Mendapatkan pembayaran pembayar tertentu
     */
    public function getPembayarans(Pembayar $pembayar): JsonResponse
    {
        try {
            $pembayarans = $pembayar->pembayarans()->with('jenisZakat')->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berjaya diambil',
                'data' => $pembayarans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ralat: ' . $e->getMessage()
            ], 500);
        }
    }
}
```

### Menguji API dengan Thunder Client

**Thunder Client** ialah extension VS Code yang mudah untuk menguji API. Instalasi dari VS Code Extensions Marketplace.

#### Ujian GET (Baca Semua Pembayar)

1. Buka Thunder Client
2. Pilih **GET**
3. Masukkan URL: `http://localhost:8000/api/pembayar`
4. Klik **Send**
5. Lihat respons JSON:

```json
{
  "success": true,
  "message": "Senarai pembayar berjaya diambil",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "nama": "Ahmad bin Hasan",
        "no_id": "850101101234",
        "no_telefon": "0125671234",
        "alamat": "123 Jalan Merdeka",
        "bandar": "Alor Setar",
        "negeri": "Kedah",
        "created_at": "2025-01-15T10:30:00.000000Z",
        "updated_at": "2025-01-15T10:30:00.000000Z"
      }
    ]
  }
}
```

#### Ujian GET (Baca Satu Pembayar)

1. Pilih **GET**
2. URL: `http://localhost:8000/api/pembayar/1`
3. Klik **Send**

#### Ujian POST (Cipta Pembayar Baru)

1. Pilih **POST**
2. URL: `http://localhost:8000/api/pembayar`
3. Pergi ke tab **Body** → **JSON**
4. Masukkan:

```json
{
  "nama": "Fatimah binti Ali",
  "no_id": "880515101234",
  "no_telefon": "0167891234",
  "alamat": "456 Jalan Perdana",
  "bandar": "Ipoh",
  "negeri": "Perak"
}
```

5. Klik **Send**

#### Ujian PUT (Kemaskini Pembayar)

1. Pilih **PUT**
2. URL: `http://localhost:8000/api/pembayar/1`
3. Body (JSON):

```json
{
  "nama": "Ahmad bin Hasan Baru",
  "no_telefon": "0125671999"
}
```

4. Klik **Send**

#### Ujian DELETE (Padam Pembayar)

1. Pilih **DELETE**
2. URL: `http://localhost:8000/api/pembayar/1`
3. Klik **Send**

### Menggunakan API dari curl (Terminal)

Anda juga boleh menguji API dengan `curl` di terminal:

```bash
# GET
curl http://localhost:8000/api/pembayar

# POST
curl -X POST http://localhost:8000/api/pembayar \
  -H "Content-Type: application/json" \
  -d '{"nama":"Siti Nur","no_id":"900101101234","no_telefon":"0189991234","alamat":"789 Jalan Raja","bandar":"Kuala Lumpur","negeri":"Wilayah Persekutuan"}'

# PUT
curl -X PUT http://localhost:8000/api/pembayar/1 \
  -H "Content-Type: application/json" \
  -d '{"nama":"Ahmad Baru"}'

# DELETE
curl -X DELETE http://localhost:8000/api/pembayar/1
```

---

## Modul 4.4: Dashboard & Laporan

### Tujuan Dashboard

Dashboard memberikan paparan ringkas tentang:
- Jumlah pembayar berdaftar
- Jumlah kutipan zakat dalam RM
- Kutipan mengikut jenis zakat
- Pembayaran terkini (5 terakhir)

### Membuat DashboardController

```bash
php artisan make:controller DashboardController
```

Isi `app/Http/Controllers/DashboardController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pembayar;
use App\Models\Pembayaran;
use App\Models\JenisZakat;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Paparan dashboard
     */
    public function index(): View
    {
        // Jumlah pembayar berdaftar
        $jumlahPembayar = Pembayar::count();

        // Jumlah kutipan zakat dalam RM
        $jumlahKutipan = Pembayaran::sum('jumlah') ?? 0;

        // Kutipan mengikut jenis zakat
        $kutipanPerJenis = JenisZakat::with('pembayarans')
            ->get()
            ->map(function ($jenis) {
                return [
                    'nama' => $jenis->nama,
                    'jumlah' => $jenis->pembayarans->sum('jumlah'),
                    'bilangan' => $jenis->pembayarans->count()
                ];
            });

        // Pembayaran terkini (5 terakhir)
        $pembayaranTerkini = Pembayaran::with('pembayar', 'jenisZakat')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'jumlahPembayar' => $jumlahPembayar,
            'jumlahKutipan' => $jumlahKutipan,
            'kutipanPerJenis' => $kutipanPerJenis,
            'pembayaranTerkini' => $pembayaranTerkini
        ]);
    }
}
```

### Membuat Dashboard View

Buat `resources/views/dashboard.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Baris 1: Statistik Utama -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Jumlah Pembayar -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Jumlah Pembayar Berdaftar</p>
                                <p class="text-3xl font-bold mt-2">{{ $jumlahPembayar }}</p>
                            </div>
                            <div class="text-4xl">👥</div>
                        </div>
                        <a href="{{ route('pembayar.index') }}" class="text-blue-600 dark:text-blue-400 text-sm mt-4 inline-block">
                            Lihat Semua →
                        </a>
                    </div>
                </div>

                <!-- Jumlah Kutipan -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Jumlah Kutipan Zakat (RM)</p>
                                <p class="text-3xl font-bold mt-2">RM {{ number_format($jumlahKutipan, 2) }}</p>
                            </div>
                            <div class="text-4xl">💰</div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 text-xs mt-4">
                            Dari {{ Illuminate\Support\Facades\DB::table('pembayaran')->count() }} pembayaran
                        </p>
                    </div>
                </div>
            </div>

            <!-- Baris 2: Kutipan Mengikut Jenis Zakat -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Kutipan Mengikut Jenis Zakat</h3>

                    @if($kutipanPerJenis->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">Tiada data kutipan</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">Jenis Zakat</th>
                                        <th class="px-4 py-2 text-right text-sm font-semibold">Jumlah (RM)</th>
                                        <th class="px-4 py-2 text-right text-sm font-semibold">Bilangan Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kutipanPerJenis as $jenis)
                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                            <td class="px-4 py-3">{{ $jenis['nama'] }}</td>
                                            <td class="px-4 py-3 text-right font-semibold">
                                                RM {{ number_format($jenis['jumlah'], 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right">{{ $jenis['bilangan'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Baris 3: Pembayaran Terkini -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Pembayaran Terkini (5 Terakhir)</h3>

                    @if($pembayaranTerkini->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">Tiada pembayaran dicatat</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">No. Resit</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">Pembayar</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">Jenis Zakat</th>
                                        <th class="px-4 py-2 text-right text-sm font-semibold">Jumlah (RM)</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">Tarikh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pembayaranTerkini as $pembayaran)
                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                            <td class="px-4 py-3 font-mono text-sm">{{ $pembayaran->no_resit }}</td>
                                            <td class="px-4 py-3">{{ $pembayaran->pembayar->nama }}</td>
                                            <td class="px-4 py-3">{{ $pembayaran->jenisZakat->nama }}</td>
                                            <td class="px-4 py-3 text-right font-semibold">
                                                RM {{ number_format($pembayaran->jumlah, 2) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ \Carbon\Carbon::parse($pembayaran->tarikh_bayar)->format('d/m/Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('pembayaran.index') }}" class="text-blue-600 dark:text-blue-400 text-sm">
                                Lihat Semua Pembayaran →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## Lab 4.1: Pasang Laravel Breeze

### Langkah Demi Langkah

#### Langkah 1: Buka Terminal

```bash
cd /path/ke/projek/anda
```

#### Langkah 2: Pasang Breeze via Composer

```bash
composer require laravel/breeze --dev
```

Tunggu sehingga selesai. Anda sepatutnya melihat:

```
Using version ^1.0 for laravel/breeze
...
[OK] Package installed successfully.
```

#### Langkah 3: Jalankan Arisan Breeze

```bash
php artisan breeze:install blade
```

Anda akan ditanya pilihan:
- **Dark mode support?** - Jawab `yes` atau `no` (pilihan anda)
- **ESM with SSR?** - Jawab `no` (tidak diperlukan)

#### Langkah 4: Pasang Dependensi Node (Jika Perlu)

```bash
npm install
npm run build
```

#### Langkah 5: Jalankan Migrasi

```bash
php artisan migrate
```

Ini akan mencipta jadual `users`, `password_reset_tokens`, dan `sessions`.

#### Langkah 6: Mulai Server

```bash
php artisan serve
```

Sekarang akses `http://localhost:8000/register` untuk mendaftar pengguna pertama.

#### Langkah 7: Daftar Pengguna Admin

1. Pergi ke `http://localhost:8000/register`
2. Masukkan nama, email, dan kata laluan
3. Klik Register
4. Anda sekarang boleh login

---

## Lab 4.2: Modul Pembayaran

### Membuat PembayaranController

```bash
php artisan make:controller PembayaranController --resource
```

Isi `app/Http/Controllers/PembayaranController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pembayar;
use App\Models\JenisZakat;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PembayaranController extends Controller
{
    /**
     * Senarai pembayaran
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $query = Pembayaran::with('pembayar', 'jenisZakat');

        if ($search) {
            $query->whereHas('pembayar', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            })->orWhere('no_resit', 'like', '%' . $search . '%');
        }

        $pembayarans = $query->latest()->paginate(15);

        return view('pembayaran.index', [
            'pembayarans' => $pembayarans,
            'search' => $search
        ]);
    }

    /**
     * Bentuk tambah pembayaran baru
     */
    public function create(): View
    {
        $pembayars = Pembayar::orderBy('nama')->get();
        $jenisZakats = JenisZakat::all();

        return view('pembayaran.create', [
            'pembayars' => $pembayars,
            'jenisZakats' => $jenisZakats
        ]);
    }

    /**
     * Simpan pembayaran baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pembayar_id' => 'required|exists:pembayar,id',
            'jenis_zakat_id' => 'required|exists:jenis_zakat,id',
            'jumlah' => 'required|numeric|min:0.01',
            'cara_bayar' => 'required|in:tunai,cek,transfer',
            'tarikh_bayar' => 'required|date'
        ]);

        // Auto-generate no_resit
        $tahun = date('Y');
        $bulan = date('m');
        $bilangan = Pembayaran::whereYear('created_at', $tahun)
                              ->whereMonth('created_at', $bulan)
                              ->count() + 1;
        $validated['no_resit'] = 'RST-' . $tahun . $bulan . '-' . str_pad($bilangan, 4, '0', STR_PAD_LEFT);

        Pembayaran::create($validated);

        return redirect()->route('pembayaran.index')
                       ->with('success', 'Pembayaran berjaya ditambah');
    }

    /**
     * Paparan detail pembayaran
     */
    public function show(Pembayaran $pembayaran): View
    {
        $pembayaran->load('pembayar', 'jenisZakat');

        return view('pembayaran.show', ['pembayaran' => $pembayaran]);
    }

    /**
     * Bentuk kemaskini pembayaran
     */
    public function edit(Pembayaran $pembayaran): View
    {
        $pembayars = Pembayar::orderBy('nama')->get();
        $jenisZakats = JenisZakat::all();

        return view('pembayaran.edit', [
            'pembayaran' => $pembayaran,
            'pembayars' => $pembayars,
            'jenisZakats' => $jenisZakats
        ]);
    }

    /**
     * Kemaskini pembayaran
     */
    public function update(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $validated = $request->validate([
            'pembayar_id' => 'required|exists:pembayar,id',
            'jenis_zakat_id' => 'required|exists:jenis_zakat,id',
            'jumlah' => 'required|numeric|min:0.01',
            'cara_bayar' => 'required|in:tunai,cek,transfer',
            'tarikh_bayar' => 'required|date'
        ]);

        $pembayaran->update($validated);

        return redirect()->route('pembayaran.show', $pembayaran)
                       ->with('success', 'Pembayaran berjaya dikemaskini');
    }

    /**
     * Padam pembayaran
     */
    public function destroy(Pembayaran $pembayaran): RedirectResponse
    {
        $pembayaran->delete();

        return redirect()->route('pembayaran.index')
                       ->with('success', 'Pembayaran berjaya dipadam');
    }
}
```

### Bentuk Pembayaran (Create)

Buat `resources/views/pembayaran/create.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('pembayaran.store') }}">
                        @csrf

                        <!-- Pembayar -->
                        <div class="mb-6">
                            <label for="pembayar_id" class="block text-sm font-medium mb-2">
                                Pembayar <span class="text-red-500">*</span>
                            </label>
                            <select id="pembayar_id" name="pembayar_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded" required>
                                <option value="">-- Pilih Pembayar --</option>
                                @foreach($pembayars as $pembayar)
                                    <option value="{{ $pembayar->id }}">
                                        {{ $pembayar->nama }} ({{ $pembayar->no_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('pembayar_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenis Zakat -->
                        <div class="mb-6">
                            <label for="jenis_zakat_id" class="block text-sm font-medium mb-2">
                                Jenis Zakat <span class="text-red-500">*</span>
                            </label>
                            <select id="jenis_zakat_id" name="jenis_zakat_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded" required>
                                <option value="">-- Pilih Jenis Zakat --</option>
                                @foreach($jenisZakats as $jenis)
                                    <option value="{{ $jenis->id }}">{{ $jenis->nama }}</option>
                                @endforeach
                            </select>
                            @error('jenis_zakat_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jumlah -->
                        <div class="mb-6">
                            <label for="jumlah" class="block text-sm font-medium mb-2">
                                Jumlah (RM) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="jumlah" name="jumlah" step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded"
                                   required value="{{ old('jumlah') }}">
                            @error('jumlah')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cara Bayar -->
                        <div class="mb-6">
                            <label for="cara_bayar" class="block text-sm font-medium mb-2">
                                Cara Bayar <span class="text-red-500">*</span>
                            </label>
                            <select id="cara_bayar" name="cara_bayar" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded" required>
                                <option value="">-- Pilih Cara Bayar --</option>
                                <option value="tunai">Tunai</option>
                                <option value="cek">Cek</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                            @error('cara_bayar')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tarikh Bayar -->
                        <div class="mb-6">
                            <label for="tarikh_bayar" class="block text-sm font-medium mb-2">
                                Tarikh Bayar <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tarikh_bayar" name="tarikh_bayar"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded"
                                   required value="{{ old('tarikh_bayar', date('Y-m-d')) }}">
                            @error('tarikh_bayar')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Butang -->
                        <div class="flex gap-4">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Simpan Pembayaran
                            </button>
                            <a href="{{ route('pembayaran.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### Senarai Pembayaran (Index)

Buat `resources/views/pembayaran/index.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pembayaran Zakat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Butang Tambah & Carian -->
                    <div class="flex justify-between items-center mb-6">
                        <a href="{{ route('pembayaran.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            + Tambah Pembayaran
                        </a>

                        <form method="GET" action="{{ route('pembayaran.index') }}" class="flex gap-2">
                            <input type="text" name="search" placeholder="Cari nama pembayar atau no. resit..."
                                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded"
                                   value="{{ $search }}">
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded">Cari</button>
                        </form>
                    </div>

                    <!-- Jadual Pembayaran -->
                    @if($pembayarans->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">Tiada pembayaran dicatat</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">No. Resit</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">Pembayar</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">Jenis Zakat</th>
                                        <th class="px-4 py-2 text-right text-sm font-semibold">Jumlah (RM)</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">Cara Bayar</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pembayarans as $pembayaran)
                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                            <td class="px-4 py-3 font-mono text-sm">{{ $pembayaran->no_resit }}</td>
                                            <td class="px-4 py-3">{{ $pembayaran->pembayar->nama }}</td>
                                            <td class="px-4 py-3">{{ $pembayaran->jenisZakat->nama }}</td>
                                            <td class="px-4 py-3 text-right font-semibold">
                                                RM {{ number_format($pembayaran->jumlah, 2) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded capitalize">
                                                    {{ $pembayaran->cara_bayar }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 flex gap-2">
                                                <a href="{{ route('pembayaran.show', $pembayaran) }}" class="text-blue-600 hover:underline">Lihat</a>
                                                <a href="{{ route('pembayaran.edit', $pembayaran) }}" class="text-green-600 hover:underline">Edit</a>
                                                <form method="POST" action="{{ route('pembayaran.destroy', $pembayaran) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Yakin?')">
                                                        Padam
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $pembayarans->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## Lab 4.3: Bina Dashboard

### Mendaftarkan Rute Dashboard

Dalam `routes/web.php`, pastikan rute dashboard sudah ada:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // ... rute lain
});
```

### Mengakses Dashboard

1. Login ke sistem: `http://localhost:8000/login`
2. Akses dashboard: `http://localhost:8000/dashboard`
3. Lihat statistik zakat yang dikemas kini secara automatik

---

## Lab 4.4: API & Ujian

### Mendaftarkan Rute API

Dalam `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\PembayarApiController;
use Illuminate\Support\Facades\Route;

Route::apiResource('pembayar', PembayarApiController::class);
Route::get('pembayar/{id}/pembayarans', [PembayarApiController::class, 'getPembayarans']);
```

### Menjalankan Server

```bash
php artisan serve
```

### Menguji API

Gunakan Thunder Client atau curl seperti yang ditunjukkan dalam **Modul 4.3**.

---

## Modul 4.5: Penyebaran (Deployment)

### Persiapan Penyebaran

Sebelum menghantar ke production, lakukan:

#### 1. Optimisasi Kod

```bash
# Compile config dan route
php artisan config:cache
php artisan route:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

#### 2. Tetapkan Environment ke Production

Dalam `.env`:

```env
APP_ENV=production
APP_DEBUG=false
```

#### 3. Generate Application Key (Jika Belum)

```bash
php artisan key:generate
```

#### 4. Jalankan Migrasi di Server

```bash
php artisan migrate --force
php artisan db:seed (opsional)
```

### Penyebaran ke Shared Hosting

#### Langkah 1: Punya Akses SSH

Hubungi penyedia hosting untuk mendapat akses SSH.

#### Langkah 2: Upload Projek

Gunakan FTP atau Git:

```bash
# Via Git (lebih mudah)
git clone https://github.com/anda/projek.git
cd projek
composer install --optimize-autoloader --no-dev
```

#### Langkah 3: Konfigurasi Database

1. Buat pangkalan data baru di cPanel
2. Salin `.env.example` ke `.env`
3. Atur database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_db
DB_USERNAME=pengguna_db
DB_PASSWORD=kata_laluan_db
```

#### Langkah 4: Jalankan Migrasi

```bash
php artisan migrate --force
```

#### Langkah 5: Tetapkan Ownership & Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data .
```

#### Langkah 6: Pointing Domain

Dalam cPanel, arahkan domain ke folder `public/`.

### Penyebaran dengan Laravel Forge

**Laravel Forge** ialah platform yang memudahkan penyebaran Laravel.

#### Langkah 1: Daftar Akaun Forge

Pergi ke [forge.laravel.com](https://forge.laravel.com)

#### Langkah 2: Tambah Server

Pilih penyedia cloud (DigitalOcean, AWS, Linode, dll.)

#### Langkah 3: Buat Site

1. Klik "New Site"
2. Pilih "New Laravel Project"
3. Sambungkan repository GitHub anda

#### Langkah 4: Deploy

Forge secara otomatis akan:
- Menjalankan `composer install`
- Menjalankan `php artisan migrate`
- Mengkonfigurasi SSL (Let's Encrypt)

---

## Projek Akhir Lengkap: Sistem Pengurusan Zakat

### Ringkasan Ciri-ciri

Selepas menyelesaikan 4 hari, anda mempunyai sistem yang lengkap:

| Ciri | Status | Modul |
|------|--------|-------|
| **Pengesahan (Login/Register)** | Siap | 4.1 |
| **CRUD Pembayar** | Siap | 1-3 |
| **CRUD Pembayaran** | Siap | 4.2 |
| **Jenis Zakat Management** | Siap | 1-3 |
| **Dashboard & Laporan** | Siap | 4.4 |
| **API RESTful** | Siap | 4.3 |
| **Hubungan Model** | Siap | 4.2 |
| **Deployment Ready** | Siap | 4.5 |

### Struktur Projek Akhir

```
sistem-zakat/
├── app/
│   ├── Models/
│   │   ├── Pembayar.php
│   │   ├── Pembayaran.php
│   │   └── JenisZakat.php
│   └── Http/
│       └── Controllers/
│           ├── PembayarController.php
│           ├── PembayaranController.php
│           ├── DashboardController.php
│           └── Api/
│               └── PembayarApiController.php
├── database/
│   ├── migrations/
│   │   ├── create_pembayar_table.php
│   │   ├── create_jenis_zakat_table.php
│   │   └── create_pembayaran_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   └── views/
│       ├── dashboard.blade.php
│       ├── pembayar/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       └── pembayaran/
│           ├── index.blade.php
│           ├── create.blade.php
│           ├── edit.blade.php
│           └── show.blade.php
├── routes/
│   ├── web.php
│   └── api.php
└── .env
```

### Fitur Utama Sistem

1. **Pengesahan Pengguna** - Hanya staf berdaftar boleh login
2. **Pengurusan Pembayar** - Tambah, ubah, lihat, padam pembayar zakat
3. **Pengurusan Pembayaran** - Rekod pembayaran zakat dengan auto-generate resit
4. **Dashboard** - Paparan statistik zakat (jumlah pembayar, kutipan, jenis zakat)
5. **API** - Akses data via REST API untuk integrasi mobile/pihak ketiga
6. **Laporan** - Lihat pembayaran terkini dan statistik per jenis zakat

---

## Ringkasan & Langkah Seterusnya

### Apa yang Telah Anda Pelajari

| Konsep | Deskripsi |
|--------|-----------|
| **Authentication** | Sistem login/logout dengan Laravel Breeze |
| **Eloquent Relationships** | Hubungan HasMany, BelongsTo antara model |
| **RESTful API** | Membuat API JSON untuk akses data |
| **Dashboard** | Paparan statistik dan laporan |
| **Deployment** | Menghantar projek ke production |

### Langkah Seterusnya untuk Pembelajaran Lebih Lanjut

#### 1. Fitur Lanjutan

- **Testing** - Belajar PHPUnit untuk unit testing dan feature testing
- **Queue** - Proses pembayaran secara asinkron
- **WebSocket** - Notifikasi real-time pembayaran baru

#### 2. Keamanan

- **Authorization** - Kawal akses berdasarkan peranan (admin, staff, viewer)
- **Encryption** - Enkripsi data sensitif (no. ID, no. telefon)
- **Two-Factor Authentication** - Keselamatan login berlapis

#### 3. Prestasi

- **Caching** - Cache data statistik dashboard
- **Database Indexing** - Indexkan lajur sering dicari
- **Pagination** - Bahagi data besar kepada halaman

#### 4. Integrasi

- **Email** - Hantar notifikasi pembayaran via email
- **SMS** - Notifikasi SMS pembayaran
- **Payment Gateway** - Integrasi FPX, Stripe, PayPal

#### 5. Sumber Pembelajaran

- **Dokumentasi Laravel** - https://laravel.com/docs
- **Laracasts** - https://laracasts.com (video tutorial)
- **Laravel News** - https://laravel-news.com
- **Stack Overflow** - Tanya soalan komunitas

### Tips Untuk Masa Depan

1. **Version Control** - Guna Git untuk menguruskan kod
2. **Documentation** - Tuliskan dokumentasi kod anda
3. **Testing** - Sentiasa tulis test sebelum kod production
4. **Code Review** - Minta review daripada rakan programmer
5. **Performance** - Monitor prestasi menggunakan tools seperti New Relic

---

## Kesimpulan

Tahniah! Anda telah menyelesaikan Kursus Laravel 4 Hari dan membina **Sistem Pengurusan Zakat** untuk **Pusat Zakat Negeri Kedah**.

Sistem ini siap untuk digunakan oleh staf zakat untuk menguruskan pembayar, pembayaran, dan menjana laporan statistik. Dengan API yang tersedia, sistem juga boleh diperluas dengan aplikasi mobile atau integrasi lain.

**Langkah seterusnya:**
1. Tambahkan lebih banyak pengguna/staf
2. Konfigurasi email untuk notifikasi
3. Pasang ke server production
4. Latih pengguna sistem
5. Kembangkan fitur tambahan berdasarkan feedback

Semoga projek ini membawa manfaat kepada Pusat Zakat Negeri Kedah dalam menguruskan zakat dengan lebih efisien!

---

**Dikemaskini:** 6 April 2026
**Versi:** 1.0
**Bahasa:** Bahasa Melayu
