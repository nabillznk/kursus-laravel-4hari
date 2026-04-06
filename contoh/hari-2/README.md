# Hari 2 -- Penghalaan, Pengawal & Middleware (Panduan Bengkel)

> **Sistem Pengurusan Zakat Kedah** -- Kursus Laravel 4 Hari
> Tempoh: 6 jam | Bahasa: Melayu | Tahap: Permulaan

---

## Pengenalan

Pada Hari 1, kita telah mencipta projek `sistem-zakat`, menyediakan pangkalan data `zakat_kedah`, dan membina persekitaran pembangunan menggunakan Laragon (atau pelayan pembangunan PHP).

Hari ini kita akan membina **keseluruhan sistem CRUD Pembayar** beserta ciri-ciri tambahan:

| # | Topik | Perkara yang Dipelajari |
|---|-------|------------------------|
| 1 | **Model & Migrasi** | Cipta model `Pembayar` dengan migrasi, skop tempatan (local scope), dan aksesor (accessor) |
| 2 | **Pengawal** | Pengawal sumber (resource controller) dengan 7 kaedah CRUD dan pengesahan (validation) |
| 3 | **Seeder** | Data contoh untuk pengujian menggunakan seeder |
| 4 | **Middleware** | Cipta middleware tersuai untuk log akses dan semak waktu pejabat |
| 5 | **Laluan** | Kumpulan laluan, prefix, middleware alias, laluan sumber |
| 6 | **Paparan Blade** | Susun atur (layout), paparan CRUD lengkap, halaman semakan, halaman ralat |
| 7 | **Pengawal Tambahan** | Pengawal untuk semakan sistem dan senarai laluan |

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

## Langkah 1: Cipta Model & Migrasi Pembayar

Model `Pembayar` mewakili pembayar zakat dalam sistem. Kita akan mencipta model berserta fail migrasi untuk jadual pangkalan data.

### Arahan Artisan

```bash
php artisan make:model Pembayar -m
```

Bendera `-m` akan mencipta fail migrasi secara automatik bersama model.

### Fail Migrasi

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

**Penerangan medan:**

| Medan | Jenis | Keterangan |
|-------|-------|------------|
| `id` | Big Integer (auto-increment) | Kunci utama |
| `nama` | String (255) | Nama penuh pembayar |
| `no_ic` | String (12), unik | No. Kad Pengenalan (12 digit tanpa sengkang) |
| `alamat` | Text | Alamat penuh |
| `no_tel` | String (15) | Nombor telefon |
| `email` | String, nullable | Alamat e-mel (pilihan) |
| `pekerjaan` | String, nullable | Pekerjaan (pilihan) |
| `pendapatan_bulanan` | Decimal (10,2), nullable | Pendapatan bulanan dalam RM (pilihan) |
| `created_at` | Timestamp | Tarikh dicipta (dijana oleh `$table->timestamps()`) |
| `updated_at` | Timestamp | Tarikh dikemaskini (dijana oleh `$table->timestamps()`) |

### Fail Model

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

**Penerangan terperinci:**

- **`$fillable`** -- Senarai medan yang dibenarkan untuk mass-assignment. Ini melindungi daripada serangan mass-assignment di mana pengguna mungkin menghantar medan tambahan yang tidak dijangka.

- **`$casts`** -- Menukar jenis data secara automatik. `'pendapatan_bulanan' => 'decimal:2'` memastikan nilai sentiasa dalam format perpuluhan dengan 2 tempat perpuluhan.

- **`scopeCarian($query, $carian)`** -- Skop tempatan yang membolehkan carian mengikut nama atau no IC. Cara penggunaan:
  ```php
  // Dalam pengawal
  Pembayar::carian('Ahmad')->get();

  // Dalam Tinker
  Pembayar::carian('850101')->first();
  ```

- **`getIcFormatAttribute()`** -- Aksesor yang memformat no IC 12 digit kepada format bersengkang. Contoh: `850101145678` menjadi `850101-14-5678`. Cara penggunaan dalam paparan Blade:
  ```blade
  {{ $pembayar->ic_format }}
  ```

- **`getPendapatanFormatAttribute()`** -- Aksesor yang memformat pendapatan dengan simbol RM dan pemisah ribuan. Contoh: `3500.00` menjadi `RM 3,500.00`. Jika pendapatan tiada (null), ia mengembalikan teks `'Tiada maklumat'`. Cara penggunaan dalam paparan Blade:
  ```blade
  {{ $pembayar->pendapatan_format }}
  ```

---

## Langkah 2: Cipta Pengawal Pembayar

Pengawal sumber (resource controller) mengandungi 7 kaedah standard untuk operasi CRUD: `index`, `create`, `store`, `show`, `edit`, `update`, dan `destroy`.

### Arahan Artisan

```bash
php artisan make:controller PembayarController --resource
```

Bendera `--resource` akan menjana pengawal dengan 7 kaedah CRUD yang kosong.

### Fail Pengawal

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

### Penerangan Setiap Kaedah

| Kaedah | Fungsi | Keterangan |
|--------|--------|------------|
| `index()` | Papar senarai | Menyokong carian melalui query string `?carian=`. Menggunakan `when()` untuk menambah skop carian secara bersyarat. Paginate 15 rekod per halaman. `withQueryString()` memastikan parameter carian dikekalkan semasa navigasi halaman. |
| `create()` | Papar borang baru | Mengembalikan paparan borang pendaftaran. Tiada data diperlukan. |
| `store()` | Simpan rekod baru | Mengesahkan input dengan peraturan terperinci dan mesej ralat dalam Bahasa Melayu. `size:12` memastikan no IC tepat 12 digit. `unique:pembayars,no_ic` menghalang pendaftaran berulang. Selepas berjaya, mengalih ke senarai dengan mesej kejayaan. |
| `show()` | Papar butiran | Menggunakan Route Model Binding -- Laravel secara automatik mencari pembayar berdasarkan ID dalam URL. |
| `edit()` | Papar borang kemaskini | Sama seperti `show()` tetapi mengembalikan paparan borang untuk kemaskini. |
| `update()` | Kemaskini rekod | Hampir sama dengan `store()` tetapi dengan `unique:pembayars,no_ic,' . $pembayar->id` untuk mengecualikan rekod semasa daripada semakan keunikan. |
| `destroy()` | Padam rekod | Memadamkan pembayar dan mengalih ke senarai dengan mesej kejayaan. |

