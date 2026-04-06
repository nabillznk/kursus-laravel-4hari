# Hari 2 — Penghalaan, Pengawal & Middleware

Projek contoh **Sistem Pengurusan Zakat** untuk Hari 2 kursus Laravel.
Hari ini kita belajar tentang penghalaan (routes), pengawal (controllers), dan middleware.

## Keperluan

- PHP 8.2+
- Composer
- MySQL / MariaDB
- Laragon (disyorkan) atau persekitaran pembangunan setara

## Langkah Persediaan

### 1. Cipta pangkalan data

```sql
CREATE DATABASE zakat_kedah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Pasang kebergantungan

```bash
composer install
```

### 3. Salin fail .env (jika perlu)

```bash
cp .env.example .env
php artisan key:generate
```

Kemudian kemaskini `.env`:

```
DB_CONNECTION=mysql
DB_DATABASE=zakat_kedah
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Jalankan migrasi dan seeder

```bash
php artisan migrate
php artisan db:seed
```

### 5. Mulakan pelayan

```bash
php artisan serve
```

Atau gunakan Laragon dengan domain `sistem-zakat.test`.

## Apa yang Dipelajari

### Penghalaan (Routes)
- `Route::get()`, `Route::post()`, `Route::put()`, `Route::delete()`
- `Route::resource()` — cipta 7 laluan CRUD secara automatik
- `Route::group()` — kumpulan laluan dengan middleware dan prefix
- `Route::prefix()` dan `Route::name()` — organisasi laluan

### Pengawal (Controllers)
- **PembayarController** — pengawal sumber penuh (resource controller) dengan 7 kaedah:
  `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- **SemakController** — semak status sistem (versi PHP, Laravel, pangkalan data)
- **MaklumatController** — papar semua laluan berdaftar dalam sistem

### Middleware
- **LogAkses** — log setiap permintaan (kaedah, URL, IP, masa) ke `storage/logs/akses.log`
- **SemakWaktuPejabat** — hadkan akses kepada waktu pejabat (Isnin-Jumaat, 8am-5pm)
- Pendaftaran middleware alias dalam `bootstrap/app.php` (Laravel 11+)

### Contoh Penggunaan Middleware

```php
// Dalam routes/web.php

// Middleware pada kumpulan laluan
Route::middleware(['log.akses'])->group(function () {
    Route::resource('pembayar', PembayarController::class);
});

// Middleware pada satu laluan
Route::get('/rahsia', fn() => 'Rahsia!')
    ->middleware('waktu.pejabat');
```

## Struktur Laluan

| Kaedah | URI | Nama | Keterangan |
|--------|-----|------|------------|
| GET | `/` | - | Alih ke senarai pembayar |
| GET | `/pembayar` | pembayar.index | Senarai pembayar |
| GET | `/pembayar/create` | pembayar.create | Borang daftar baru |
| POST | `/pembayar` | pembayar.store | Simpan pembayar baru |
| GET | `/pembayar/{id}` | pembayar.show | Lihat maklumat pembayar |
| GET | `/pembayar/{id}/edit` | pembayar.edit | Borang kemaskini |
| PUT | `/pembayar/{id}` | pembayar.update | Kemaskini pembayar |
| DELETE | `/pembayar/{id}` | pembayar.destroy | Padam pembayar |
| GET | `/semak` | semak | Semakan sistem |
| GET | `/maklumat/laluan` | maklumat.laluan | Senarai laluan |
| GET | `/admin/dashboard` | admin.dashboard | Dashboard pentadbir |

## Struktur Fail

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── MaklumatController.php
│   │   ├── PembayarController.php
│   │   └── SemakController.php
│   └── Middleware/
│       ├── LogAkses.php
│       └── SemakWaktuPejabat.php
├── Models/
│   └── Pembayar.php
database/
├── migrations/
│   └── 2024_01_01_000001_create_pembayars_table.php
├── seeders/
│   ├── DatabaseSeeder.php
│   └── PembayarSeeder.php
resources/views/
├── layouts/app.blade.php
├── pembayar/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
│   └── edit.blade.php
├── maklumat/laluan.blade.php
├── semak.blade.php
├── admin/dashboard.blade.php
└── errors/
    ├── 403.blade.php
    ├── 404.blade.php
    └── luar-waktu.blade.php
```
