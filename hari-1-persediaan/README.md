# Hari 1: Persediaan Persekitaran & Projek Sistem Zakat

**Kursus Laravel 4 Hari | Pusat Zakat Negeri Kedah**

---

## Objektif Pembelajaran

Pada akhir Hari 1, anda akan dapat:

- Memahami apa itu Laravel dan kelebihannya
- Memasang semua perisian yang diperlukan pada Windows
- Mengkonfigurasi Laragon, MySQL, dan VS Code
- Mencipta projek Laravel `sistem-zakat`
- Mencipta pangkalan data `zakat_kedah`
- Memahami struktur direktori Laravel

---

## Pengenalan Projek: Sistem Pengurusan Zakat

Sepanjang 4 hari kursus ini, kita akan membina **Sistem Pengurusan Zakat** untuk **Pusat Zakat Negeri Kedah**. Sistem ini akan:

- **Mendaftar pembayar zakat** — Simpan maklumat pembayar (nama, IC, alamat, pendapatan)
- **Merekod pembayaran** — Rekod setiap transaksi zakat dengan resit
- **Mengurus jenis zakat** — Zakat Fitrah, Zakat Pendapatan, Zakat Perniagaan, dll.
- **Menjana laporan** — Jumlah kutipan mengikut jenis dan tempoh
- **Kawalan akses** — Log masuk kakitangan Pusat Zakat sahaja

### Struktur Data Sistem

```
┌─────────────────┐       ┌─────────────────┐
│   Pembayar      │       │   Jenis Zakat   │
├─────────────────┤       ├─────────────────┤
│ nama            │       │ nama            │
│ no_ic           │       │ kadar           │
│ alamat          │  1──M │ penerangan      │
│ no_tel          │◄──────│ is_aktif        │
│ email           │       └─────────────────┘
│ pekerjaan       │              │
│ pendapatan      │              │ 1
└────────┬────────┘              │
         │ 1                     │
         │                       │
         │ M          M          │
    ┌────┴───────────────────────┴─┐
    │       Pembayaran             │
    ├──────────────────────────────┤
    │ pembayar_id (FK)             │
    │ jenis_zakat_id (FK)          │
    │ jumlah                       │
    │ tarikh_bayar                 │
    │ cara_bayar                   │
    │ no_resit                     │
    │ status                       │
    └──────────────────────────────┘
```

---

## Modul 1.1: Pengenalan kepada Laravel

### Apa itu Laravel?

Laravel ialah rangka kerja (framework) PHP sumber terbuka yang direka untuk memudahkan pembangunan aplikasi web. Ia mengikuti corak seni bina **Model-View-Controller (MVC)** yang memisahkan logik perniagaan, data, dan paparan.

### Kenapa Laravel Sesuai untuk Sistem Zakat?

- **Sintaks Elegan** — Kod yang bersih, sesuai untuk sistem pengurusan
- **Eloquent ORM** — Mudah urus data pembayar, pembayaran, dan jenis zakat
- **Blade Templating** — Bina borang dan laporan dengan pantas
- **Keselamatan Terbina Dalam** — Penting untuk data peribadi pembayar (CSRF, SQL injection)
- **Authentication** — Kawalan akses kakitangan terbina dalam
- **Artisan CLI** — Menjana controller, model, migration dengan satu arahan

### Corak MVC dalam Konteks Sistem Zakat

```
Kakitangan Pusat Zakat (Pelayar)
    │
    ▼
  Routes ──► PembayarController ──► Pembayar (Model/DB)
                    │
                    ▼
              pembayar/index.blade.php
                    │
                    ▼
            Senarai Pembayar Dipaparkan
```

- **Model** — `Pembayar.php`, `JenisZakat.php`, `Pembayaran.php` (berinteraksi dengan jadual MySQL)
- **View** — Borang pendaftaran, senarai pembayar, resit pembayaran (fail `.blade.php`)
- **Controller** — `PembayarController.php` mengendalikan operasi CRUD

---

## Modul 1.2: Keperluan Sistem & Perisian

### Senarai Perisian yang Diperlukan

| # | Perisian | Versi | Tujuan | Saiz |
|---|----------|-------|--------|------|
| 1 | **Laragon** | v6.0+ | Persekitaran pembangunan (Apache, PHP, MySQL) | ~80MB |
| 2 | **PHP** | 8.1+ | Bahasa pengaturcaraan (dipasang melalui Laragon) | — |
| 3 | **Composer** | v2.x | Pengurus kebergantungan PHP (dipasang melalui Laragon) | — |
| 4 | **MySQL** | 8.0+ | Pangkalan data hubungan (dipasang melalui Laragon) | — |
| 5 | **VS Code** | Terkini | Editor kod utama | ~90MB |
| 6 | **Node.js** | v18+ | Pengurus aset frontend (Vite) | ~30MB |
| 7 | **Git** | v2.x | Kawalan versi (dipasang melalui Laragon) | — |
| 8 | **Chrome** | Terkini | Pelayar web untuk ujian & DevTools | — |

