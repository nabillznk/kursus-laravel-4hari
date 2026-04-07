# Hari 2 -- Penghalaan, Pengawal, Middleware & Hubungan Model

> **Sistem Pengurusan Zakat Kedah** -- Kursus Laravel 4 Hari
> Tempoh: 6 jam | Bahasa: Melayu | Tahap: Permulaan

---

## Pengenalan

Pada Hari 1, kita telah mencipta projek `sistem-zakat`, menyediakan pangkalan data `zakat_kedah`, dan membina persekitaran pembangunan menggunakan Laragon (atau pelayan pembangunan PHP).

Hari ini kita akan membina **keseluruhan sistem CRUD Pembayar** beserta model hubungan (relationships), middleware, dan ciri-ciri tambahan:

| # | Topik | Perkara yang Dipelajari |
|---|-------|------------------------|
| 1 | **Model & Migrasi** | Cipta model `Pembayar`, `JenisZakat`, `Pembayaran` dengan migrasi, skop, dan aksesor |
| 2 | **Hubungan Model** | `hasMany`, `belongsTo`, eager loading, `withCount`, `withSum`, `doesntHave`, `whereHas` |
| 3 | **Pengawal** | Pengawal sumber (resource controller) dengan 7 kaedah CRUD dan pengesahan (validation) |
| 4 | **Seeder** | Data contoh untuk pengujian — 10 pembayar, 5 jenis zakat, 20 pembayaran |
| 5 | **Middleware** | Cipta middleware tersuai untuk log akses dan semak waktu pejabat |
| 6 | **Laluan** | Kumpulan laluan, prefix, middleware alias, laluan sumber |
| 7 | **Paparan Blade** | Susun atur (layout), paparan CRUD lengkap, halaman semakan, halaman ralat |
| 8 | **Pengawal Tambahan** | Pengawal untuk semakan sistem dan senarai laluan |

Pastikan projek Laravel `sistem-zakat` daripada Hari 1 sudah wujud dan berfungsi sebelum meneruskan.

---

## Persediaan

### Pangkalan Data

Pastikan pangkalan data `zakat_kedah` telah dicipta dalam MySQL. Jika menggunakan Laragon, buka HeidiSQL atau terminal MySQL:

```sql
CREATE DATABASE IF NOT EXISTS zakat_kedah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Konfigurasi .env

Buka fail `.env` di dalam projek `sistem-zakat` dan pastikan tetapan pangkalan data seperti berikut:

```env
APP_NAME="Sistem Zakat Kedah"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://sistem-zakat.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zakat_kedah
DB_USERNAME=root
DB_PASSWORD=
```

### Pasang Kebergantungan & Jana Kunci

Jika belum dilakukan pada Hari 1:

```bash
composer install
php artisan key:generate
```

---

## Bahagian A: Model, Migrasi & Seeder

### Langkah 1: Cipta Model & Migrasi Pembayar

Model `Pembayar` mewakili pembayar zakat dalam sistem. Kita akan mencipta model berserta fail migrasi untuk jadual pangkalan data.

#### Arahan Artisan

```bash
php artisan make:model Pembayar -m
```

Bendera `-m` akan mencipta fail migrasi secara automatik bersama model.

#### 1a. Fail Migrasi

Buka fail migrasi yang dijana di `database/migrations/` dan gantikan kandungannya dengan kod berikut.

**Fail:** `database/migrations/2024_01_01_000001_create_pembayars_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cipta jadual pembayars.
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
     * Padam jadual pembayars.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayars');
    }
};
```

#### Penerangan Medan

| Medan | Jenis | Keterangan |
|-------|-------|-----------|
| `id` | `bigint` auto-increment | Kunci utama |
| `nama` | `varchar(255)` | Nama penuh pembayar |
| `no_ic` | `varchar(12)` unique | No. Kad Pengenalan (12 digit tanpa sengkang) |
| `alamat` | `text` | Alamat penuh |
| `no_tel` | `varchar(15)` | Nombor telefon |
| `email` | `varchar(255)` nullable | Alamat e-mel (pilihan) |
| `pekerjaan` | `varchar(255)` nullable | Pekerjaan (pilihan) |
| `pendapatan_bulanan` | `decimal(10,2)` nullable | Pendapatan bulanan dalam RM (pilihan) |
| `timestamps` | `datetime` | `created_at` dan `updated_at` automatik |

**Konsep penting:**

- `$table->id()` — Cipta lajur `id` sebagai kunci utama auto-increment.
- `->unique()` — Memastikan tiada dua pembayar dengan No. IC yang sama.
- `->nullable()` — Membenarkan lajur kosong (NULL).
- `$table->timestamps()` — Cipta dua lajur `created_at` dan `updated_at` yang diurus oleh Laravel secara automatik.

#### 1b. Fail Model

**Fail:** `app/Models/Pembayar.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // ──────────────────────────────────────────────
    // Hubungan (Relationships)
    // ──────────────────────────────────────────────

    /**
     * Seorang pembayar mempunyai banyak pembayaran.
     * (One-to-Many: Pembayar hasMany Pembayaran)
     */
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
}
```

#### Penerangan Konsep Model

| Konsep | Contoh Penggunaan | Penerangan |
|--------|-------------------|-----------|
| `$fillable` | `Pembayar::create($data)` | Senarai medan yang boleh diisi melalui mass-assignment. Ini melindungi daripada serangan mass-assignment. |
| `$casts` | `$pembayar->pendapatan_bulanan` | Laravel secara automatik tukar jenis data. `decimal:2` memastikan 2 tempat perpuluhan. |
| `scopeCarian()` | `Pembayar::carian('Ahmad')->get()` | Skop tempatan (local scope) — kaedah query yang boleh diguna semula. Panggil tanpa prefix `scope`. |
| `getIcFormatAttribute()` | `$pembayar->ic_format` | Aksesor — cipta atribut maya yang memformat data. Panggil sebagai property (`ic_format`), bukan method. |
| `getPendapatanFormatAttribute()` | `$pembayar->pendapatan_format` | Aksesor — format pendapatan dengan simbol RM dan koma. |
| `pembayarans()` | `$pembayar->pembayarans` | **Hubungan hasMany** — seorang pembayar boleh ada banyak pembayaran. Topik utama Hari 2! |
| `getJumlahBayaranAttribute()` | `$pembayar->jumlah_bayaran` | Aksesor yang menggunakan hubungan untuk mengira jumlah bayaran sah. |

> **Nota:** Perhatikan perbezaan antara `$pembayar->pembayarans` (property — pulangkan Collection) dan `$pembayar->pembayarans()` (method — pulangkan query builder). Guna method `()` apabila mahu tambah syarat seperti `where`, `sum`, dll.

---

### Langkah 2: Cipta Model & Migrasi JenisZakat

Model `JenisZakat` mewakili jenis-jenis zakat yang boleh dibayar (contoh: Zakat Fitrah, Zakat Pendapatan, dll.).

#### Arahan Artisan

```bash
php artisan make:model JenisZakat -m
```

#### 2a. Fail Migrasi

**Fail:** `database/migrations/2024_01_01_000002_create_jenis_zakats_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cipta jadual jenis_zakats.
     */
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

    /**
     * Padam jadual jenis_zakats.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_zakats');
    }
};
```

#### Penerangan Medan

| Medan | Jenis | Keterangan |
|-------|-------|-----------|
| `id` | `bigint` auto-increment | Kunci utama |
| `nama` | `varchar(255)` | Nama jenis zakat (cth: Zakat Fitrah, Zakat Pendapatan) |
| `kadar` | `decimal(8,4)` | Kadar peratusan atau nilai tetap (cth: 2.5000 = 2.5%, atau 7.0000 = RM 7) |
| `penerangan` | `text` nullable | Penerangan ringkas tentang jenis zakat |
| `is_aktif` | `boolean` default `true` | Status aktif/tidak aktif |
| `timestamps` | `datetime` | `created_at` dan `updated_at` |

#### 2b. Fail Model

**Fail:** `app/Models/JenisZakat.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisZakat extends Model
{
    use HasFactory;

    /**
     * Medan yang boleh diisi secara mass-assignment.
     */
    protected $fillable = [
        'nama',
        'kadar',
        'penerangan',
        'is_aktif',
    ];

    /**
     * Penukaran jenis data automatik.
     */
    protected $casts = [
        'kadar'    => 'decimal:4',
        'is_aktif' => 'boolean',
    ];

    // ──────────────────────────────────────────────
    // Hubungan (Relationships)
    // ──────────────────────────────────────────────

    /**
     * Satu jenis zakat mempunyai banyak pembayaran.
     * (One-to-Many: JenisZakat hasMany Pembayaran)
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    // ──────────────────────────────────────────────
    // Skop (Scopes)
    // ──────────────────────────────────────────────

    /**
     * Skop: Hanya jenis zakat yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }
}
```

**Penerangan:**

- **`hasMany(Pembayaran::class)`** — Satu jenis zakat boleh mempunyai banyak rekod pembayaran. Laravel secara automatik mencari lajur `jenis_zakat_id` pada jadual `pembayarans`.
- **`scopeAktif`** — Hanya pulangkan jenis zakat yang aktif. Penggunaan: `JenisZakat::aktif()->get()`.
- **`'is_aktif' => 'boolean'`** — Cast ke `true`/`false` supaya boleh guna `@if ($jenisZakat->is_aktif)` dalam Blade.

---

### Langkah 3: Cipta Model & Migrasi Pembayaran

Model `Pembayaran` mewakili transaksi pembayaran zakat. Ia mempunyai **dua hubungan belongsTo** — ke `Pembayar` dan `JenisZakat`.

#### Arahan Artisan

```bash
php artisan make:model Pembayaran -m
```

#### 3a. Fail Migrasi

**Fail:** `database/migrations/2024_01_01_000003_create_pembayarans_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cipta jadual pembayarans.
     */
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

    /**
     * Padam jadual pembayarans.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
```

#### Penerangan Medan

| Medan | Jenis | Keterangan |
|-------|-------|-----------|
| `id` | `bigint` auto-increment | Kunci utama |
| `pembayar_id` | `foreignId` | Kunci asing ke jadual `pembayars` — siapa yang bayar |
| `jenis_zakat_id` | `foreignId` | Kunci asing ke jadual `jenis_zakats` — jenis zakat apa |
| `jumlah` | `decimal(12,2)` | Jumlah bayaran dalam RM |
| `tarikh_bayar` | `date` | Tarikh pembayaran dibuat |
| `cara_bayar` | `enum` | Cara pembayaran: `tunai`, `kad`, `fpx`, `online` |
| `no_resit` | `varchar` unique | Nombor resit unik (cth: ZK-2024-0001) |
| `status` | `enum` default `pending` | Status pembayaran: `pending`, `sah`, `batal` |
| `timestamps` | `datetime` | `created_at` dan `updated_at` |

**Konsep penting dalam migrasi ini:**

| Konsep | Kod | Penerangan |
|--------|-----|-----------|
| Kunci asing | `$table->foreignId('pembayar_id')->constrained()` | Cipta lajur `pembayar_id` yang merujuk kepada `pembayars.id`. Laravel meneka nama jadual daripada nama lajur secara automatik. |
| Cascade delete | `->cascadeOnDelete()` | Apabila pembayar dipadam, semua pembayaran beliau turut dipadam secara automatik. |
| Enum | `$table->enum('cara_bayar', ['tunai', 'kad', 'fpx', 'online'])` | Hadkan nilai lajur kepada senarai yang ditetapkan sahaja. Jika cuba masukkan nilai lain, MySQL akan menolak. |
| Default | `->default('pending')` | Nilai lalai jika tidak dinyatakan semasa simpan. |

#### 3b. Fail Model

**Fail:** `app/Models/Pembayaran.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    /**
     * Medan yang boleh diisi secara mass-assignment.
     */
    protected $fillable = [
        'pembayar_id',
        'jenis_zakat_id',
        'jumlah',
        'tarikh_bayar',
        'cara_bayar',
        'no_resit',
        'status',
    ];

    /**
     * Penukaran jenis data automatik.
     */
    protected $casts = [
        'jumlah'       => 'decimal:2',
        'tarikh_bayar' => 'date',
    ];

    // ──────────────────────────────────────────────
    // Hubungan (Relationships)
    // ──────────────────────────────────────────────

    /**
     * Pembayaran ini milik seorang pembayar.
     * (Many-to-One: Pembayaran belongsTo Pembayar)
     */
    public function pembayar(): BelongsTo
    {
        return $this->belongsTo(Pembayar::class);
    }

    /**
     * Pembayaran ini milik satu jenis zakat.
     * (Many-to-One: Pembayaran belongsTo JenisZakat)
     */
    public function jenisZakat(): BelongsTo
    {
        return $this->belongsTo(JenisZakat::class);
    }

    // ──────────────────────────────────────────────
    // Aksesor (Accessors)
    // ──────────────────────────────────────────────

    /**
     * Aksesor: Format jumlah dengan RM (RM 150.00).
     */
    public function getJumlahFormatAttribute(): string
    {
        return 'RM ' . number_format($this->jumlah, 2);
    }

    // ──────────────────────────────────────────────
    // Skop (Scopes)
    // ──────────────────────────────────────────────

    /**
     * Skop: Hanya pembayaran yang sah.
     */
    public function scopeSah($query)
    {
        return $query->where('status', 'sah');
    }
}
```

**Penerangan:**

| Konsep | Kod | Penerangan |
|--------|-----|-----------|
| belongsTo Pembayar | `$this->belongsTo(Pembayar::class)` | Setiap pembayaran milik seorang pembayar. Laravel mencari lajur `pembayar_id` secara automatik. |
| belongsTo JenisZakat | `$this->belongsTo(JenisZakat::class)` | Setiap pembayaran milik satu jenis zakat. Nama method `jenisZakat()` ditukar kepada `jenis_zakat_id` secara automatik (camelCase ke snake_case). |
| Cast `date` | `'tarikh_bayar' => 'date'` | Laravel tukar kepada objek Carbon, jadi boleh guna `$bayaran->tarikh_bayar->format('d/m/Y')`. |
| scopeSah | `Pembayaran::sah()->get()` | Skop yang hanya pulangkan pembayaran berstatus `sah`. |

---

### Langkah 4: Memahami Hubungan Model (Relationships)

Ini adalah **topik paling penting** dalam Hari 2. Hubungan model (Eloquent Relationships) membolehkan kita mengakses data berkaitan antara jadual tanpa menulis SQL JOIN secara manual. Laravel menguruskan semuanya dengan kaedah yang mudah dan elegan.

#### 4a. Diagram Hubungan

```
┌─────────────────┐              ┌──────────────────┐              ┌─────────────────┐
│    Pembayar     │              │   Pembayaran     │              │   JenisZakat    │
├─────────────────┤              ├──────────────────┤              ├─────────────────┤
│ id          PK  │───┐         │ id           PK  │         ┌───│ id          PK  │
│ nama            │   │         │ pembayar_id  FK  │◄────────┘   │ nama            │
│ no_ic           │   └────────►│ jenis_zakat_id FK│             │ kadar           │
│ alamat          │              │ jumlah           │              │ penerangan      │
│ no_tel          │              │ tarikh_bayar     │              │ is_aktif        │
│ email           │              │ cara_bayar       │              └─────────────────┘
│ pekerjaan       │              │ no_resit         │
│ pendapatan      │              │ status           │
└─────────────────┘              └──────────────────┘