### Jadual Laluan Sumber (Resource Routes)

`Route::resource('pembayar', PembayarController::class)` menjana 7 laluan berikut secara automatik:

| Kaedah HTTP | URI | Nama Laluan | Kaedah Pengawal |
|-------------|-----|-------------|-----------------|
| GET | `/pembayar` | pembayar.index | index() |
| GET | `/pembayar/create` | pembayar.create | create() |
| POST | `/pembayar` | pembayar.store | store() |
| GET | `/pembayar/{pembayar}` | pembayar.show | show() |
| GET | `/pembayar/{pembayar}/edit` | pembayar.edit | edit() |
| PUT/PATCH | `/pembayar/{pembayar}` | pembayar.update | update() |
| DELETE | `/pembayar/{pembayar}` | pembayar.destroy | destroy() |

---

## Langkah 3: Cipta Seeder

Seeder digunakan untuk memasukkan data contoh ke dalam pangkalan data supaya kita boleh menguji aplikasi tanpa perlu memasukkan data secara manual.

### Arahan Artisan

```bash
php artisan make:seeder PembayarSeeder
```

### Fail PembayarSeeder

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

**Penerangan:**

- Seeder ini memasukkan 10 rekod pembayar contoh dengan data realistik -- nama, no IC, alamat di Kedah, nombor telefon, e-mel, pekerjaan, dan pendapatan bulanan.
- Perhatikan bahawa beberapa rekod mempunyai `email` dan `pendapatan_bulanan` yang `null` untuk menguji medan pilihan.
- `Pembayar::create()` digunakan supaya mass-assignment dilindungi oleh `$fillable` dan `$casts` diaplikasikan secara automatik.

