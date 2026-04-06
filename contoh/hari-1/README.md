# Hari 1 — Persediaan & CRUD Asas

Projek contoh untuk **Hari 1** Kursus Laravel 4 Hari.
Sistem Pengurusan Zakat untuk Pusat Zakat Negeri Kedah.

## Apa Yang Dipelajari

- Pemasangan persekitaran pembangunan (Laragon, MySQL, VS Code)
- Mencipta projek Laravel baru
- Memahami struktur folder Laravel
- Mencipta pangkalan data MySQL dan konfigurasi `.env`
- Migrasi pangkalan data (migration)
- Model Eloquent asas
- Pengawal sumber (resource controller) dengan 7 kaedah CRUD
- Templat Blade dengan Tailwind CSS
- Pengesahan borang (validation) dengan mesej Bahasa Melayu

## Langkah Persediaan

### 1. Cipta Pangkalan Data

Buka terminal MySQL dan jalankan:

```sql
CREATE DATABASE zakat_kedah;
```

### 2. Salin Fail Persekitaran

```bash
cp .env.example .env
```

Kemudian kemaskini `.env` dengan tetapan berikut:

```
DB_CONNECTION=mysql
DB_DATABASE=zakat_kedah
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Pasang Kebergantungan

```bash
composer install
```

### 4. Jana Kunci Aplikasi

```bash
php artisan key:generate
```

### 5. Jalankan Migrasi

```bash
php artisan migrate
```

### 6. Isi Data Contoh

```bash
php artisan db:seed
```

### 7. Jalankan Pelayan

```bash
php artisan serve
```

Buka pelayar dan layari: [http://localhost:8000](http://localhost:8000)

## Struktur Fail Penting

```
app/
├── Http/Controllers/
│   └── PembayarController.php    ← Pengawal CRUD
├── Models/
│   └── Pembayar.php              ← Model Eloquent
database/
├── migrations/
│   └── 2024_01_01_000001_...     ← Migrasi jadual pembayars
├── seeders/
│   ├── DatabaseSeeder.php
│   └── PembayarSeeder.php        ← 10 rekod contoh
resources/views/
├── layouts/
│   └── app.blade.php             ← Templat induk
├── pembayar/
│   ├── index.blade.php           ← Senarai pembayar
│   ├── create.blade.php          ← Borang daftar baru
│   ├── show.blade.php            ← Paparan terperinci
│   └── edit.blade.php            ← Borang kemaskini
└── semak.blade.php               ← Semakan sistem
routes/
└── web.php                       ← Laluan aplikasi
```

## Laluan (Routes)

| Kaedah | URL                  | Fungsi                    |
|--------|----------------------|---------------------------|
| GET    | `/`                  | Hala ke senarai pembayar  |
| GET    | `/semak`             | Semakan sistem            |
| GET    | `/pembayar`          | Senarai pembayar          |
| GET    | `/pembayar/create`   | Borang daftar baru        |
| POST   | `/pembayar`          | Simpan pembayar baru      |
| GET    | `/pembayar/{id}`     | Lihat pembayar            |
| GET    | `/pembayar/{id}/edit`| Borang kemaskini          |
| PUT    | `/pembayar/{id}`     | Kemaskini pembayar        |
| DELETE | `/pembayar/{id}`     | Padam pembayar            |