Pembayar  (1) ──── hasMany ────► (N) Pembayaran
JenisZakat(1) ──── hasMany ────► (N) Pembayaran

Pembayaran ──── belongsTo ────► Pembayar
Pembayaran ──── belongsTo ────► JenisZakat
```

**Ringkasan hubungan dalam kod:**

| Model | Method | Jenis | Sasaran | Penerangan |
|-------|--------|-------|---------|-----------|
| `Pembayar` | `pembayarans()` | `hasMany` | `Pembayaran` | Seorang pembayar boleh ada banyak pembayaran |
| `JenisZakat` | `pembayarans()` | `hasMany` | `Pembayaran` | Satu jenis zakat boleh ada banyak pembayaran |
| `Pembayaran` | `pembayar()` | `belongsTo` | `Pembayar` | Setiap pembayaran milik seorang pembayar |
| `Pembayaran` | `jenisZakat()` | `belongsTo` | `JenisZakat` | Setiap pembayaran milik satu jenis zakat |

**Bagaimana Laravel menentukan lajur kunci asing?**

Laravel menggunakan konvensyen penamaan:
- `hasMany(Pembayaran::class)` pada model `Pembayar` — Laravel cari lajur `pembayar_id` pada jadual `pembayarans`.
- `belongsTo(Pembayar::class)` pada model `Pembayaran` — Laravel cari lajur `pembayar_id` pada jadual `pembayarans` (jadual model semasa).
- `belongsTo(JenisZakat::class)` — Method name `jenisZakat()` (camelCase) ditukar kepada `jenis_zakat_id` (snake_case + `_id`).

---

#### 4b. Contoh Penggunaan dalam Tinker

Buka terminal dan jalankan:

```bash
php artisan tinker
```

##### Akses hubungan hasMany — dapatkan semua pembayaran seorang pembayar

```php
// Cari pembayar pertama
$pembayar = \App\Models\Pembayar::find(1);
echo $pembayar->nama;
// => "Ahmad bin Abdullah"

// Dapatkan semua pembayaran beliau
$pembayar->pembayarans;
// => Collection of 3 Pembayaran objects

// Berapa banyak pembayaran?
$pembayar->pembayarans->count();
// => 3

// Senarai nombor resit
$pembayar->pembayarans->pluck('no_resit');
// => ["ZK-2024-0001", "ZK-2024-0002", "ZK-2024-0003"]
```

##### Akses hubungan belongsTo — dapatkan maklumat pembayar dari pembayaran

```php
// Cari pembayaran pertama
$pembayaran = \App\Models\Pembayaran::find(1);

// Siapa pembayar?
$pembayaran->pembayar->nama;
// => "Ahmad bin Abdullah"

// Apakah jenis zakat?
$pembayaran->jenisZakat->nama;
// => "Zakat Fitrah"

// Berapa kadar?
$pembayaran->jenisZakat->kadar;
// => "7.0000"
```

##### Kira jumlah bayaran sah seorang pembayar

```php
$pembayar = \App\Models\Pembayar::find(1);

// Guna hubungan sebagai query builder (method call dengan parentheses)
$pembayar->pembayarans()->where('status', 'sah')->sum('jumlah');
// => "1357.00"

// Atau guna aksesor yang telah kita cipta dalam model
$pembayar->jumlah_bayaran;
// => 1357.0
```

> **Nota penting:** Perhatikan perbezaan antara:
> - `$pembayar->pembayarans` (tanpa parentheses) — Akses sebagai property, pulangkan Collection (semua data dimuat).
> - `$pembayar->pembayarans()` (dengan parentheses) — Akses sebagai method, pulangkan query builder (boleh tambah syarat `where`, `sum`, `count` dll.).

##### Dapatkan pembayar yang pernah membuat pembayaran

```php
// has() — ada hubungan
\App\Models\Pembayar::has('pembayarans')->get();
// => Hanya pembayar yang mempunyai sekurang-kurangnya 1 pembayaran