### Kemaskini DatabaseSeeder

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
        ]);
    }
}
```

**Penerangan:**

- `DatabaseSeeder` adalah seeder utama yang dijalankan apabila kita laksanakan `php artisan db:seed`.
- `$this->call([...])` membolehkan kita mendaftarkan pelbagai seeder dalam satu senarai. Pada masa ini, hanya `PembayarSeeder` didaftarkan. Pada Hari 3, kita akan menambah seeder untuk `JenisZakat` dan `Pembayaran`.

---

## Langkah 4: Cipta Middleware LogAkses

Middleware adalah lapisan penapisan yang memproses permintaan HTTP sebelum atau selepas ia sampai ke pengawal. Middleware `LogAkses` akan merekod setiap permintaan HTTP yang masuk ke dalam fail log khusus.

### Arahan Artisan

```bash
php artisan make:middleware LogAkses
```

### Fail Middleware

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

- `Log::channel('akses')` -- Menulis ke saluran log berasingan bernama `akses` (akan dikonfigurasi di bawah). Ini memisahkan log akses daripada log aplikasi utama.
- `$request->method()` -- Mengembalikan kaedah HTTP (GET, POST, PUT, DELETE, dll.).
- `$request->fullUrl()` -- Mengembalikan URL penuh termasuk query string.
- `$request->ip()` -- Mengembalikan alamat IP pelanggan.
- `now()->format('Y-m-d H:i:s')` -- Mengembalikan cap masa semasa dalam format yang mudah dibaca.
- `$next($request)` -- Meneruskan permintaan ke lapisan seterusnya (middleware lain atau pengawal). Ini adalah corak "before middleware" di mana log ditulis sebelum permintaan diproses.

### Konfigurasi Saluran Log

Buka `config/logging.php` dan tambah saluran `akses` di dalam tatasusunan `channels`:

```php
'akses' => [
    'driver' => 'single',
    'path' => storage_path('logs/akses.log'),
    'level' => 'info',
    'replace_placeholders' => true,
],
```

Saluran ini menggunakan pemacu `single` yang menulis ke satu fail sahaja: `storage/logs/akses.log`. Fail ini akan dijana secara automatik apabila log pertama ditulis.

---

## Langkah 5: Cipta Middleware SemakWaktuPejabat

Middleware ini akan menyekat akses ke bahagian tertentu jika permintaan dibuat di luar waktu pejabat. Ini adalah contoh "middleware pengesahan syarat" -- jika syarat tidak dipenuhi, pengguna akan disekat.

### Arahan Artisan

```bash
php artisan make:middleware SemakWaktuPejabat
```

### Fail Middleware

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

- `now()->dayOfWeek` -- Mengembalikan hari dalam minggu sebagai nombor (0 = Ahad, 1 = Isnin, ..., 5 = Jumaat, 6 = Sabtu). Fungsi `now()` menggunakan Carbon (pustaka tarikh/masa Laravel).
- `now()->hour` -- Mengembalikan jam semasa dalam format 24 jam (0-23).
- **Logik semakan:**
  - `$hariBekerja` -- `true` jika hari semasa adalah Isnin (1) hingga Jumaat (5).
  - `$waktuPejabat` -- `true` jika jam semasa antara 8:00 (8) hingga 4:59 petang (< 17).
  - Jika salah satu syarat tidak dipenuhi, halaman ralat `errors.luar-waktu` akan dipaparkan dengan kod status HTTP 403 (Forbidden).
- `response()->view('errors.luar-waktu', [], 403)` -- Mengembalikan paparan ralat tanpa meneruskan permintaan ke pengawal. Kod 403 bermaksud "Akses Ditolak".

> **Nota untuk bengkel:** Middleware ini tidak digunakan secara aktif dalam laluan contoh hari ini (untuk memudahkan pengujian). Ia didaftarkan sebagai alias `'waktu.pejabat'` supaya boleh digunakan apabila diperlukan. Untuk mengujinya, tambah middleware ke mana-mana laluan: `->middleware('waktu.pejabat')`.

---

## Langkah 6: Daftar Middleware dalam bootstrap/app.php

Dalam Laravel 11+, middleware didaftarkan di dalam `bootstrap/app.php` menggunakan alias. Ini berbeza daripada versi Laravel sebelumnya yang menggunakan `app/Http/Kernel.php`. Alias membolehkan kita merujuk middleware dengan nama pendek dalam definisi laluan.

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

- **`->withRouting(...)`** -- Mengkonfigurasi fail laluan untuk web, console, dan health check.
- **`->withMiddleware(...)`** -- Bahagian pendaftaran middleware. Kita mendaftarkan dua alias:
  - `'waktu.pejabat'` -- Alias untuk `SemakWaktuPejabat`. Digunakan sebagai `->middleware('waktu.pejabat')` dalam laluan.
  - `'log.akses'` -- Alias untuk `LogAkses`. Digunakan sebagai `->middleware('log.akses')` dalam laluan.
- **`->withExceptions(...)`** -- Tempat untuk konfigurasi pengendalian pengecualian (exception handling). Dibiarkan kosong buat masa ini.
- Selepas pendaftaran ini, kita boleh merujuk middleware dengan nama pendek dan bukannya nama kelas penuh (contoh: `'log.akses'` berbanding `\App\Http\Middleware\LogAkses::class`).

---

## Langkah 7: Cipta SemakController

Pengawal ini memaparkan halaman semakan sistem -- maklumat versi PHP, versi Laravel, status sambungan pangkalan data, dan pelayan web. Halaman ini berguna untuk pengesahan bahawa persekitaran pembangunan berfungsi dengan betul.

### Arahan Artisan

```bash
php artisan make:controller SemakController
```

### Fail Pengawal

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

- **`PHP_VERSION`** -- Pemalar PHP terbina dalam yang mengembalikan versi PHP yang sedang berjalan (contoh: `8.3.0`).
- **`app()->version()`** -- Mengembalikan versi Laravel (contoh: `11.0.0`).
- **`$_SERVER['SERVER_SOFTWARE']`** -- Mengembalikan maklumat pelayan web. Pada Laragon, ini biasanya `Apache/2.4.x`. Jika menggunakan `php artisan serve`, ia akan menjadi `PHP Built-in Server`.
- **`semakPangkalanData()`** -- Kaedah persendirian (private) yang cuba menyambung ke pangkalan data:
  - `DB::connection()->getPdo()` -- Cuba mendapatkan objek PDO daripada sambungan pangkalan data semasa. Jika berjaya, pangkalan data bersambung.
  - `config('database.default')` -- Mendapatkan nama pemacu pangkalan data lalai daripada konfigurasi (biasanya `mysql`).
  - `config('database.connections.mysql.database')` -- Mendapatkan nama pangkalan data (contoh: `zakat_kedah`).
  - Jika sambungan gagal, pengecualian ditangkap dan mesej ralat dikembalikan.

---

## Langkah 8: Cipta MaklumatController

Pengawal ini memaparkan senarai semua laluan yang didaftarkan dalam aplikasi. Ini berguna untuk rujukan semasa pembangunan -- anda boleh melihat semua URL yang tersedia, kaedah HTTP, dan pengawal yang bertanggungjawab.

### Arahan Artisan

```bash
php artisan make:controller MaklumatController
```

### Fail Pengawal

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

- `Route::getRoutes()` -- Mengembalikan koleksi semua laluan yang didaftarkan dalam aplikasi. Ini termasuk laluan web, laluan API, dan laluan yang dijana oleh pakej.
- `collect(...)` -- Membungkus hasil dalam koleksi Laravel supaya kita boleh menggunakan kaedah `map()`.
- `$route->getName()` -- Nama laluan (contoh: `pembayar.index`). Jika tiada nama, `null` dikembalikan dan digantikan dengan `'-'`.
- `$route->methods()` -- Mengembalikan tatasusunan kaedah HTTP (contoh: `['GET', 'HEAD']`).
- `$route->uri()` -- URI laluan (contoh: `pembayar/{pembayar}`).
- `$route->getActionName()` -- Nama pengawal dan kaedah penuh (contoh: `App\Http\Controllers\PembayarController@index`).

---

## Langkah 9: Tetapkan Laluan

Fail laluan menentukan semua URL yang boleh diakses dalam aplikasi. Setiap laluan memetakan URL kepada pengawal atau closure yang akan memproses permintaan.

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

- **Baris 9:** `Route::get('/', fn() => redirect()->route('pembayar.index'))` -- Halaman utama (`/`) mengalihkan pengguna terus ke senarai pembayar. `fn()` adalah arrow function PHP 7.4+.

- **Baris 12:** `Route::get('/semak', ...)` -- Laluan `/semak` dipetakan ke `SemakController@index`. `->name('semak')` memberi nama supaya boleh dirujuk dengan `route('semak')` dalam Blade.

- **Baris 15:** `Route::get('/maklumat/laluan', ...)` -- Laluan `/maklumat/laluan` dipetakan ke `MaklumatController@laluan`.

- **Baris 18-20:** `Route::middleware(['log.akses'])->group(function () {...})` -- **Kumpulan laluan dengan middleware.** Semua laluan dalam kumpulan ini akan melalui middleware `LogAkses`. `Route::resource('pembayar', PembayarController::class)` menjana 7 laluan CRUD secara automatik.

- **Baris 23-25:** `Route::prefix('admin')->name('admin.')->middleware(['log.akses'])->group(...)` -- **Kumpulan laluan dengan prefix dan nama.** Ciri-ciri:
  - `prefix('admin')` -- Menambah awalan `/admin` pada semua URI dalam kumpulan. Jadi `'/dashboard'` menjadi `/admin/dashboard`.
  - `name('admin.')` -- Menambah awalan `admin.` pada semua nama laluan. Jadi `'dashboard'` menjadi `admin.dashboard`.
  - `middleware(['log.akses'])` -- Menambah middleware log akses pada semua laluan dalam kumpulan.
  - Dashboard pentadbir menggunakan closure `fn() => view('admin.dashboard')` kerana ia hanya memaparkan paparan tanpa logik tambahan.

### Jadual Laluan Lengkap

| Kaedah | URI | Nama | Pengawal | Middleware |
|--------|-----|------|----------|------------|
| GET | `/` | - | Alih ke pembayar.index | - |
| GET | `/pembayar` | pembayar.index | PembayarController@index | log.akses |
| GET | `/pembayar/create` | pembayar.create | PembayarController@create | log.akses |
| POST | `/pembayar` | pembayar.store | PembayarController@store | log.akses |
| GET | `/pembayar/{pembayar}` | pembayar.show | PembayarController@show | log.akses |
| GET | `/pembayar/{pembayar}/edit` | pembayar.edit | PembayarController@edit | log.akses |
| PUT/PATCH | `/pembayar/{pembayar}` | pembayar.update | PembayarController@update | log.akses |
| DELETE | `/pembayar/{pembayar}` | pembayar.destroy | PembayarController@destroy | log.akses |
| GET | `/semak` | semak | SemakController@index | - |
| GET | `/maklumat/laluan` | maklumat.laluan | MaklumatController@laluan | - |
| GET | `/admin/dashboard` | admin.dashboard | Closure (view) | log.akses |

---

## Langkah 10: Cipta Layout Blade

Susun atur (layout) adalah kerangka HTML yang dikongsi oleh semua halaman. Ia mengandungi elemen yang sama di setiap halaman: bar navigasi, mesej kilat, dan kaki halaman. Setiap halaman hanya perlu mengisi bahagian `@yield('content')`.

### Cipta Direktori

```bash
mkdir -p resources/views/layouts
```

### Fail Layout

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

**Penerangan terperinci:**

- **`lang="ms"`** -- Menetapkan bahasa halaman sebagai Bahasa Melayu.
- **`{{ csrf_token() }}`** -- Token CSRF diletakkan dalam meta tag supaya boleh digunakan oleh permintaan AJAX. Setiap borang POST juga memerlukan `@csrf`.
- **`@yield('title', 'Sistem Zakat Kedah')`** -- Arahan Blade yang membenarkan halaman anak menentukan tajuk. Jika tidak ditetapkan, nilai lalai `'Sistem Zakat Kedah'` digunakan. Halaman anak menggunakan `@section('title', 'Tajuk Saya')` untuk menetapkan tajuk.
- **Tailwind CSS CDN** -- `<script src="https://cdn.tailwindcss.com"></script>` memuatkan Tailwind CSS untuk penggayaan. Untuk produksi, gunakan PostCSS/Vite.
- **Bar navigasi:**
  - Latar belakang hijau zamrud (`bg-emerald-700`) dengan tiga pautan: Pembayar, Maklumat Laluan, dan Semak Sistem.
  - `request()->routeIs('pembayar.*')` -- Menyemak sama ada laluan semasa sepadan dengan corak. Jika ya, pautan diberikan latar belakang gelap (`bg-emerald-900 text-white`) untuk menunjukkan halaman aktif. Jika tidak, pautan mempunyai warna cerah dengan kesan hover.
- **Mesej kilat:**
  - `session('success')` -- Memaparkan mesej kejayaan selepas operasi CRUD (contoh: "Pembayar berjaya didaftarkan."). Ditunjukkan dalam kotak hijau.
  - `session('error')` -- Memaparkan mesej ralat. Ditunjukkan dalam kotak merah.
  - Mesej ini hilang selepas satu kali muat semula halaman (flash session).
- **`@yield('content')`** -- Tempat di mana kandungan setiap halaman anak akan dimasukkan. Halaman anak menggunakan `@section('content') ... @endsection`.
- **Kaki halaman** -- Ditunjukkan di bahagian bawah setiap halaman.
- **`flex flex-col` dan `flex-1`** -- Menggunakan Flexbox untuk memastikan kaki halaman sentiasa di bahagian bawah, walaupun kandungan halaman pendek.

---

## Langkah 11: Cipta Paparan CRUD Pembayar

Cipta empat paparan Blade untuk operasi CRUD Pembayar: senarai (index), daftar baru (create), butiran (show), dan kemaskini (edit).

### Cipta Direktori

```bash
mkdir -p resources/views/pembayar
```

### 11a. Senarai Pembayar (index.blade.php)

Paparan ini memaparkan senarai pembayar dalam bentuk jadual dengan fungsi carian dan paginasi.

**Fail:** `resources/views/pembayar/index.blade.php`

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

**Penerangan terperinci:**

- **Borang carian:**
  - Menghantar permintaan GET ke `pembayar.index` dengan parameter query `?carian=...`.
  - `value="{{ $carian ?? '' }}"` -- Memaparkan teks carian sebelumnya dalam kotak input.
  - Butang "Padam Carian" hanya muncul jika ada carian aktif (`@if ($carian)`).

- **Jadual:**
  - `@forelse ... @empty ... @endforelse` -- Gabungan `@foreach` dan `@if` kosong. Jika tiada rekod, bahagian `@empty` dipaparkan.
  - `$pembayars->firstItem() + $index` -- Mengira nombor baris yang betul merentas halaman paginasi.
  - `$pembayar->ic_format` -- Menggunakan aksesor `getIcFormatAttribute()` untuk memaparkan no IC berformat.
  - `$pembayar->email ?? '-'` -- Operator null coalescing; papar `'-'` jika e-mel tiada.
  - `$pembayar->pendapatan_format` -- Menggunakan aksesor `getPendapatanFormatAttribute()` untuk format RM.

- **Pautan tindakan:**
  - **Lihat** -- Pautan ke halaman butiran (`pembayar.show`).
  - **Kemaskini** -- Pautan ke halaman kemaskini (`pembayar.edit`).
  - **Padam** -- Borang POST dengan `@method('DELETE')` kerana HTML tidak menyokong kaedah DELETE secara asli. `@csrf` menambah token CSRF untuk perlindungan. `onsubmit="return confirm(...)"` memaparkan dialog pengesahan sebelum pemadaman.

- **Paginasi:**
  - `$pembayars->hasPages()` -- Hanya papar pautan paginasi jika ada lebih daripada satu halaman.
  - `$pembayars->links()` -- Menjana pautan navigasi halaman (Sebelumnya, 1, 2, 3, Seterusnya).
  - Baris kiraan menunjukkan "Menunjukkan 1 hingga 10 daripada 25 rekod".

---

### 11b. Daftar Pembayar Baru (create.blade.php)

Paparan ini memaparkan borang untuk mendaftarkan pembayar baru.

**Fail:** `resources/views/pembayar/create.blade.php`

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

**Penerangan terperinci:**

- **Breadcrumb** -- Navigasi serbuk roti yang menunjukkan kedudukan halaman semasa: Senarai Pembayar > Daftar Baru.

- **`@csrf`** -- Arahan Blade yang menjana medan tersembunyi (hidden field) berisi token CSRF. Wajib untuk semua borang POST/PUT/DELETE bagi melindungi daripada serangan Cross-Site Request Forgery.

- **`old('nama')`** -- Fungsi helper Laravel yang mengembalikan nilai input sebelumnya jika pengesahan gagal. Ini membolehkan pengguna tidak perlu mengisi semula borang dari awal.

- **`$errors->has('nama')`** -- Menyemak sama ada terdapat ralat pengesahan untuk medan tertentu. Jika ada, sempadan input bertukar merah (`border-red-500`). Jika tiada, ia kekal kelabu (`border-gray-300`).

- **`@error('nama') ... @enderror`** -- Arahan Blade yang memaparkan mesej ralat pengesahan. Pembolehubah `$message` mengandungi mesej ralat yang ditetapkan dalam pengawal.

- **`<span class="text-red-500">*</span>`** -- Tanda bintang merah menunjukkan medan wajib diisi.

- **Grid responsif** -- `grid grid-cols-1 md:grid-cols-2` memaparkan satu lajur pada skrin kecil dan dua lajur pada skrin sederhana ke atas. `md:col-span-2` memaksa medan alamat mengambil lebar penuh.

- **Input pendapatan** -- `type="number"` dengan `step="0.01"` membenarkan nilai perpuluhan dan `min="0"` menghalang nilai negatif.

---

### 11c. Butiran Pembayar (show.blade.php)

Paparan ini memaparkan maklumat lengkap seorang pembayar.

**Fail:** `resources/views/pembayar/show.blade.php`

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
        </div>
    </div>
@endsection
```