### Kenapa Laragon dan Bukan XAMPP/WAMP?

| Ciri | Laragon | XAMPP | WAMP |
|------|---------|-------|------|
| Saiz | ~80MB | ~150MB | ~120MB |
| Masa mula | 3 saat | 10-15 saat | 10-15 saat |
| Auto Virtual Host | Ya (projek.test) | Tidak | Tidak |
| Tukar versi PHP | Satu klik | Manual | Manual |
| Terminal terbina dalam | Ya (dengan PATH) | Tidak | Tidak |
| Composer terbina dalam | Ya | Tidak | Tidak |
| Git terbina dalam | Ya | Tidak | Tidak |

---

## Modul 1.3: Pemasangan Perisian

> **PENTING:** Ikut langkah ini mengikut urutan. Jangan langkau mana-mana langkah.

### Langkah 1: Muat Turun dan Pasang Laragon

1. Buka pelayar dan pergi ke: **https://laragon.org/download/index.html**
2. Klik butang **"Download Laragon Full (64-bit)"**
3. Jalankan fail `.exe` yang dimuat turun
4. Dalam wizard pemasangan:
   - Pilih bahasa: **English**
   - Lokasi pemasangan: **`C:\laragon`** (biarkan lalai)
   - Pastikan kotak berikut ditanda:
     - ✅ Auto Virtual Hosts
     - ✅ Add Laragon to PATH
   - Klik **Install**
5. Tunggu sehingga pemasangan selesai
6. Klik **Finish**

**Pengesahan:**
```
Buka Laragon > Klik "Start All"
Buka pelayar > Pergi ke http://localhost
Anda sepatutnya nampak halaman Laragon
```

### Langkah 2: Muat Turun dan Pasang VS Code

1. Pergi ke: **https://code.visualstudio.com/download**
2. Klik **"Windows (User Installer)"**
3. Jalankan pemasang
4. Dalam wizard:
   - ✅ Tandakan "Add to PATH"
   - ✅ Tandakan "Register Code as an editor for supported file types"
   - Klik **Install**

### Langkah 3: Muat Turun dan Pasang Node.js

1. Pergi ke: **https://nodejs.org/**
2. Muat turun versi **LTS** (Long Term Support)
3. Jalankan pemasang dengan pilihan lalai
4. **Pengesahan** — Buka Command Prompt:
   ```
   node --version
   npm --version
   ```

### Langkah 4: Sahkan Semua Perisian

Buka **Terminal Laragon** (Klik kanan ikon Laragon > Terminal) dan jalankan:

```bash
# Sahkan PHP
php --version
# Jangkaan: PHP 8.x.x

# Sahkan Composer
composer --version
# Jangkaan: Composer version 2.x.x

# Sahkan Git
git --version
# Jangkaan: git version 2.x.x

# Sahkan Node.js
node --version
# Jangkaan: v18.x.x atau lebih tinggi

# Sahkan npm
npm --version
```

> **Penyelesaian Masalah:** Jika `composer` atau `git` tidak dikenali, tutup dan buka semula Terminal Laragon. Jika masih tidak berjaya, pastikan Laragon telah dimulakan ("Start All").

---

## Modul 1.4: Pasang Sambungan VS Code

Buka VS Code dan tekan `Ctrl+Shift+X` untuk membuka panel Extensions.

### Sambungan Wajib

1. **PHP Intelephense** — Autocomplete, diagnostik, dan go-to-definition untuk PHP
2. **Laravel Blade Snippets** — Penyerlahan sintaks untuk fail `.blade.php`
3. **Laravel Snippets** — Snippet pantas untuk Route, Controller, Model
4. **DotENV** — Penyerlahan sintaks untuk fail `.env`

### Sambungan Disyorkan

5. **PHP DocBlocker** — Jana dokumentasi PHPDoc secara automatik
6. **Thunder Client** — Klien API terbina dalam (alternatif Postman)
7. **MySQL (by Weijan Chen)** — Sambung ke MySQL dari VS Code
8. **GitLens** — Lihat sejarah Git dan blame

### Konfigurasi VS Code

Buka Settings (`Ctrl+,`) dan tambah tetapan berikut dalam `settings.json`:

```json
{
    "editor.tabSize": 4,
    "editor.formatOnSave": true,
    "files.associations": {
        "*.blade.php": "blade"
    },
    "emmet.includeLanguages": {
        "blade": "html"
    }
}
```