// has() dengan syarat minimum
\App\Models\Pembayar::has('pembayarans', '>=', 3)->get();
// => Pembayar yang mempunyai 3 atau lebih pembayaran
```

##### Dapatkan pembayar yang BELUM pernah membuat pembayaran

```php
// doesntHave() — tiada hubungan
\App\Models\Pembayar::doesntHave('pembayarans')->pluck('nama');
// => ["Nor Azizah binti Ibrahim", "Razak bin Che Mat"]
// (Pembayar 4 dan 7 tiada rekod pembayaran dalam seeder)
```

##### Kira bilangan pembayaran setiap pembayar (withCount)

```php
\App\Models\Pembayar::withCount('pembayarans')
    ->orderByDesc('pembayarans_count')
    ->get()
    ->each(function ($p) {
        echo "$p->nama: $p->pembayarans_count pembayaran\n";
    });

// Output:
// Ahmad bin Abdullah: 3 pembayaran
// Mohd Faizal bin Hassan: 3 pembayaran
// Ismail bin Yusof: 3 pembayaran
// Nurul Huda binti Zakaria: 3 pembayaran
// Aminah binti Sulaiman: 3 pembayaran
// Siti Nurhaliza binti Mohd Razali: 2 pembayaran
// Fatimah binti Omar: 2 pembayaran
// Hassan bin Daud: 1 pembayaran
// Nor Azizah binti Ibrahim: 0 pembayaran
// Razak bin Che Mat: 0 pembayaran
```

##### Eager loading — elak masalah N+1 query

```php
// BURUK — N+1 query (1 query + N query untuk setiap pembayaran)
$pembayarans = \App\Models\Pembayaran::all();
foreach ($pembayarans as $b) {
    echo $b->pembayar->nama;  // Setiap iterasi buat 1 query baru!
}
// Jika 20 pembayaran = 21 query keseluruhan!

// BAIK — Eager loading (hanya 2 query)
$pembayarans = \App\Models\Pembayaran::with(['pembayar', 'jenisZakat'])->get();
foreach ($pembayarans as $b) {
    echo $b->pembayar->nama;  // Tiada query tambahan!
}
// Hanya 3 query: pembayarans + pembayars + jenis_zakats
```

##### Dapatkan jenis zakat yang paling popular

```php
\App\Models\JenisZakat::withCount('pembayarans')
    ->orderByDesc('pembayarans_count')
    ->first();
// => JenisZakat { nama: "Zakat Fitrah", pembayarans_count: 8 }
```

##### Kira jumlah kutipan mengikut jenis zakat

```php
\App\Models\JenisZakat::withSum('pembayarans', 'jumlah')
    ->get()
    ->each(function ($j) {
        echo "$j->nama: RM " . number_format($j->pembayarans_sum_jumlah, 2) . "\n";
    });

// Output:
// Zakat Fitrah: RM 56.00
// Zakat Pendapatan: RM 9,900.00
// Zakat Perniagaan: RM 7,700.00
// Zakat Wang Simpanan: RM 1,300.00
// Zakat Emas: RM 1,200.00
```

##### Carian dengan syarat pada hubungan (whereHas)

```php
// Pembayar yang pernah bayar zakat pendapatan (id = 2)
\App\Models\Pembayar::whereHas('pembayarans', function ($query) {
    $query->where('jenis_zakat_id', 2);
})->pluck('nama');
// => ["Ahmad bin Abdullah", "Siti Nurhaliza binti Mohd Razali", ...]

// Pembayar yang ada pembayaran berstatus 'pending'
\App\Models\Pembayar::whereHas('pembayarans', function ($query) {
    $query->where('status', 'pending');
})->pluck('nama');
// => ["Ahmad bin Abdullah", "Fatimah binti Omar", "Aminah binti Sulaiman"]
```

---

#### 4c. Contoh Penggunaan dalam Controller

Berikut adalah contoh bagaimana hubungan model digunakan dalam pengawal sebenar.

##### Papar pembayar dengan senarai pembayaran (sudah dilaksanakan)

Ini adalah kod yang sudah ada dalam `PembayarController@show` projek ini:

```php
public function show(Pembayar $pembayar)
{
    // Eager load pembayaran beserta jenis zakat — elak masalah N+1
    $pembayar->load('pembayarans.jenisZakat');

    return view('pembayar.show', compact('pembayar'));
}
```

**Apa yang berlaku:**

1. `$pembayar->load(...)` — Lazy eager loading. Muat data hubungan selepas model sudah diambil.
2. `'pembayarans.jenisZakat'` — Muat hubungan bersarang (nested). Pembayaran **dan** jenis zakat setiap pembayaran dimuat sekali gus.
3. Dalam Blade, `$pembayar->pembayarans` dan `$bayaran->jenisZakat` boleh diakses tanpa query tambahan.

##### Contoh lain — senarai pembayar dengan kiraan pembayaran

```php
// Dalam PembayarController@index — tambah withCount
public function index(Request $request)
{
    $carian = $request->query('carian');

    $pembayars = Pembayar::query()
        ->withCount('pembayarans')                       // Tambah lajur pembayarans_count
        ->when($carian, fn($query) => $query->carian($carian))
        ->orderBy('nama')
        ->paginate(15)
        ->withQueryString();

    return view('pembayar.index', compact('pembayars', 'carian'));
}