**Penerangan terperinci:**

- **`@section('title', $pembayar->nama)`** -- Tajuk halaman ditetapkan kepada nama pembayar supaya tab pelayar menunjukkan nama pembayar.

- **Butang tindakan:**
  - **Kemaskini** (biru) -- Pautan ke halaman kemaskini.
  - **Padam** (merah) -- Borang DELETE dengan dialog pengesahan. `@method('DELETE')` menjana medan tersembunyi `_method=DELETE` kerana HTML hanya menyokong GET dan POST.
  - **Kembali** (kelabu) -- Kembali ke senarai pembayar.

- **Paparan maklumat:**
  - `$pembayar->ic_format` -- Menggunakan aksesor untuk memaparkan no IC berformat (contoh: `850101-14-5678`).
  - `$pembayar->pendapatan_format` -- Menggunakan aksesor untuk memaparkan pendapatan berformat (contoh: `RM 4,500.00`).
  - `$pembayar->email ?? '-'` dan `$pembayar->pekerjaan ?? '-'` -- Papar sengkang jika medan pilihan tiada nilai.
  - `$pembayar->created_at->format('d/m/Y H:i')` -- Memformat tarikh menggunakan Carbon. `created_at` dan `updated_at` secara automatik ditukar kepada objek Carbon oleh Eloquent.