---

## Lab 1.1: Konfigurasi Laragon & MySQL

### Langkah 1: Mulakan Laragon

1. Buka aplikasi Laragon
2. Klik butang **"Start All"**
3. Pastikan kedua-dua **Apache** dan **MySQL** berstatus hijau (running)

### Langkah 2: Buka phpMyAdmin

1. Klik kanan ikon Laragon di system tray
2. Pilih **MySQL > phpMyAdmin**
3. Atau buka pelayar dan pergi ke: **http://localhost/phpmyadmin**
4. Log masuk dengan:
   - Username: **root**
   - Password: *(kosongkan)*

### Langkah 3: Cipta Pangkalan Data Zakat

1. Di phpMyAdmin, klik **"New"** di panel kiri
2. Masukkan nama pangkalan data: **`zakat_kedah`**
3. Pilih Collation: **`utf8mb4_unicode_ci`**
4. Klik **"Create"**

**Output yang dijangka:**
```
Pangkalan data "zakat_kedah" telah dicipta.
Anda akan nampak "zakat_kedah" dalam senarai di sebelah kiri.
```

> **Nota:** Kita namakan `zakat_kedah` kerana sistem ini dibangunkan khusus untuk Pusat Zakat Negeri Kedah.

---

## Lab 1.2: Cipta Projek Laravel — Sistem Zakat

### Langkah 1: Buka Terminal Laragon

1. Klik kanan ikon Laragon > **Terminal**
2. Terminal akan dibuka di folder `C:\laragon\www`

### Langkah 2: Cipta Projek

```bash
composer create-project laravel/laravel sistem-zakat
```

> **Nota:** Proses ini mengambil masa 2-5 minit bergantung pada kelajuan internet. Ia akan memuat turun semua kebergantungan Laravel.

**Output yang dijangka:**
```
Creating a "laravel/laravel" project at "./sistem-zakat"
Installing laravel/laravel (v11.x.x)
  - Downloading laravel/laravel (v11.x.x)
...
Application key set successfully.
```

### Langkah 3: Konfigurasi Fail `.env`

1. Buka folder projek dalam VS Code:
   ```bash
   cd sistem-zakat
   code .
   ```

2. Buka fail `.env` di root projek dan kemas kini tetapan:

```env
APP_NAME="Sistem Zakat Kedah"
APP_ENV=local
APP_KEY=base64:... (sudah dijana)
APP_DEBUG=true
APP_URL=http://sistem-zakat.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zakat_kedah
DB_USERNAME=root
DB_PASSWORD=
```

> **PENTING:** Pastikan `DB_DATABASE=zakat_kedah` sepadan dengan nama pangkalan data yang anda cipta dalam phpMyAdmin tadi.

### Langkah 4: Jalankan Pelayan

**Pilihan A — Menggunakan Artisan:**
```bash
php artisan serve
```
Buka pelayar: **http://127.0.0.1:8000**

**Pilihan B — Menggunakan Laragon (disyorkan):**

Laragon secara automatik mencipta virtual host. Buka pelayar dan pergi ke:
**http://sistem-zakat.test**

> **Penyelesaian Masalah:** Jika `sistem-zakat.test` tidak berfungsi:
> 1. Pastikan Laragon sedang berjalan
> 2. Klik kanan Laragon > Apache > Reload
> 3. Cuba buka `http://sistem-zakat.test` semula

**Output yang dijangka:**
```
Anda akan melihat halaman selamat datang Laravel
dengan teks "Laravel" dan butang navigasi.
```

### Langkah 5: Uji Sambungan Pangkalan Data

```bash
php artisan migrate
```

**Output yang dijangka:**
```
Migration table created successfully.
Running migrations...
2024_xx_xx_000000_create_users_table ........... DONE
2024_xx_xx_000001_create_password_reset_tokens_table ... DONE
2024_xx_xx_000002_create_sessions_table ........ DONE
2024_xx_xx_000003_create_cache_table ........... DONE
2024_xx_xx_000004_create_jobs_table ............ DONE
```

> Jika anda mendapat ralat sambungan, semak semula tetapan `DB_*` dalam fail `.env` dan pastikan MySQL sedang berjalan dalam Laragon.

---

## Modul 1.5: Struktur Direktori Laravel

Selepas mencipta projek, ini adalah struktur folder utama:

```
sistem-zakat/
├── app/                    # Logik teras aplikasi
│   ├── Http/
│   │   ├── Controllers/    # PembayarController, dll.
│   │   └── Middleware/     # Middleware
│   ├── Models/             # Pembayar.php, JenisZakat.php, Pembayaran.php
│   └── Providers/          # Service Providers
├── bootstrap/              # Fail permulaan rangka kerja
├── config/                 # Fail konfigurasi
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   └── ...
├── database/
│   ├── factories/          # Factory untuk data ujian
│   ├── migrations/         # Fail migrasi (create_pembayars_table, dll.)
│   └── seeders/            # Seeder untuk data awal (jenis zakat)
├── public/                 # Titik masuk utama
│   └── index.php           # Front controller
├── resources/
│   ├── css/                # Fail CSS sumber
│   ├── js/                 # Fail JavaScript sumber
│   └── views/              # Paparan Blade (.blade.php)
│       ├── layouts/        # Layout utama
│       ├── pembayar/       # Views untuk pembayar
│       ├── pembayaran/     # Views untuk pembayaran
│       └── jenis-zakat/    # Views untuk jenis zakat
├── routes/
│   ├── web.php             # Laluan untuk pelayar web
│   ├── api.php             # Laluan untuk API
│   └── console.php         # Arahan Artisan tersuai
├── storage/                # Log, cache, sesi, fail dimuat naik
├── tests/                  # Fail ujian
├── .env                    # Konfigurasi persekitaran (RAHSIA!)
├── .env.example            # Contoh fail .env
├── artisan                 # CLI Laravel
├── composer.json           # Kebergantungan PHP
├── package.json            # Kebergantungan Node.js
└── vite.config.js          # Konfigurasi Vite (aset frontend)
```

### Fail & Folder Paling Penting untuk Projek Zakat

| Fail/Folder | Kekerapan Guna | Tujuan dalam Sistem Zakat |
|-------------|----------------|---------------------------|
| `routes/web.php` | Sangat kerap | URL: /pembayar, /pembayaran, /jenis-zakat |
| `app/Http/Controllers/` | Sangat kerap | PembayarController, PembayaranController |
| `app/Models/` | Kerap | Pembayar.php, JenisZakat.php, Pembayaran.php |
| `resources/views/` | Sangat kerap | Borang, senarai, resit |
| `database/migrations/` | Kerap | Jadual pembayar, pembayaran, jenis_zakat |
| `.env` | Kadang-kadang | DB: zakat_kedah, APP_NAME |
| `config/` | Jarang | Tetapan rangka kerja |

---

## Lab 1.3: Meneroka Projek dalam VS Code

### Tugasan

1. Buka fail `routes/web.php` — ini adalah fail pertama yang Laravel baca apabila menerima permintaan
2. Buka fail `resources/views/welcome.blade.php` — ini adalah halaman selamat datang
3. Buka fail `.env` — ini mengandungi konfigurasi aplikasi anda
4. Buka Terminal dalam VS Code (`Ctrl+``) dan jalankan:
   ```bash
   php artisan route:list
   ```
   Ini memaparkan semua laluan yang telah didaftarkan.

### Cuba Ubah Halaman Selamat Datang

1. Buka `resources/views/welcome.blade.php`
2. Cari teks "Laravel" dan tukar kepada **"Sistem Pengurusan Zakat Kedah"**
3. Simpan fail (`Ctrl+S`)
4. Muat semula pelayar — anda akan nampak perubahan serta-merta!

### Cuba Tambah Route Pertama

Buka `routes/web.php` dan tambah di bawah route sedia ada:

```php
Route::get('/salam', function () {
    return 'Assalamualaikum! Selamat datang ke Sistem Zakat Kedah.';
});
```

Buka pelayar: **http://sistem-zakat.test/salam** — anda akan nampak teks tersebut!

---

## Ringkasan Hari 1

| Topik | Status |
|-------|--------|
| Memahami apa itu Laravel & MVC | ✅ |
| Memahami projek Sistem Zakat yang akan dibina | ✅ |
| Memasang Laragon (Apache + PHP + MySQL) | ✅ |
| Memasang VS Code + sambungan | ✅ |
| Memasang Node.js | ✅ |
| Mengkonfigurasi MySQL & phpMyAdmin | ✅ |
| Mencipta pangkalan data `zakat_kedah` | ✅ |
| Mencipta projek Laravel `sistem-zakat` | ✅ |
| Menjalankan migrasi pertama | ✅ |
| Memahami struktur direktori | ✅ |

## Persediaan untuk Hari 2

Pastikan sebelum pulang:
1. ✅ Laragon boleh dimulakan tanpa ralat
2. ✅ `http://sistem-zakat.test` memaparkan halaman Laravel
3. ✅ `php artisan migrate` berjaya tanpa ralat
4. ✅ VS Code boleh membuka folder projek `sistem-zakat`
5. ✅ Route `/salam` berfungsi di pelayar

> **Hari 2:** Kita akan mula membina **PembayarController** dengan semua operasi CRUD — senarai, daftar, kemaskini, dan padam pembayar zakat!