// Dalam paparan index.blade.php, boleh guna:
// {{ $pembayar->pembayarans_count }} pembayaran
```

##### Contoh lain — laporan kutipan mengikut jenis zakat

```php
// Dalam controller laporan
public function laporanKutipan()
{
    $jenisZakats = JenisZakat::aktif()
        ->withCount(['pembayarans as pembayarans_sah_count' => function ($query) {
            $query->where('status', 'sah');
        }])
        ->withSum(['pembayarans as jumlah_kutipan' => function ($query) {
            $query->where('status', 'sah');
        }], 'jumlah')
        ->get();

    return view('laporan.kutipan', compact('jenisZakats'));

    // Dalam Blade:
    // $jenisZakat->pembayarans_sah_count  — bilangan pembayaran sah
    // $jenisZakat->jumlah_kutipan         — jumlah RM kutipan sah
}
```

---

#### 4d. Contoh Penggunaan dalam Paparan (Blade)

##### Papar jumlah bayaran sah seorang pembayar

```blade
{{-- Guna aksesor jumlah_bayaran yang kita cipta dalam model Pembayar --}}
<p>Jumlah Bayaran Sah: RM {{ number_format($pembayar->jumlah_bayaran, 2) }}</p>
```

##### Senarai pembayaran seorang pembayar (sudah dilaksanakan dalam `show.blade.php`)

```blade
{{-- Senarai Pembayaran — menggunakan hubungan hasMany --}}
<div class="bg-white rounded-lg shadow mt-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Senarai Pembayaran</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">No. Resit</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Jenis Zakat</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Tarikh Bayar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Cara Bayar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pembayar->pembayarans as $bayaran)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $bayaran->no_resit }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $bayaran->jenisZakat->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">RM {{ number_format($bayaran->jumlah, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $bayaran->tarikh_bayar->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($bayaran->cara_bayar) }}</td>
                        <td class="px-4 py-3 text-sm">
                            @php
                                $warnaStatus = match($bayaran->status) {
                                    'sah'     => 'bg-emerald-100 text-emerald-800',
                                    'pending' => 'bg-amber-100 text-amber-800',
                                    'batal'   => 'bg-red-100 text-red-800',
                                };
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold {{ $warnaStatus }}">
                                {{ ucfirst($bayaran->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Tiada rekod pembayaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
```

**Perkara penting dalam kod Blade di atas:**

1. **`$pembayar->pembayarans`** — Akses hubungan hasMany. Pulangkan Collection semua pembayaran pembayar tersebut.
2. **`$bayaran->jenisZakat->nama`** — Akses hubungan belongsTo bertingkat. Dapatkan nama jenis zakat melalui pembayaran.
3. **`$bayaran->tarikh_bayar->format('d/m/Y')`** — Kerana kita cast `tarikh_bayar` sebagai `date` dalam model, ia menjadi objek Carbon. Boleh guna `format()` terus.
4. **`@forelse ... @empty ... @endforelse`** — Papar kandungan jika ada data, mesej alternatif jika tiada.
5. **`match($bayaran->status)`** — Guna `match` PHP 8 untuk tentukan warna lencana (badge) mengikut status.

---

#### 4e. Kes Penggunaan Lazim (Common Use Cases)

Jadual ini merangkum pertanyaan biasa yang boleh dijawab menggunakan hubungan Eloquent dalam Sistem Zakat:

| # | Kes Penggunaan | Kod Laravel | Penjelasan |
|---|---------------|-------------|-----------|
| 1 | Senarai pembayaran seorang pembayar | `$pembayar->pembayarans` | Hubungan `hasMany` — akses terus seperti property |
| 2 | Nama pembayar dari pembayaran | `$pembayaran->pembayar->nama` | Hubungan `belongsTo` — akses model induk |
| 3 | Jenis zakat dari pembayaran | `$pembayaran->jenisZakat->nama` | Hubungan `belongsTo` — akses model induk |
| 4 | Jumlah kutipan sah keseluruhan | `Pembayaran::sah()->sum('jumlah')` | Gabungan scope + aggregate function |
| 5 | Pembayar paling banyak bayar | `Pembayar::withCount('pembayarans')->orderByDesc('pembayarans_count')->first()` | `withCount` menambah lajur kiraan |
| 6 | Elak N+1 query | `Pembayaran::with(['pembayar', 'jenisZakat'])->paginate(15)` | Eager loading — muat semua hubungan dalam 2-3 query |
| 7 | Pembayar yang belum bayar | `Pembayar::doesntHave('pembayarans')->get()` | `doesntHave` — cari rekod tanpa hubungan |
| 8 | Pembayar yang pernah bayar | `Pembayar::has('pembayarans')->get()` | `has` — cari rekod yang ada hubungan |
| 9 | Jumlah kutipan per jenis | `JenisZakat::withSum('pembayarans', 'jumlah')->get()` | `withSum` — kira jumlah lajur dalam hubungan |
| 10 | Jenis zakat paling popular | `JenisZakat::withCount('pembayarans')->orderByDesc('pembayarans_count')->first()` | `withCount` + `orderByDesc` |
| 11 | Pembayar bayar >= 3 kali | `Pembayar::has('pembayarans', '>=', 3)->get()` | `has` dengan operator perbandingan |
| 12 | Pembayar bayar zakat tertentu | `Pembayar::whereHas('pembayarans', fn($q) => $q->where('jenis_zakat_id', 2))->get()` | `whereHas` — syarat pada hubungan |
| 13 | Muat hubungan selepas query | `$pembayar->load('pembayarans.jenisZakat')` | Lazy eager loading — muat kemudian |
| 14 | Bilangan + jumlah sekali gus | `Pembayar::withCount('pembayarans')->withSum('pembayarans', 'jumlah')->get()` | Gabungan `withCount` dan `withSum` |

---

#### 4f. Perbezaan Kaedah Query Hubungan

| Method | Fungsi | Contoh | Hasil |
|--------|--------|--------|-------|
| `has('hubungan')` | Tapis — hanya rekod yang **ada** hubungan | `Pembayar::has('pembayarans')->get()` | 8 pembayar (yang ada pembayaran) |
| `doesntHave('hubungan')` | Tapis — hanya rekod yang **tiada** hubungan | `Pembayar::doesntHave('pembayarans')->get()` | 2 pembayar (tiada pembayaran) |
| `whereHas('hubungan', fn)` | Tapis — ada hubungan **dengan syarat** | `Pembayar::whereHas('pembayarans', fn($q) => $q->sah())->get()` | Pembayar yang ada bayaran sah |
| `withCount('hubungan')` | Tambah lajur **bilangan** hubungan | `Pembayar::withCount('pembayarans')->get()` | `$p->pembayarans_count` |
| `withSum('hubungan', 'lajur')` | Tambah lajur **jumlah** dari hubungan | `JenisZakat::withSum('pembayarans', 'jumlah')->get()` | `$j->pembayarans_sum_jumlah` |
| `with('hubungan')` | **Eager load** — muat data hubungan sekali gus | `Pembayaran::with('pembayar')->get()` | Elak N+1 query |
| `load('hubungan')` | **Lazy eager load** — muat selepas model diambil | `$pembayar->load('pembayarans')` | Muat hubungan kemudian |

---

#### 4g. Memahami Masalah N+1 Query

Masalah N+1 adalah salah satu isu prestasi paling biasa dalam aplikasi Laravel (dan mana-mana aplikasi yang menggunakan ORM). Ia berlaku apabila kita mengakses hubungan dalam gelung tanpa eager loading.

##### Contoh masalah N+1

```php
// Query 1: SELECT * FROM pembayarans
$pembayarans = Pembayaran::all();  // 1 query

foreach ($pembayarans as $bayaran) {
    // Query 2, 3, 4, ... N: SELECT * FROM pembayars WHERE id = ?
    echo $bayaran->pembayar->nama;  // 1 query setiap iterasi!
}
// Jumlah: 1 + 20 = 21 query (jika 20 pembayaran)
```

**Mengapa ini masalah?** Jika ada 1000 pembayaran, kita akan buat 1001 query ke pangkalan data. Ini sangat perlahan dan membazir sumber pelayan.

##### Penyelesaian dengan eager loading

```php
// Query 1: SELECT * FROM pembayarans
// Query 2: SELECT * FROM pembayars WHERE id IN (1, 2, 3, 5, 6, 8, 9, 10)
$pembayarans = Pembayaran::with('pembayar')->get();  // 2 query sahaja!

foreach ($pembayarans as $bayaran) {
    echo $bayaran->pembayar->nama;  // Tiada query tambahan!
}
// Jumlah: 2 query sahaja (tidak kira berapa banyak pembayaran)
```

##### Eager load pelbagai hubungan sekaligus

```php
// 3 query sahaja — untuk pembayarans, pembayars, dan jenis_zakats
$pembayarans = Pembayaran::with(['pembayar', 'jenisZakat'])->get();

foreach ($pembayarans as $bayaran) {
    echo $bayaran->pembayar->nama . ' - ' . $bayaran->jenisZakat->nama;
    // Tiada query tambahan!
}
```

##### Eager load bersarang (nested)

```php
// Muat pembayar -> pembayarans -> jenis zakat
$pembayars = Pembayar::with('pembayarans.jenisZakat')->get();

foreach ($pembayars as $pembayar) {
    foreach ($pembayar->pembayarans as $bayaran) {
        echo $bayaran->jenisZakat->nama;  // Tiada query tambahan
    }
}
// 3 query sahaja walaupun ada banyak pembayar dan pembayaran
```

##### Cara kenal pasti masalah N+1

Gunakan `DB::enableQueryLog()` dalam tinker:

```php
\DB::enableQueryLog();

// Kod yang disyaki ada N+1...
$pembayarans = \App\Models\Pembayaran::all();
foreach ($pembayarans as $b) { $b->pembayar->nama; }

// Semak berapa query yang dijalankan
count(\DB::getQueryLog());
// => 21 (masalah N+1!)

// Cuba dengan eager loading
\DB::flushQueryLog();
$pembayarans = \App\Models\Pembayaran::with('pembayar')->get();
foreach ($pembayarans as $b) { $b->pembayar->nama; }

count(\DB::getQueryLog());
// => 2 (selesai!)
```

---

### Langkah 5: Cipta Seeder

Seeder digunakan untuk memasukkan data contoh ke dalam pangkalan data. Ini memudahkan pengujian dan pembangunan.

#### 5a. Seeder Pembayar

**Fail:** `database/seeders/PembayarSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Pembayar;
use Illuminate\Database\Seeder;

class PembayarSeeder extends Seeder
{
    /**
     * Masukkan 10 rekod pembayar contoh.
     */
    public function run(): void
    {
        $pembayars = [
            [
                'nama' => 'Ahmad bin Abdullah',
                'no_ic' => '850101145678',
                'alamat' => 'No. 12, Jalan Sultan Badlishah, 05000 Alor Setar, Kedah',
                'no_tel' => '0124567890',
                'email' => 'ahmad@email.com',
                'pekerjaan' => 'Guru',
                'pendapatan_bulanan' => 4500.00,
            ],
            [
                'nama' => 'Siti Nurhaliza binti Mohd Razali',
                'no_ic' => '900215085432',
                'alamat' => 'Lot 5, Taman Aman, 06000 Jitra, Kedah',
                'no_tel' => '0135678901',
                'email' => 'siti.nurhaliza@email.com',
                'pekerjaan' => 'Jururawat',
                'pendapatan_bulanan' => 3800.00,
            ],
            [
                'nama' => 'Mohd Faizal bin Hassan',
                'no_ic' => '880730021234',
                'alamat' => 'No. 8, Lorong Mawar 3, Taman Sejahtera, 08000 Sungai Petani, Kedah',
                'no_tel' => '0196789012',
                'email' => 'faizal.hassan@email.com',
                'pekerjaan' => 'Jurutera',
                'pendapatan_bulanan' => 6500.00,
            ],
            [
                'nama' => 'Nor Azizah binti Ibrahim',
                'no_ic' => '920503145566',
                'alamat' => 'Blok C-3-12, Pangsapuri Damai, 05150 Alor Setar, Kedah',
                'no_tel' => '0167890123',
                'email' => null,
                'pekerjaan' => 'Suri rumah',
                'pendapatan_bulanan' => null,
            ],
            [
                'nama' => 'Ismail bin Yusof',
                'no_ic' => '780420025678',
                'alamat' => 'No. 45, Kampung Baru, 06700 Pendang, Kedah',
                'no_tel' => '0148901234',
                'email' => 'ismail.yusof@email.com',
                'pekerjaan' => 'Peniaga',
                'pendapatan_bulanan' => 5200.00,
            ],
            [
                'nama' => 'Fatimah binti Omar',
                'no_ic' => '950812146789',
                'alamat' => 'No. 22, Jalan Pegawai, 05100 Alor Setar, Kedah',
                'no_tel' => '0179012345',
                'email' => 'fatimah.omar@email.com',
                'pekerjaan' => 'Pegawai Tadbir',
                'pendapatan_bulanan' => 4200.00,
            ],
            [
                'nama' => 'Razak bin Che Mat',
                'no_ic' => '830625087890',
                'alamat' => 'Lot 18, Felda Sungai Tiang, 09300 Kuala Ketil, Kedah',
                'no_tel' => '0110123456',
                'email' => null,
                'pekerjaan' => 'Petani',
                'pendapatan_bulanan' => 2800.00,
            ],
            [
                'nama' => 'Nurul Huda binti Zakaria',
                'no_ic' => '970118141234',
                'alamat' => 'No. 3, Jalan Putra, Taman Putra, 05200 Alor Setar, Kedah',
                'no_tel' => '0181234567',
                'email' => 'nurul.huda@email.com',
                'pekerjaan' => 'Akauntan',
                'pendapatan_bulanan' => 5800.00,
            ],
            [
                'nama' => 'Hassan bin Daud',
                'no_ic' => '800903025432',
                'alamat' => 'No. 67, Jalan Langgar, 05460 Alor Setar, Kedah',
                'no_tel' => '0192345678',
                'email' => 'hassan.daud@email.com',
                'pekerjaan' => 'Pemandu',
                'pendapatan_bulanan' => 2500.00,
            ],
            [
                'nama' => 'Aminah binti Sulaiman',
                'no_ic' => '860417145678',
                'alamat' => 'No. 9, Taman Harmoni, 06550 Changlun, Kedah',
                'no_tel' => '0163456789',
                'email' => 'aminah.sulaiman@email.com',
                'pekerjaan' => 'Pensyarah',
                'pendapatan_bulanan' => 7200.00,
            ],
        ];

        foreach ($pembayars as $pembayar) {
            Pembayar::create($pembayar);
        }
    }
}
```

#### 5b. Seeder JenisZakat

**Fail:** `database/seeders/JenisZakatSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\JenisZakat;
use Illuminate\Database\Seeder;

class JenisZakatSeeder extends Seeder
{
    /**
     * Masukkan 5 jenis zakat contoh.
     */
    public function run(): void
    {
        $jenisZakats = [
            [
                'nama'       => 'Zakat Fitrah',
                'kadar'      => 7.0000,
                'penerangan' => 'Zakat yang wajib dibayar oleh setiap individu Muslim pada bulan Ramadan. Kadar ditetapkan berdasarkan harga beras tempatan.',
                'is_aktif'   => true,
            ],
            [
                'nama'       => 'Zakat Pendapatan',
                'kadar'      => 2.5000,
                'penerangan' => 'Zakat yang dikenakan ke atas pendapatan penggajian dan pendapatan bebas yang telah mencapai nisab.',
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
                'penerangan' => 'Zakat yang dikenakan ke atas wang simpanan yang telah cukup haul (setahun) dan mencapai nisab.',
                'is_aktif'   => true,
            ],
            [
                'nama'       => 'Zakat Emas',
                'kadar'      => 2.5000,
                'penerangan' => 'Zakat yang dikenakan ke atas emas yang disimpan (tidak dipakai) yang telah cukup haul dan nisab.',
                'is_aktif'   => true,
            ],
        ];

        foreach ($jenisZakats as $jenis) {
            JenisZakat::create($jenis);
        }
    }
}
```

#### 5c. Seeder Pembayaran

**Fail:** `database/seeders/PembayaranSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Pembayaran;
use Illuminate\Database\Seeder;

class PembayaranSeeder extends Seeder
{
    /**
     * Masukkan 20 rekod pembayaran contoh.
     */
    public function run(): void
    {
        $pembayarans = [
            // Pembayar 1 — Ahmad bin Abdullah (3 pembayaran)
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
                'pembayar_id'    => 1,
                'jenis_zakat_id' => 4,
                'jumlah'         => 500.00,
                'tarikh_bayar'   => '2024-09-01',
                'cara_bayar'     => 'online',
                'no_resit'       => 'ZK-2024-0003',
                'status'         => 'pending',
            ],
            // Pembayar 2 — Siti Nurhaliza (2 pembayaran)
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
                'pembayar_id'    => 2,
                'jenis_zakat_id' => 2,
                'jumlah'         => 1140.00,
                'tarikh_bayar'   => '2024-07-10',
                'cara_bayar'     => 'kad',
                'no_resit'       => 'ZK-2024-0005',
                'status'         => 'sah',
            ],
            // Pembayar 3 — Mohd Faizal (3 pembayaran)
            [
                'pembayar_id'    => 3,
                'jenis_zakat_id' => 1,
                'jumlah'         => 7.00,
                'tarikh_bayar'   => '2024-03-27',
                'cara_bayar'     => 'tunai',
                'no_resit'       => 'ZK-2024-0006',
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
                'pembayar_id'    => 3,
                'jenis_zakat_id' => 3,
                'jumlah'         => 3200.00,
                'tarikh_bayar'   => '2024-08-15',
                'cara_bayar'     => 'online',
                'no_resit'       => 'ZK-2024-0008',
                'status'         => 'sah',
            ],
            // Pembayar 5 — Ismail bin Yusof (3 pembayaran)
            [
                'pembayar_id'    => 5,
                'jenis_zakat_id' => 1,
                'jumlah'         => 7.00,
                'tarikh_bayar'   => '2024-03-30',
                'cara_bayar'     => 'tunai',
                'no_resit'       => 'ZK-2024-0009',
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
            [
                'pembayar_id'    => 5,
                'jenis_zakat_id' => 2,
                'jumlah'         => 1560.00,
                'tarikh_bayar'   => '2024-10-05',
                'cara_bayar'     => 'online',
                'no_resit'       => 'ZK-2024-0011',
                'status'         => 'batal',
            ],
            // Pembayar 6 — Fatimah binti Omar (2 pembayaran)
            [
                'pembayar_id'    => 6,
                'jenis_zakat_id' => 1,
                'jumlah'         => 7.00,
                'tarikh_bayar'   => '2024-03-28',
                'cara_bayar'     => 'kad',
                'no_resit'       => 'ZK-2024-0012',
                'status'         => 'sah',
            ],
            [
                'pembayar_id'    => 6,
                'jenis_zakat_id' => 2,
                'jumlah'         => 1260.00,
                'tarikh_bayar'   => '2024-08-20',
                'cara_bayar'     => 'fpx',
                'no_resit'       => 'ZK-2024-0013',
                'status'         => 'pending',
            ],
            // Pembayar 8 — Nurul Huda (3 pembayaran)
            [
                'pembayar_id'    => 8,
                'jenis_zakat_id' => 1,
                'jumlah'         => 7.00,
                'tarikh_bayar'   => '2024-03-26',
                'cara_bayar'     => 'tunai',
                'no_resit'       => 'ZK-2024-0014',
                'status'         => 'sah',
            ],
            [
                'pembayar_id'    => 8,
                'jenis_zakat_id' => 2,
                'jumlah'         => 1740.00,
                'tarikh_bayar'   => '2024-04-15',
                'cara_bayar'     => 'fpx',
                'no_resit'       => 'ZK-2024-0015',
                'status'         => 'sah',
            ],
            [
                'pembayar_id'    => 8,
                'jenis_zakat_id' => 4,
                'jumlah'         => 800.00,
                'tarikh_bayar'   => '2024-07-01',
                'cara_bayar'     => 'online',
                'no_resit'       => 'ZK-2024-0016',
                'status'         => 'sah',
            ],
            // Pembayar 9 — Hassan bin Daud (1 pembayaran)
            [
                'pembayar_id'    => 9,
                'jenis_zakat_id' => 1,
                'jumlah'         => 7.00,
                'tarikh_bayar'   => '2024-03-31',
                'cara_bayar'     => 'tunai',
                'no_resit'       => 'ZK-2024-0017',
                'status'         => 'sah',
            ],
            // Pembayar 10 — Aminah binti Sulaiman (3 pembayaran)
            [
                'pembayar_id'    => 10,
                'jenis_zakat_id' => 1,
                'jumlah'         => 7.00,
                'tarikh_bayar'   => '2024-03-25',
                'cara_bayar'     => 'tunai',
                'no_resit'       => 'ZK-2024-0018',
                'status'         => 'sah',
            ],
            [
                'pembayar_id'    => 10,
                'jenis_zakat_id' => 2,
                'jumlah'         => 2160.00,
                'tarikh_bayar'   => '2024-05-10',
                'cara_bayar'     => 'fpx',
                'no_resit'       => 'ZK-2024-0019',
                'status'         => 'sah',
            ],
            [
                'pembayar_id'    => 10,
                'jenis_zakat_id' => 5,
                'jumlah'         => 1200.00,
                'tarikh_bayar'   => '2024-09-15',
                'cara_bayar'     => 'online',
                'no_resit'       => 'ZK-2024-0020',
                'status'         => 'pending',
            ],
        ];

        foreach ($pembayarans as $pembayaran) {
            Pembayaran::create($pembayaran);
        }
    }
}
```

**Data contoh merangkumi:**
- 8 daripada 10 pembayar mempunyai pembayaran (Pembayar 4 dan 7 tiada — berguna untuk ujian `doesntHave`).
- Campuran status: `sah` (majoriti), `pending` (3 rekod), `batal` (1 rekod).
- Pelbagai cara bayar: `tunai`, `kad`, `fpx`, `online`.
- 5 jenis zakat berbeza digunakan dalam pembayaran.

#### 5d. DatabaseSeeder

**Fail:** `database/seeders/DatabaseSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed pangkalan data aplikasi.
     */
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

> **Susunan penting:** `PembayarSeeder` dan `JenisZakatSeeder` mesti dijalankan **sebelum** `PembayaranSeeder` kerana jadual `pembayarans` merujuk kepada kedua-dua jadual tersebut melalui kunci asing (`foreignId`). Jika susunan terbalik, seeder akan gagal dengan ralat integriti kunci asing.

---

## Bahagian B: Pengawal & Penghalaan

### Langkah 6: Cipta Pengawal PembayarController

Pengawal sumber (resource controller) menyediakan 7 kaedah standard untuk operasi CRUD.

#### Arahan Artisan

```bash
php artisan make:controller PembayarController --resource
```

Bendera `--resource` mencipta 7 kaedah automatik: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.

#### Fail Pengawal

**Fail:** `app/Http/Controllers/PembayarController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pembayar;
use Illuminate\Http\Request;

class PembayarController extends Controller
{
    /**
     * Papar senarai pembayar zakat.
     */
    public function index(Request $request)
    {
        $carian = $request->query('carian');

        $pembayars = Pembayar::query()
            ->when($carian, fn($query) => $query->carian($carian))
            ->orderBy('nama')
            ->paginate(15)
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
        $validated = $request->validate([
            'nama'                => 'required|string|max:255',
            'no_ic'               => 'required|string|size:12|unique:pembayars,no_ic',
            'alamat'              => 'required|string',
            'no_tel'              => 'required|string|max:15',
            'email'               => 'nullable|email|max:255',
            'pekerjaan'           => 'nullable|string|max:255',
            'pendapatan_bulanan'  => 'nullable|numeric|min:0',
        ], [
            'nama.required'       => 'Sila masukkan nama penuh.',
            'no_ic.required'      => 'Sila masukkan No. Kad Pengenalan.',
            'no_ic.size'          => 'No. KP mestilah 12 digit.',
            'no_ic.unique'        => 'No. KP ini telah didaftarkan.',
            'alamat.required'     => 'Sila masukkan alamat.',
            'no_tel.required'     => 'Sila masukkan nombor telefon.',
            'email.email'         => 'Format e-mel tidak sah.',
            'pendapatan_bulanan.numeric' => 'Pendapatan mestilah dalam bentuk angka.',
            'pendapatan_bulanan.min'     => 'Pendapatan tidak boleh negatif.',
        ]);

        Pembayar::create($validated);

        return redirect()
            ->route('pembayar.index')
            ->with('success', 'Pembayar berjaya didaftarkan.');
    }

    /**
     * Papar maklumat lengkap pembayar.
     */
    public function show(Pembayar $pembayar)
    {
        // Eager load pembayaran beserta jenis zakat — elak masalah N+1
        $pembayar->load('pembayarans.jenisZakat');

        return view('pembayar.show', compact('pembayar'));
    }

    /**
     * Papar borang kemaskini pembayar.
     */
    public function edit(Pembayar $pembayar)
    {
        return view('pembayar.edit', compact('pembayar'));
    }

    /**
     * Kemaskini maklumat pembayar.
     */
    public function update(Request $request, Pembayar $pembayar)
    {
        $validated = $request->validate([
            'nama'                => 'required|string|max:255',
            'no_ic'               => 'required|string|size:12|unique:pembayars,no_ic,' . $pembayar->id,
            'alamat'              => 'required|string',
            'no_tel'              => 'required|string|max:15',
            'email'               => 'nullable|email|max:255',
            'pekerjaan'           => 'nullable|string|max:255',
            'pendapatan_bulanan'  => 'nullable|numeric|min:0',
        ], [
            'nama.required'       => 'Sila masukkan nama penuh.',
            'no_ic.required'      => 'Sila masukkan No. Kad Pengenalan.',
            'no_ic.size'          => 'No. KP mestilah 12 digit.',
            'no_ic.unique'        => 'No. KP ini telah didaftarkan.',
            'alamat.required'     => 'Sila masukkan alamat.',
            'no_tel.required'     => 'Sila masukkan nombor telefon.',
            'email.email'         => 'Format e-mel tidak sah.',
            'pendapatan_bulanan.numeric' => 'Pendapatan mestilah dalam bentuk angka.',
            'pendapatan_bulanan.min'     => 'Pendapatan tidak boleh negatif.',
        ]);

        $pembayar->update($validated);

        return redirect()
            ->route('pembayar.show', $pembayar)
            ->with('success', 'Maklumat pembayar berjaya dikemaskini.');
    }

    /**
     * Padam pembayar dari pangkalan data.
     */
    public function destroy(Pembayar $pembayar)
    {
        $pembayar->delete();

        return redirect()
            ->route('pembayar.index')
            ->with('success', 'Pembayar berjaya dipadamkan.');
    }
}
```

#### 7 Kaedah Resource Controller

| Kaedah | HTTP | URI | Nama Laluan | Fungsi |
|--------|------|-----|-------------|--------|
| `index` | GET | `/pembayar` | `pembayar.index` | Papar senarai pembayar |
| `create` | GET | `/pembayar/create` | `pembayar.create` | Papar borang daftar baru |
| `store` | POST | `/pembayar` | `pembayar.store` | Simpan pembayar baru |
| `show` | GET | `/pembayar/{pembayar}` | `pembayar.show` | Papar maklumat lengkap |
| `edit` | GET | `/pembayar/{pembayar}/edit` | `pembayar.edit` | Papar borang kemaskini |
| `update` | PUT/PATCH | `/pembayar/{pembayar}` | `pembayar.update` | Kemaskini maklumat |
| `destroy` | DELETE | `/pembayar/{pembayar}` | `pembayar.destroy` | Padam pembayar |

**Perhatikan** kaedah `show()` — kita gunakan `$pembayar->load('pembayarans.jenisZakat')` untuk eager load hubungan sebelum hantar ke paparan. Ini memastikan tiada masalah N+1 query apabila kita loop senarai pembayaran dalam Blade.

---

### Langkah 7: Cipta Middleware

#### 7a. LogAkses — Log Setiap Permintaan

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
- Middleware ini merekodkan setiap permintaan HTTP ke saluran log `akses`.
- Maklumat yang direkodkan: kaedah HTTP (GET/POST/dll.), URL penuh, alamat IP, dan masa.
- `$next($request)` — Teruskan permintaan ke middleware/pengawal seterusnya.

#### 7b. SemakWaktuPejabat — Hadkan Akses Mengikut Waktu

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
- Middleware ini menyekat akses di luar waktu pejabat (Isnin-Jumaat, 8am-5pm).
- `now()->dayOfWeek` — Hari dalam minggu (0=Ahad, 1=Isnin, ..., 6=Sabtu).
- `now()->hour` — Jam semasa (0-23).
- Jika di luar waktu, paparan ralat `errors.luar-waktu` dipaparkan dengan kod HTTP 403 (Forbidden).

---

### Langkah 8: Daftar Middleware

Dalam Laravel 11+, middleware didaftarkan dalam `bootstrap/app.php`.

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
- `$middleware->alias([...])` — Daftar alias untuk middleware. Alias ini boleh digunakan dalam laluan.
- `'waktu.pejabat'` — Alias untuk middleware `SemakWaktuPejabat`. Guna dalam laluan: `->middleware('waktu.pejabat')`.
- `'log.akses'` — Alias untuk middleware `LogAkses`. Guna dalam laluan: `->middleware('log.akses')`.

---

### Langkah 9: Cipta SemakController & MaklumatController

#### 9a. SemakController — Semakan Sistem

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

#### 9b. MaklumatController — Senarai Laluan

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

---

### Langkah 10: Tetapkan Laluan

**Fail:** `routes/web.php`

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

// Contoh kumpulan laluan dengan prefix (demonstrasi konsep)
Route::prefix('admin')->name('admin.')->middleware(['log.akses'])->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
});
```

**Konsep penting dalam penghalaan:**

| Konsep | Contoh | Penerangan |
|--------|--------|-----------|
| `Route::resource` | `Route::resource('pembayar', PembayarController::class)` | Cipta 7 laluan CRUD secara automatik untuk resource controller |
| `Route::get` | `Route::get('/semak', ...)` | Laluan tunggal untuk kaedah HTTP GET |
| `->name()` | `->name('semak')` | Namakan laluan — guna `route('semak')` dalam Blade untuk jana URL |
| `Route::middleware` | `Route::middleware(['log.akses'])` | Lampirkan middleware ke kumpulan laluan |
| `Route::prefix` | `Route::prefix('admin')` | Tambah prefix URI (cth: `/admin/dashboard`) |
| `->name('admin.')` | `->name('admin.')` | Tambah prefix nama laluan (cth: `admin.dashboard`) |
| `->group()` | `->group(function () { ... })` | Kumpulkan laluan yang berkongsi tetapan |
| `fn() =>` | `fn() => redirect()->route(...)` | Arrow function — laluan ringkas tanpa pengawal |

---

## Bahagian C: Paparan (Blade)

### Langkah 11: Cipta Layout

Layout adalah rangka utama halaman yang dikongsi oleh semua paparan.

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

**Konsep Blade dalam layout:**

| Konsep | Contoh | Penerangan |
|--------|--------|-----------|
| `@yield('title', 'default')` | `@yield('title', 'Sistem Zakat Kedah')` | Titik sisipan — halaman anak boleh isi kandungan. Jika tidak diisi, guna nilai lalai. |
| `@yield('content')` | `@yield('content')` | Titik sisipan utama untuk kandungan halaman. |
| `{{ }}` | `{{ session('success') }}` | Output dengan escape HTML — selamat daripada serangan XSS. |
| `@if ... @endif` | `@if (session('success')) ... @endif` | Blok syarat — papar hanya jika syarat dipenuhi. |
| `request()->routeIs()` | `request()->routeIs('pembayar.*')` | Semak laluan semasa — guna untuk highlight pautan navigasi aktif. |
| `{{ csrf_token() }}` | `<meta name="csrf-token" ...>` | Token CSRF untuk keselamatan — diperlukan untuk permintaan POST/PUT/DELETE. |

### Langkah 12: Cipta Paparan CRUD Pembayar

Paparan CRUD untuk model Pembayar menggunakan layout yang telah kita cipta.

#### 12a. Senarai Pembayar — `resources/views/pembayar/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Senarai Pembayar Zakat')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Senarai Pembayar Zakat</h1>
        <a href="{{ route('pembayar.create') }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg transition">
            + Daftar Pembayar Baru
        </a>
    </div>

    {{-- Borang carian --}}
    <form action="{{ route('pembayar.index') }}" method="GET" class="mb-6">
        <div class="flex gap-2">
            <input type="text" name="carian" value="{{ $carian ?? '' }}"
                   placeholder="Cari nama atau No. KP..."
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition">
                Cari
            </button>
            @if ($carian)
                <a href="{{ route('pembayar.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                    Padam Carian
                </a>
            @endif
        </div>
    </form>

    {{-- Jadual pembayar --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">No. IC</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">No. Tel</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">E-mel</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Pendapatan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-emerald-800 uppercase">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pembayars as $index => $pembayar)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $pembayars->firstItem() + $index }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $pembayar->nama }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $pembayar->ic_format }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $pembayar->no_tel }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $pembayar->email ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $pembayar->pendapatan_format }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('pembayar.show', $pembayar) }}"
                                       class="text-emerald-600 hover:text-emerald-800 font-medium">Lihat</a>
                                    <a href="{{ route('pembayar.edit', $pembayar) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium">Kemaskini</a>
                                    <form action="{{ route('pembayar.destroy', $pembayar) }}" method="POST"
                                          onsubmit="return confirm('Adakah anda pasti mahu memadam pembayar ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 font-medium">Padam</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                @if ($carian)
                                    Tiada pembayar dijumpai untuk carian "{{ $carian }}".
                                @else
                                    Tiada rekod pembayar. <a href="{{ route('pembayar.create') }}" class="text-emerald-600 hover:underline">Daftar pembayar baru</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination dan kiraan --}}
    @if ($pembayars->hasPages())
        <div class="mt-4">
            {{ $pembayars->links() }}
        </div>
    @endif

    <div class="mt-3 text-sm text-gray-500">
        Menunjukkan {{ $pembayars->firstItem() ?? 0 }} hingga {{ $pembayars->lastItem() ?? 0 }}
        daripada {{ $pembayars->total() }} rekod.
    </div>