---

### 11d. Kemaskini Pembayar (edit.blade.php)

Paparan ini memaparkan borang kemaskini dengan data sedia ada diisi terlebih dahulu.

**Fail:** `resources/views/pembayar/edit.blade.php`

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

**Penerangan terperinci:**

- **`@method('PUT')`** -- Arahan Blade yang menjana medan tersembunyi `_method=PUT`. HTML hanya menyokong GET dan POST, jadi Laravel menggunakan teknik "method spoofing" untuk menyimulasikan kaedah PUT/PATCH/DELETE.

- **`old('nama', $pembayar->nama)`** -- Fungsi `old()` dengan parameter kedua sebagai nilai lalai. Jika pengesahan gagal, nilai yang dimasukkan sebelumnya (`old('nama')`) dipaparkan. Jika bukan pengalihan pengesahan (iaitu muat pertama), nilai sedia ada daripada pangkalan data (`$pembayar->nama`) dipaparkan. Ini adalah perbezaan utama antara borang `create` dan `edit`.

- **Breadcrumb tiga peringkat** -- Senarai Pembayar > Nama Pembayar > Kemaskini. Ini membolehkan pengguna kembali ke halaman butiran atau senarai dengan mudah.

- **Butang Batal** -- Mengalih ke halaman butiran pembayar (`pembayar.show`) dan bukannya senarai, kerana pengguna berkemungkinan besar mahu kembali ke halaman yang sama.