@endsection
```

#### 12b. Daftar Baru — `resources/views/pembayar/create.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Daftar Pembayar Baru')

@section('content')
    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('pembayar.index') }}" class="hover:text-emerald-600">Senarai Pembayar</a>
        <span class="mx-1">/</span>
        <span class="text-gray-800">Daftar Baru</span>
    </nav>

    <h1 class="text-2xl font-bold text-gray-800 mb-6">Daftar Pembayar Baru</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('pembayar.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nama Penuh --}}
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Penuh <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}"
                           class="w-full border {{ $errors->has('nama') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No. KP --}}
                <div>
                    <label for="no_ic" class="block text-sm font-medium text-gray-700 mb-1">No. Kad Pengenalan <span class="text-red-500">*</span></label>
                    <input type="text" name="no_ic" id="no_ic" value="{{ old('no_ic') }}" maxlength="12"
                           placeholder="Contoh: 850101145678"
                           class="w-full border {{ $errors->has('no_ic') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('no_ic')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat <span class="text-red-500">*</span></label>
                    <textarea name="alamat" id="alamat" rows="3"
                              class="w-full border {{ $errors->has('alamat') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No. Tel --}}
                <div>
                    <label for="no_tel" class="block text-sm font-medium text-gray-700 mb-1">No. Telefon <span class="text-red-500">*</span></label>
                    <input type="text" name="no_tel" id="no_tel" value="{{ old('no_tel') }}" maxlength="15"
                           placeholder="Contoh: 0124567890"
                           class="w-full border {{ $errors->has('no_tel') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('no_tel')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- E-mel --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mel</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           placeholder="Contoh: ahmad@email.com"
                           class="w-full border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pekerjaan --}}
                <div>
                    <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                    <input type="text" name="pekerjaan" id="pekerjaan" value="{{ old('pekerjaan') }}"
                           class="w-full border {{ $errors->has('pekerjaan') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('pekerjaan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pendapatan Bulanan --}}
                <div>
                    <label for="pendapatan_bulanan" class="block text-sm font-medium text-gray-700 mb-1">Pendapatan Bulanan (RM)</label>
                    <input type="number" name="pendapatan_bulanan" id="pendapatan_bulanan" value="{{ old('pendapatan_bulanan') }}"
                           step="0.01" min="0" placeholder="Contoh: 3500.00"
                           class="w-full border {{ $errors->has('pendapatan_bulanan') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('pendapatan_bulanan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Butang --}}
            <div class="flex gap-3 mt-6">
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    Simpan
                </button>
                <a href="{{ route('pembayar.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