---

## Langkah 12: Cipta Paparan Semak Sistem

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

- Empat kad grid memaparkan: versi PHP, versi Laravel, status pangkalan data, dan pelayan web.
- Arahan `@if` digunakan untuk memaparkan status sambungan pangkalan data -- hijau jika bersambung, merah jika gagal.
- Grid dua lajur pada skrin sederhana (`md:grid-cols-2`) dan satu lajur pada skrin kecil (`grid-cols-1`).

---

## Langkah 13: Cipta Paparan Senarai Laluan

Paparan ini memaparkan semua laluan berdaftar dalam bentuk jadual dengan warna mengikut kaedah HTTP.

### Cipta Direktori

```bash
mkdir -p resources/views/maklumat
```

### Fail Paparan

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
- Kaedah HTTP diwarnakan mengikut jenis menggunakan lencana (badge):
  - **GET** -- Hijau (`bg-emerald-100 text-emerald-800`)
  - **POST** -- Biru (`bg-blue-100 text-blue-800`)
  - **PUT/PATCH** -- Kuning emas (`bg-amber-100 text-amber-800`)
  - **DELETE** -- Merah (`bg-red-100 text-red-800`)
  - **Lain-lain** -- Kelabu (`bg-gray-100 text-gray-800`)
- `match()` adalah ungkapan padanan PHP 8.0+ yang lebih ringkas daripada `switch-case`. Ia mengembalikan nilai yang sepadan dengan kunci yang diberikan.
- `@php ... @endphp` membenarkan kod PHP mentah dalam paparan Blade. Berguna untuk logik paparan kecil.
- Jumlah laluan dipaparkan di bawah jadual.

---

## Langkah 14: Cipta Paparan Admin Dashboard

Dashboard pentadbir memaparkan statistik ringkas. Pada Hari 2 ini, hanya kiraan pembayar yang aktif. Statistik untuk Jenis Zakat dan Pembayaran akan ditambah pada Hari 3.

### Cipta Direktori

```bash
mkdir -p resources/views/admin
```

### Fail Paparan

**Fail:** `resources/views/admin/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Pentadbir</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Jumlah Pembayar --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-emerald-500">
            <p class="text-sm text-gray-500 mb-1">Jumlah Pembayar</p>
            <p class="text-3xl font-bold text-emerald-700">{{ \App\Models\Pembayar::count() }}</p>
            <p class="text-sm text-gray-400 mt-2">Pembayar zakat berdaftar</p>
        </div>

        {{-- Placeholder --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500 mb-1">Jenis Zakat</p>
            <p class="text-3xl font-bold text-blue-700">-</p>
            <p class="text-sm text-gray-400 mt-2">Akan ditambah pada Hari 3</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-amber-500">
            <p class="text-sm text-gray-500 mb-1">Jumlah Pembayaran</p>
            <p class="text-3xl font-bold text-amber-700">-</p>
            <p class="text-sm text-gray-400 mt-2">Akan ditambah pada Hari 3</p>
        </div>
    </div>
@endsection
```

**Penerangan:**

- `\App\Models\Pembayar::count()` -- Mengira jumlah rekod pembayar secara langsung dalam paparan. Dalam aplikasi sebenar, ini biasanya dilakukan dalam pengawal dan dihantar melalui `compact()`.
- Tiga kad statistik menggunakan `border-l-4` untuk garis sempadan kiri berwarna -- hijau untuk pembayar, biru untuk jenis zakat, dan kuning emas untuk pembayaran.
- Dua kad terakhir menunjukkan placeholder (`-`) kerana modul tersebut belum dibina. Ini menunjukkan amalan "progressive enhancement" -- membina antara muka dahulu, isi data kemudian.

---

## Langkah 15: Cipta Halaman Ralat

Cipta tiga halaman ralat tersuai. Halaman ralat menggunakan susun atur kendiri (tidak `@extends('layouts.app')`) supaya ia boleh dipaparkan walaupun susun atur utama bermasalah.

### Cipta Direktori

```bash
mkdir -p resources/views/errors
```

### 15a. Halaman Luar Waktu Pejabat

Halaman ini dipaparkan apabila middleware `SemakWaktuPejabat` menyekat akses kerana permintaan dibuat di luar waktu pejabat.

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

**Penerangan:**

- Halaman ini menggunakan susun atur kendiri dengan HTML penuh. Ia tidak bergantung kepada `layouts.app` kerana halaman ralat perlu boleh dipaparkan secara bebas.
- `&#128336;` adalah entiti HTML untuk ikon jam (mewakili "luar waktu").
- Mesej memaklumkan pengguna tentang waktu pejabat: Isnin hingga Jumaat, 8:00 pagi hingga 5:00 petang.
- Latar belakang hijau muda (`bg-emerald-50`) memberikan reka bentuk yang konsisten dengan tema aplikasi.

### 15b. Halaman 404 (Tidak Dijumpai)

Halaman ini dipaparkan apabila pengguna mengakses URL yang tidak wujud.

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

**Penerangan:**

- Nombor `404` dipaparkan dalam saiz besar dengan warna pudar (`text-emerald-200`) sebagai elemen reka bentuk.
- Laravel secara automatik memaparkan fail `resources/views/errors/404.blade.php` apabila pengecualian `NotFoundHttpException` berlaku (URL tidak dijumpai).
- Pautan "Kembali ke Laman Utama" mengalihkan pengguna ke halaman utama (`/`).