```

#### 12c. Maklumat Pembayar (dengan Senarai Pembayaran) — `resources/views/pembayar/show.blade.php`

Paparan ini telah dikemaskini untuk memaparkan **senarai pembayaran** menggunakan hubungan `hasMany`.

```blade
@extends('layouts.app')

@section('title', $pembayar->nama)

@section('content')
    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('pembayar.index') }}" class="hover:text-emerald-600">Senarai Pembayar</a>
        <span class="mx-1">/</span>
        <span class="text-gray-800">{{ $pembayar->nama }}</span>
    </nav>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">{{ $pembayar->nama }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('pembayar.edit', $pembayar) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                Kemaskini
            </a>
            <form action="{{ route('pembayar.destroy', $pembayar) }}" method="POST"
                  onsubmit="return confirm('Adakah anda pasti mahu memadam pembayar ini?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    Padam
                </button>
            </form>
            <a href="{{ route('pembayar.index') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition">
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-500">Nama Penuh</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->nama }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">No. Kad Pengenalan</p>
                <p class="text-lg font-medium text-gray-900 font-mono">{{ $pembayar->ic_format }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Alamat</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->alamat }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">No. Telefon</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->no_tel }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">E-mel</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->email ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Pekerjaan</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->pekerjaan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Pendapatan Bulanan</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->pendapatan_format }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tarikh Didaftarkan</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tarikh Dikemaskini</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->updated_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Jumlah Bayaran Sah</p>
                <p class="text-lg font-medium text-emerald-700">RM {{ number_format($pembayar->jumlah_bayaran, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Senarai Pembayaran — menggunakan hubungan hasMany --}}
    <div class="bg-white rounded-lg shadow mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Senarai Pembayaran</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">No. Resit</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Jenis Zakat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Tarikh Bayar</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Cara Bayar</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pembayar->pembayarans as $bayaran)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $bayaran->no_resit }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $bayaran->jenisZakat->nama }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">RM {{ number_format($bayaran->jumlah, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $bayaran->tarikh_bayar->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($bayaran->cara_bayar) }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $warnaStatus = match($bayaran->status) {
                                        'sah'     => 'bg-emerald-100 text-emerald-800',
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        'batal'   => 'bg-red-100 text-red-800',
                                    };
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold {{ $warnaStatus }}">
                                    {{ ucfirst($bayaran->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Tiada rekod pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
```

#### 12d. Kemaskini — `resources/views/pembayar/edit.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Kemaskini — ' . $pembayar->nama)

@section('content')
    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('pembayar.index') }}" class="hover:text-emerald-600">Senarai Pembayar</a>
        <span class="mx-1">/</span>
        <a href="{{ route('pembayar.show', $pembayar) }}" class="hover:text-emerald-600">{{ $pembayar->nama }}</a>
        <span class="mx-1">/</span>
        <span class="text-gray-800">Kemaskini</span>
    </nav>

    <h1 class="text-2xl font-bold text-gray-800 mb-6">Kemaskini Pembayar</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('pembayar.update', $pembayar) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nama Penuh --}}
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Penuh <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama', $pembayar->nama) }}"
                           class="w-full border {{ $errors->has('nama') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No. KP --}}
                <div>
                    <label for="no_ic" class="block text-sm font-medium text-gray-700 mb-1">No. Kad Pengenalan <span class="text-red-500">*</span></label>
                    <input type="text" name="no_ic" id="no_ic" value="{{ old('no_ic', $pembayar->no_ic) }}" maxlength="12"
                           class="w-full border {{ $errors->has('no_ic') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('no_ic')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat <span class="text-red-500">*</span></label>
                    <textarea name="alamat" id="alamat" rows="3"
                              class="w-full border {{ $errors->has('alamat') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('alamat', $pembayar->alamat) }}</textarea>
                    @error('alamat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No. Tel --}}
                <div>
                    <label for="no_tel" class="block text-sm font-medium text-gray-700 mb-1">No. Telefon <span class="text-red-500">*</span></label>
                    <input type="text" name="no_tel" id="no_tel" value="{{ old('no_tel', $pembayar->no_tel) }}" maxlength="15"
                           class="w-full border {{ $errors->has('no_tel') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('no_tel')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- E-mel --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mel</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $pembayar->email) }}"
                           class="w-full border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pekerjaan --}}
                <div>
                    <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                    <input type="text" name="pekerjaan" id="pekerjaan" value="{{ old('pekerjaan', $pembayar->pekerjaan) }}"
                           class="w-full border {{ $errors->has('pekerjaan') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('pekerjaan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pendapatan Bulanan --}}
                <div>
                    <label for="pendapatan_bulanan" class="block text-sm font-medium text-gray-700 mb-1">Pendapatan Bulanan (RM)</label>
                    <input type="number" name="pendapatan_bulanan" id="pendapatan_bulanan"
                           value="{{ old('pendapatan_bulanan', $pembayar->pendapatan_bulanan) }}"
                           step="0.01" min="0"
                           class="w-full border {{ $errors->has('pendapatan_bulanan') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('pendapatan_bulanan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Butang --}}
            <div class="flex gap-3 mt-6">
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    Kemaskini
                </button>
                <a href="{{ route('pembayar.show', $pembayar) }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
```

---

### Langkah 13: Cipta Paparan Semak, Laluan & Ralat

#### 13a. Semakan Sistem — `resources/views/semak.blade.php`

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

#### 13b. Senarai Laluan — `resources/views/maklumat/laluan.blade.php`

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

#### 13c. Ralat — Luar Waktu Pejabat — `resources/views/errors/luar-waktu.blade.php`

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

---

## Bahagian D: Jalankan & Uji

### Langkah 14: Jalankan Migrasi & Seeder

```bash
# Jalankan migrasi (cipta jadual)
php artisan migrate

# Jalankan seeder (masukkan data contoh)
php artisan db:seed

# Atau jalankan kedua-dua sekali gus
php artisan migrate --seed

# Jika mahu reset dan jalankan semula (PADAM semua data)
php artisan migrate:fresh --seed
```

**Hasil yang dijangka:**

```
Migrating: 0001_01_01_000000_create_users_table
Migrated:  0001_01_01_000000_create_users_table
Migrating: 0001_01_01_000001_create_cache_table
Migrated:  0001_01_01_000001_create_cache_table
Migrating: 0001_01_01_000002_create_jobs_table
Migrated:  0001_01_01_000002_create_jobs_table
Migrating: 2024_01_01_000001_create_pembayars_table
Migrated:  2024_01_01_000001_create_pembayars_table
Migrating: 2024_01_01_000002_create_jenis_zakats_table
Migrated:  2024_01_01_000002_create_jenis_zakats_table
Migrating: 2024_01_01_000003_create_pembayarans_table
Migrated:  2024_01_01_000003_create_pembayarans_table

Seeding: Database\Seeders\PembayarSeeder
Seeded:  Database\Seeders\PembayarSeeder (10 rekod)
Seeding: Database\Seeders\JenisZakatSeeder
Seeded:  Database\Seeders\JenisZakatSeeder (5 rekod)
Seeding: Database\Seeders\PembayaranSeeder
Seeded:  Database\Seeders\PembayaranSeeder (20 rekod)
```

---

### Langkah 15: Uji Hubungan dalam Tinker

Ini adalah langkah paling penting untuk memahami hubungan model. Buka tinker dan cuba setiap arahan satu demi satu.

```bash
php artisan tinker
```

#### Uji 1: hasMany — Pembayar ke Pembayaran

```php
$pembayar = \App\Models\Pembayar::find(1);
echo $pembayar->nama;
// => "Ahmad bin Abdullah"

echo $pembayar->pembayarans->count();
// => 3

$pembayar->pembayarans->pluck('no_resit');
// => ["ZK-2024-0001", "ZK-2024-0002", "ZK-2024-0003"]
```

#### Uji 2: belongsTo — Pembayaran ke Pembayar & JenisZakat

```php
$pembayaran = \App\Models\Pembayaran::find(1);
echo $pembayaran->pembayar->nama;
// => "Ahmad bin Abdullah"

echo $pembayaran->jenisZakat->nama;
// => "Zakat Fitrah"
```

#### Uji 3: Aksesor jumlah_bayaran

```php
$pembayar = \App\Models\Pembayar::find(1);
echo $pembayar->jumlah_bayaran;
// => 1357.0 (hanya pembayaran berstatus 'sah')
```

#### Uji 4: doesntHave — Pembayar tanpa pembayaran

```php
\App\Models\Pembayar::doesntHave('pembayarans')->pluck('nama');
// => ["Nor Azizah binti Ibrahim", "Razak bin Che Mat"]
```

#### Uji 5: withCount — Bilangan pembayaran

```php
\App\Models\Pembayar::withCount('pembayarans')
    ->orderByDesc('pembayarans_count')
    ->get()
    ->each(fn($p) => print("$p->nama: $p->pembayarans_count\n"));
```

#### Uji 6: Jenis zakat paling popular

```php
\App\Models\JenisZakat::withCount('pembayarans')
    ->orderByDesc('pembayarans_count')
    ->get()
    ->each(fn($j) => print("$j->nama: $j->pembayarans_count\n"));
```

#### Uji 7: Jumlah kutipan per jenis zakat

```php
\App\Models\JenisZakat::withSum('pembayarans', 'jumlah')
    ->get()
    ->each(fn($j) => print("$j->nama: RM " . number_format($j->pembayarans_sum_jumlah, 2) . "\n"));
```

---

### Langkah 16: Uji Aplikasi

Mulakan pelayan pembangunan:

```bash
php artisan serve
```

#### Jadual Ujian URL

| # | URL | Jangkaan | Hubungan Model? |
|---|-----|----------|----------------|
| 1 | `http://localhost:8000/` | Redirect ke senarai pembayar | - |
| 2 | `http://localhost:8000/pembayar` | Senarai 10 pembayar dalam jadual | - |
| 3 | `http://localhost:8000/pembayar?carian=Ahmad` | Hanya pembayar bernama Ahmad | Skop `carian` |
| 4 | `http://localhost:8000/pembayar/1` | Maklumat Ahmad + **senarai 3 pembayaran** | `hasMany`, `belongsTo` |
| 5 | `http://localhost:8000/pembayar/4` | Maklumat Nor Azizah + **"Tiada rekod pembayaran"** | `hasMany` (kosong) |
| 6 | `http://localhost:8000/pembayar/create` | Borang daftar pembayar baru | - |
| 7 | `http://localhost:8000/pembayar/1/edit` | Borang kemaskini Ahmad | - |
| 8 | `http://localhost:8000/semak` | Semakan sistem (PHP, Laravel, DB) | - |
| 9 | `http://localhost:8000/maklumat/laluan` | Senarai semua laluan berdaftar | - |

**Perkara untuk diperhatikan semasa ujian:**

1. **Halaman `pembayar/1`** (Ahmad) — Di bahagian bawah maklumat peribadi, anda akan nampak jadual **Senarai Pembayaran** dengan 3 rekod. Setiap rekod memaparkan jenis zakat (dari hubungan `belongsTo`), jumlah, tarikh, cara bayar, dan status berwarna.
2. **Halaman `pembayar/4`** (Nor Azizah) — Jadual pembayaran akan papar mesej **"Tiada rekod pembayaran"** kerana pembayar ini tiada dalam data seeder pembayaran.
3. **Jumlah Bayaran Sah** — Ditunjukkan dalam maklumat pembayar. Untuk Ahmad: RM 1,357.00 (hanya pembayaran berstatus `sah` dikira, bukan yang `pending`).

---

## Struktur Fail

```
contoh/hari-2/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   ├── MaklumatController.php
│   │   │   ├── PembayarController.php          ← CRUD + eager loading hubungan
│   │   │   └── SemakController.php
│   │   └── Middleware/
│   │       ├── LogAkses.php
│   │       └── SemakWaktuPejabat.php
│   ├── Models/
│   │   ├── JenisZakat.php                      ← BARU: hasMany Pembayaran, scopeAktif
│   │   ├── Pembayar.php                        ← DIKEMASKINI: + hasMany Pembayaran, jumlah_bayaran
│   │   ├── Pembayaran.php                      ← BARU: belongsTo Pembayar & JenisZakat, scopeSah
│   │   └── User.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php                                 ← Daftar middleware alias
├── config/
│   └── ...
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2024_01_01_000001_create_pembayars_table.php
│   │   ├── 2024_01_01_000002_create_jenis_zakats_table.php    ← BARU
│   │   └── 2024_01_01_000003_create_pembayarans_table.php    ← BARU
│   └── seeders/
│       ├── DatabaseSeeder.php                  ← DIKEMASKINI: panggil 3 seeder
│       ├── JenisZakatSeeder.php                ← BARU: 5 jenis zakat
│       ├── PembayarSeeder.php                  ← 10 pembayar
│       └── PembayaranSeeder.php                ← BARU: 20 pembayaran
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php                   ← Layout utama dengan nav dan footer
│       ├── pembayar/
│       │   ├── index.blade.php                 ← Senarai + carian + pagination
│       │   ├── create.blade.php                ← Borang daftar baru
│       │   ├── show.blade.php                  ← DIKEMASKINI: + senarai pembayaran
│       │   └── edit.blade.php                  ← Borang kemaskini
│       ├── maklumat/
│       │   └── laluan.blade.php                ← Senarai laluan berdaftar
│       ├── errors/
│       │   ├── luar-waktu.blade.php            ← Halaman luar waktu pejabat
│       │   ├── 403.blade.php
│       │   └── 404.blade.php
│       ├── admin/
│       │   └── dashboard.blade.php
│       ├── semak.blade.php                     ← Semakan sistem
│       └── welcome.blade.php
├── routes/
│   └── web.php                                 ← Semua laluan aplikasi
├── .env.example
├── composer.json
└── README.md                                   ← Fail ini
```

---

## Rumusan

### Apa yang dipelajari hari ini

| Topik | Apa yang dipelajari |
|-------|-------------------|
| **Model** | Cipta 3 model (`Pembayar`, `JenisZakat`, `Pembayaran`) dengan `$fillable`, `$casts`, skop tempatan, dan aksesor |
| **Hubungan Model** | `hasMany` (satu-ke-banyak), `belongsTo` (banyak-ke-satu), eager loading (`with`, `load`), `withCount`, `withSum`, `has`, `doesntHave`, `whereHas` |
| **Migrasi** | Cipta jadual dengan pelbagai jenis lajur, kunci asing (`foreignId`), `cascadeOnDelete`, `enum`, `unique`, `nullable`, `default` |
| **Seeder** | Masukkan data contoh — 10 pembayar, 5 jenis zakat, 20 pembayaran dengan pelbagai status |
| **Pengawal** | Resource controller (7 kaedah CRUD), controller biasa, eager loading dalam controller |
| **Penghalaan** | `Route::resource`, `Route::get`, kumpulan laluan, prefix, named routes, middleware pada laluan |
| **Middleware** | `LogAkses` (log setiap permintaan), `SemakWaktuPejabat` (hadkan akses mengikut waktu), pendaftaran alias |
| **Paparan Blade** | Layout dengan `@yield`/`@section`, CRUD views, `@forelse`, `@error`, `@csrf`, `@method`, hubungan dalam Blade |

### Peta hubungan lengkap

```
┌─────────────────────────────────────────────────────────────────┐
│                     Sistem Pengurusan Zakat                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Pembayar ──── hasMany ────► Pembayaran ◄──── hasMany ──── JenisZakat
│     │                           │   │                        │
│     │  getJumlahBayaran         │   │  getJumlahFormat       │
│     │  scopeCarian              │   │  scopeSah              │
│     │  getIcFormat              │   │                        │
│     │  getPendapatanFormat      │   │  belongsTo Pembayar    │
│     │                           │   │  belongsTo JenisZakat  │
│     │  pembayarans()            │   │  pembayar()            │
│     │                           │   │  jenisZakat()          │
│                                 │                     scopeAktif
│                                 │                     pembayarans()
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Arahan Artisan yang digunakan

```bash
# ─── Model & Migrasi ───
php artisan make:model Pembayar -m
php artisan make:model JenisZakat -m
php artisan make:model Pembayaran -m

# ─── Pengawal ───
php artisan make:controller PembayarController --resource
php artisan make:controller SemakController
php artisan make:controller MaklumatController

# ─── Middleware ───
php artisan make:middleware LogAkses
php artisan make:middleware SemakWaktuPejabat

# ─── Seeder ───
php artisan make:seeder PembayarSeeder
php artisan make:seeder JenisZakatSeeder
php artisan make:seeder PembayaranSeeder

# ─── Jalankan ───
php artisan migrate --seed
php artisan migrate:fresh --seed
php artisan tinker
php artisan serve
```

### Seterusnya (Hari 3)

Pada Hari 3, kita akan memperdalam lagi topik Blade templates, Eloquent models, migrasi lanjutan, dan validasi data. Kita akan membina paparan yang lebih lengkap termasuk borang pembayaran yang menggunakan hubungan model untuk dropdown jenis zakat, serta mempelajari teknik validasi lanjutan dalam Laravel.