### 15c. Halaman 403 (Akses Ditolak)

Halaman ini dipaparkan apabila pengguna tidak mempunyai kebenaran untuk mengakses halaman.

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

- Serupa dengan halaman 404 tetapi dengan nombor `403` dan warna merah pudar (`text-red-200`).
- Laravel secara automatik memaparkan fail ini apabila pengecualian `AccessDeniedHttpException` berlaku.
- Digunakan apabila pengguna cuba mengakses sumber yang memerlukan kebenaran yang tidak dimilikinya.

---

## Langkah 16: Jalankan & Uji

Sekarang kita akan menjalankan migrasi, memasukkan data contoh, dan menguji semua ciri yang telah dibina.

### 16a. Jalankan Migrasi

```bash
php artisan migrate
```

Arahan ini akan mencipta semua jadual dalam pangkalan data berdasarkan fail migrasi. Anda sepatutnya melihat output seperti:

```
INFO  Running migrations.

2024_01_01_000001_create_pembayars_table ........... DONE
```

### 16b. Masukkan Data Contoh

```bash
php artisan db:seed
```

Arahan ini akan menjalankan `DatabaseSeeder` yang seterusnya memanggil `PembayarSeeder` untuk memasukkan 10 rekod pembayar contoh.

> **Nota:** Jika anda perlu mengulang migrasi dan seeder dari awal, gunakan:
> ```bash
> php artisan migrate:fresh --seed
> ```
> `migrate:fresh` akan memadam semua jadual dan mencipta semula, kemudian `--seed` menjalankan seeder.

### 16c. Mulakan Pelayan Pembangunan

```bash
php artisan serve
```

Pelayan akan bermula di `http://localhost:8000`. Atau jika menggunakan Laragon, akses melalui `http://sistem-zakat.test`.

### 16d. Uji Setiap URL

Buka pelayar web dan uji URL berikut satu per satu:

| # | URL | Jangkaan |
|---|-----|----------|
| 1 | `http://localhost:8000/` | Dialihkan ke senarai pembayar |
| 2 | `http://localhost:8000/pembayar` | Senarai 10 pembayar dengan jadual, carian, dan paginasi |
| 3 | `http://localhost:8000/pembayar/create` | Borang daftar pembayar baru dengan 7 medan |
| 4 | `http://localhost:8000/pembayar/1` | Butiran pembayar pertama (Ahmad bin Abdullah) |
| 5 | `http://localhost:8000/pembayar/1/edit` | Borang kemaskini dengan data sedia ada diisi |
| 6 | `http://localhost:8000/semak` | Halaman semakan sistem (versi PHP, Laravel, pangkalan data) |
| 7 | `http://localhost:8000/maklumat/laluan` | Jadual semua laluan berdaftar dengan warna kaedah |
| 8 | `http://localhost:8000/admin/dashboard` | Dashboard pentadbir dengan kiraan pembayar |
| 9 | `http://localhost:8000/halaman-tiada` | Halaman ralat 404 tersuai |

### 16e. Uji Operasi CRUD

1. **Cipta:** Pergi ke `/pembayar/create`, isi borang, dan klik "Simpan". Pastikan mesej kejayaan muncul.
2. **Baca:** Klik "Lihat" pada mana-mana pembayar. Pastikan no IC berformat sengkang dan pendapatan berformat RM.
3. **Kemaskini:** Klik "Kemaskini", ubah data, dan klik "Kemaskini". Pastikan data dikemaskini.
4. **Padam:** Klik "Padam" dan sahkan. Pastikan pembayar dipadam dan mesej kejayaan muncul.

### 16f. Uji Carian

Pada halaman senarai pembayar (`/pembayar`):
1. Taip `Ahmad` dalam kotak carian dan klik "Cari". Hanya pembayar bernama Ahmad sepatutnya dipaparkan.
2. Taip `850101` dan cari. Pembayar dengan no IC bermula `850101` sepatutnya dijumpai.
3. Klik "Padam Carian" untuk melihat semula semua pembayar.

### 16g. Uji Pengesahan Borang

Cuba hantar borang pendaftaran baru dengan medan kosong:
1. Pergi ke `/pembayar/create`.
2. Klik "Simpan" tanpa mengisi apa-apa.
3. Mesej ralat sepatutnya muncul di bawah setiap medan wajib ("Sila masukkan nama penuh.", dll.).
4. Cuba masukkan no IC kurang daripada 12 digit -- mesej "No. KP mestilah 12 digit." sepatutnya muncul.

### 16h. Semak Fail Log

Selepas mengakses beberapa halaman Pembayar, semak fail log akses:

```bash
cat storage/logs/akses.log
```

Anda sepatutnya melihat entri log seperti ini:

```
[2024-01-15 10:30:45] local.INFO: Akses masuk {"kaedah":"GET","url":"http://localhost:8000/pembayar","ip":"127.0.0.1","masa":"2024-01-15 10:30:45"}
[2024-01-15 10:30:52] local.INFO: Akses masuk {"kaedah":"GET","url":"http://localhost:8000/pembayar/1","ip":"127.0.0.1","masa":"2024-01-15 10:30:52"}
```

Perhatikan bahawa hanya laluan yang menggunakan middleware `log.akses` yang direkodkan (laluan Pembayar dan Admin Dashboard). Laluan `/semak` dan `/maklumat/laluan` tidak direkodkan kerana ia tidak menggunakan middleware tersebut.

### 16i. Uji Skop dan Aksesor dalam Tinker

Laravel Tinker membolehkan anda menjalankan kod PHP secara interaktif:

```bash
php artisan tinker
```

Cuba arahan berikut dalam Tinker:

```php
// Uji skop carian
Pembayar::carian('Ahmad')->get();

// Uji aksesor ic_format
$p = Pembayar::first();
$p->ic_format;        // "850101-14-5678"
$p->pendapatan_format; // "RM 4,500.00"

// Kira jumlah pembayar
Pembayar::count(); // 10

// Cari pembayar tanpa e-mel
Pembayar::whereNull('email')->get();
```

### 16j. Senarai Laluan melalui Artisan

Anda juga boleh melihat senarai laluan melalui terminal:

```bash
php artisan route:list
```

Output akan menunjukkan semua laluan yang didaftarkan, termasuk kaedah HTTP, URI, nama, pengawal, dan middleware yang digunakan.

---

## Struktur Fail Akhir Hari 2

```
sistem-zakat/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   ├── MaklumatController.php       ← baharu
│   │   │   ├── PembayarController.php       ← baharu (7 kaedah CRUD)
│   │   │   └── SemakController.php          ← baharu
│   │   └── Middleware/
│   │       ├── LogAkses.php                 ← baharu
│   │       └── SemakWaktuPejabat.php        ← baharu
│   ├── Models/
│   │   ├── Pembayar.php                     ← baharu (skop + aksesor)
│   │   └── User.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php                              ← dikemaskini (alias middleware)
├── config/
│   └── logging.php                          ← dikemaskini (saluran 'akses')
├── database/
│   ├── migrations/
│   │   └── 2024_01_01_000001_create_pembayars_table.php  ← baharu
│   └── seeders/
│       ├── DatabaseSeeder.php               ← dikemaskini
│       └── PembayarSeeder.php               ← baharu (10 rekod)
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php                ← baharu (susun atur utama)
│       ├── pembayar/
│       │   ├── index.blade.php              ← baharu (senarai + carian)
│       │   ├── create.blade.php             ← baharu (borang daftar)
│       │   ├── show.blade.php               ← baharu (butiran)
│       │   └── edit.blade.php               ← baharu (borang kemaskini)
│       ├── maklumat/
│       │   └── laluan.blade.php             ← baharu (senarai laluan)
│       ├── admin/
│       │   └── dashboard.blade.php          ← baharu (dashboard)
│       ├── semak.blade.php                  ← baharu (semakan sistem)
│       └── errors/
│           ├── luar-waktu.blade.php         ← baharu (middleware)
│           ├── 404.blade.php                ← baharu (tidak dijumpai)
│           └── 403.blade.php                ← baharu (akses ditolak)
├── routes/
│   └── web.php                              ← dikemaskini (semua laluan)
└── storage/
    └── logs/
        └── akses.log                        ← dijana automatik oleh middleware
```

---

## Rumusan Hari 2

Pada hari ini, kita telah belajar dan membina komponen-komponen berikut:

1. **Model & Migrasi Pembayar** -- Mencipta model `Pembayar` dengan migrasi untuk jadual `pembayars`. Jadual ini mengandungi medan nama, no IC (unik), alamat, no telefon, e-mel, pekerjaan, dan pendapatan bulanan.

2. **Skop & Aksesor** -- Menambah `scopeCarian` untuk carian mengikut nama atau no IC, aksesor `ic_format` untuk memformat no IC dengan sengkang, dan aksesor `pendapatan_format` untuk memformat pendapatan dengan simbol RM.

3. **Pengawal Sumber (Resource Controller)** -- Mencipta `PembayarController` dengan 7 kaedah CRUD lengkap: `index` (senarai dengan carian dan paginasi), `create` (borang baru), `store` (simpan dengan pengesahan), `show` (butiran), `edit` (borang kemaskini), `update` (kemaskini dengan pengesahan), dan `destroy` (padam dengan pengesahan).

4. **Seeder** -- Mencipta `PembayarSeeder` dengan 10 rekod contoh untuk pengujian, termasuk kes ujian untuk medan pilihan yang `null`.

5. **Middleware LogAkses** -- Merekod setiap permintaan HTTP ke fail log berasingan (`storage/logs/akses.log`) termasuk kaedah, URL, IP, dan masa.

6. **Middleware SemakWaktuPejabat** -- Menyekat akses di luar waktu pejabat (Isnin-Jumaat, 8:00 pagi - 5:00 petang) dengan halaman ralat tersuai.

7. **Pendaftaran Middleware** -- Mendaftarkan alias middleware dalam `bootstrap/app.php` menggunakan cara Laravel 11+ (`$middleware->alias([...])`) sebagai pengganti `app/Http/Kernel.php`.

8. **Pengawal Tambahan** -- `SemakController` untuk halaman semakan sistem (versi PHP, Laravel, status pangkalan data) dan `MaklumatController` untuk memaparkan senarai laluan berdaftar.

9. **Kumpulan Laluan** -- Menggunakan `Route::middleware()` untuk mengelompokkan laluan dengan middleware, `Route::prefix()` untuk menambah awalan URL, `Route::name()` untuk menambah awalan nama, dan `Route::resource()` untuk menjana 7 laluan CRUD secara automatik.

10. **Susun Atur Blade (Layout)** -- Mencipta susun atur utama `layouts/app.blade.php` dengan bar navigasi, mesej kilat, dan kaki halaman. Menggunakan `@yield` dan `@section` untuk warisan templat.

11. **Paparan CRUD Lengkap** -- Empat paparan Blade untuk operasi CRUD: senarai dengan carian dan paginasi, borang pendaftaran dengan pengesahan, halaman butiran dengan aksesor, dan borang kemaskini dengan data pra-isian.

12. **Halaman Ralat Tersuai** -- Tiga halaman ralat: luar waktu pejabat (digunakan oleh middleware), 404 (halaman tidak dijumpai), dan 403 (akses ditolak).

Esok pada **Hari 3**, kita akan mendalami Blade templat, hubungan Eloquent (relationships), migrasi untuk `JenisZakat` dan `Pembayaran`, serta pengesahan borang (validation) yang lebih mendalam.
