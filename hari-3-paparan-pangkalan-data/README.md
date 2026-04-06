# Kursus Laravel 4 Hari | Pusat Zakat Negeri Kedah

## Hari 3: Paparan, Pangkalan Data, dan Model

---

## Sinopsis Hari 3

Pada Hari 3, kita akan membina fondasi paparan pengguna dan pangkalan data untuk Sistem Pengurusan Zakat. Kami akan:

- Mempelajari **Enjin Templat Blade** untuk membuat halaman HTML dinamik
- Mereka layout utama untuk seluruh aplikasi
- Menggunakan **Eloquent ORM** untuk berinteraksi dengan pangkalan data
- Membuat **Model dan Migrasi** untuk tiga jadual utama
- Melaksanakan **Pengesahan Data** untuk keselamatan dan integriti
- Menulis **Controller** yang berfungsi dengan Model dan View
- Menyedia data awal dengan **Seeder**

Pada akhir hari ini, aplikasi anda akan mempunyai antara muka pengguna yang lengkap dan pangkalan data yang berfungsi.

---

## Modul 3.1: Enjin Templat Blade

### Apakah itu Blade?

**Blade** adalah enjin templat Laravel yang membenarkan anda menulis kod PHP dalam fail HTML dengan sintaks yang ringkas dan mudah dibaca. Blade diproses di sisi pelayan sebelum dihantar ke pelayar pengguna.

### Sintaks Asas Blade

#### 1. Mencetak Pembolehubah

```blade
{{-- Cetak nilai pembolehubah --}}
{{ $pembayar->nama }}

{{-- Cetak dengan nilai lalai jika kosong --}}
{{ $pembayar->nama ?? 'Tiada Nama' }}

{{-- Cetak dan elakkan HTML (selamat) --}}
{{ $pembayar->alamat }}

{{-- Cetak tanpa escape (jika percaya sumbernya) --}}
{!! $html_content !!}
```

#### 2. Pernyataan Bersyarat

```blade
{{-- Jika --}}
@if($pembayar->pendapatan_bulanan > 10000)
    <p>Pendapatan: Tinggi</p>
@elseif($pembayar->pendapatan_bulanan > 5000)
    <p>Pendapatan: Sederhana</p>
@else
    <p>Pendapatan: Rendah</p>
@endif

{{-- Kecuali --}}
@unless($pembayar->is_aktif)
    <p>Pembayar ini tidak aktif</p>
@endunless

{{-- Jika kosong --}}
@empty($pembayar->alamat)
    <p>Alamat tidak disenaraikan</p>
@endempty
```

#### 3. Gelung

```blade
{{-- Gelung foreach --}}
@foreach($pembayars as $pembayar)
    <tr>
        <td>{{ $pembayar->nama }}</td>
        <td>{{ $pembayar->no_ic }}</td>
    </tr>
@endforeach

{{-- Gelung untuk setiap ($var as $index => $item) --}}
@forelse($pembayars as $pembayar)
    <li>{{ $pembayar->nama }}</li>
@empty
    <li>Tiada pembayar ditemui</li>
@endforelse

{{-- Pembolehubah gelung khas --}}
@foreach($pembayars as $pembayar)
    @if($loop->first)
        {{-- Baris pertama --}}
    @endif

    @if($loop->last)
        {{-- Baris terakhir --}}
    @endif

    {{-- Indeks gelung (bermula dari 0) --}}
    Baris #{{ $loop->index }}

    {{-- Bilangan iterasi (bermula dari 1) --}}
    Iterasi {{ $loop->iteration }} daripada {{ $loop->count }}
@endforeach
```

#### 4. Kemasukan (Include)

```blade
{{-- Sertakan fail view lain --}}
@include('components.alert', ['type' => 'success', 'message' => 'Berjaya!'])

{{-- Sertakan jika fail wujud --}}
@includeIf('optional-component')

{{-- Sertakan fail pertama yang wujud --}}
@includeFirst(['pembayar.delete-modal', 'modal'], $data)
```

#### 5. Pewarisan Templat

```blade
{{-- Dalam fail layout (layouts/app.blade.php) --}}
<!DOCTYPE html>
<html>
    <head>
        <title>Sistem Zakat</title>
    </head>
    <body>
        {{-- Awal bahagian yang dapat digantikan --}}
        @yield('content')
        {{-- Akhir bahagian --}}
    </body>
</html>

{{-- Dalam fail view yang menggunakan layout --}}
@extends('layouts.app')

@section('content')
    <h1>Halaman Utama</h1>
    {{-- Kandungan halaman --}}
@endsection
```

#### 6. Blok dan Slots

```blade
{{-- Mendefinisikan bahagian yang boleh digantikan --}}
@section('sidebar')
    <ul>
        <li>Menu 1</li>
        <li>Menu 2</li>
    </ul>
@endsection

{{-- Melanjutkan dari parent --}}
@push('scripts')
    <script src="/js/custom.js"></script>
@endpush
```

### Contoh Blade Konteks Zakat

```blade
<div class="pembayaran-card">
    <h3>{{ $pembayaran->jenis_zakat->nama }}</h3>

    <p>Pembayar: {{ $pembayaran->pembayar->nama }}</p>
    <p>Jumlah: RM{{ number_format($pembayaran->jumlah, 2) }}</p>

    @if($pembayaran->status === 'sah')
        <span class="badge badge-success">Disahkan</span>
    @elseif($pembayaran->status === 'pending')
        <span class="badge badge-warning">Menunggu Pengesahan</span>
    @else
        <span class="badge badge-danger">Dibatalkan</span>
    @endif

    <p>Cara Pembayaran:
        @switch($pembayaran->cara_bayar)
            @case('tunai')
                Tunai
                @break
            @case('kad')
                Kad Debit/Kredit
                @break
            @case('fpx')
                FPX
                @break
            @case('online')
                Pemindahan Online
                @break
        @endswitch
    </p>
</div>
```

---

## Modul 3.2: Layout Utama Sistem Zakat

Layout adalah templat induk yang digunakan oleh semua halaman dalam aplikasi. Ia mengandungi elemen biasa seperti header, navigasi, footer, dan bahagian untuk kandungan yang berubah-ubah.

### Struktur Layout Utama

Buat fail `resources/views/layouts/app.blade.php` dengan struktur lengkap yang mengandungi:

- Navigation bar dengan menu Dashboard, Pembayar, Jenis Zakat, Pembayaran
- Header dengan branding Pusat Zakat Negeri Kedah
- Flash messages untuk feedback pengguna
- Main content area yang flexible
- Footer dengan maklumat hubungan

Lihat `/rujukan/layouts-app.blade.php` untuk kod lengkap.

---

## Modul 3.3: Eloquent ORM dan Model

### Apakah itu Model Eloquent?

Model adalah kelas PHP yang mewakili jadual dalam pangkalan data. Dengan Eloquent, anda tidak perlu menulis SQL mentah; sebaliknya, anda berinteraksi dengan objek PHP.

### Faedah Menggunakan Model

| Faedah | Penjelasan |
|--------|-----------|
| **Kaitan Mudah** | Tentukan hubungan antara jadual dengan mudah (belongsTo, hasMany) |
| **Pertanyaan Elegan** | Gunakan kaedah berantai untuk membuat pertanyaan yang terbaca |
| **Keselamatan** | Lindungi daripada serangan SQL Injection secara automatik |
| **Pengubahan** | Tukar tipe data (casting) secara automatik |
| **Penyaring** | Buat kaedah carian yang boleh digunakan semula |

### Struktur Model Pembayar

Model Pembayar mewakili jadual pembayars dalam pangkalan data.

**Atribut utama:**
- `nama` - Nama penuh pembayar
- `no_ic` - Nombor Kad Pengenalan (unik)
- `alamat` - Alamat kediaman
- `no_tel` - Nombor telefon
- `email` - Alamat email (unik)
- `pekerjaan` - Jawatan/pekerjaan
- `pendapatan_bulanan` - Pendapatan bulanan dalam RM
- `is_aktif` - Status aktif/tidak aktif

**Kaitan:**
- Seorang pembayar boleh mempunyai banyak pembayaran (HasMany)

Lihat `/rujukan/models-Pembayar.php` untuk kod lengkap.

### Struktur Model JenisZakat

Model JenisZakat menyimpan maklumat tentang kategori zakat.

**Atribut utama:**
- `nama` - Nama jenis zakat (Fitrah, Pendapatan, etc.)
- `kadar` - Kadar zakat (peratusan atau jumlah tetap)
- `penerangan` - Penjelasan tentang jenis zakat
- `is_aktif` - Status aktif/tidak aktif

**Jenis Zakat yang disokong:**
1. Zakat Fitrah - RM 7 bagi setiap orang
2. Zakat Pendapatan - 2.5% daripada pendapatan
3. Zakat Perniagaan - 2.5% daripada keuntungan
4. Zakat Wang Simpanan - 2.5% daripada simpanan
5. Zakat Emas dan Perak - 2.5%
6. Zakat Pertanian - 5% atau 10%

Lihat `/rujukan/models-JenisZakat.php` untuk kod lengkap.

### Struktur Model Pembayaran

Model Pembayaran adalah rekod transaksi zakat yang dibayar.

**Atribut utama:**
- `pembayar_id` - FK ke jadual pembayars
- `jenis_zakat_id` - FK ke jadual jenis_zakats
- `jumlah` - Jumlah yang dibayar dalam RM
- `tarikh_bayar` - Tarikh pembayaran
- `cara_bayar` - Cara pembayaran (tunai, kad, fpx, online)
- `no_resit` - Nomor resit unik
- `status` - Status pembayaran (pending, sah, batal)
- `keterangan` - Catatan tambahan

Lihat `/rujukan/models-Pembayaran.php` untuk kod lengkap.

---

## Modul 3.4: Migrasi Pangkalan Data

### Apakah itu Migrasi?

Migrasi adalah fail yang menentukan struktur jadual dalam pangkalan data. Ia membenarkan anda menggambarkan skema dalam kod dan mengawal evolusinya dari semasa ke semasa.

### Keuntungan Migrasi

- **Versi Skema** - Jejak perubahan pada struktur jadual
- **Mudah Diulang** - Jalankan migrasi di mesin pembangunan, pengujian, dan pengeluaran
- **Rollback** - Batalkan perubahan jika diperlukan
- **Dokumentasi** - Kod migrasi bertindak sebagai dokumentasi skema

### Migrasi Jadual Pembayar

Fail: `database/migrations/TIMESTAMP_create_pembayars_table.php`

Lajur utama:
- `id` - Primary key
- `nama` - String(100)
- `no_ic` - String(12), unique
- `alamat` - String(255)
- `no_tel` - String(12)
- `email` - String(100), unique
- `pekerjaan` - String(50)
- `pendapatan_bulanan` - Decimal(12,2)
- `is_aktif` - Boolean, default true
- `timestamps` - created_at, updated_at

Indeks:
- no_ic, email, is_aktif untuk prestasi carian

Lihat `/rujukan/migrations-create_pembayars_table.php` untuk kod lengkap.

### Migrasi Jadual JenisZakat

Fail: `database/migrations/TIMESTAMP_create_jenis_zakats_table.php`

Lajur utama:
- `id` - Primary key
- `nama` - String(50), unique
- `kadar` - Decimal(5,2)
- `penerangan` - Text, nullable
- `is_aktif` - Boolean, default true
- `timestamps` - created_at, updated_at

Lihat `/rujukan/migrations-create_jenis_zakats_table.php` untuk kod lengkap.

### Migrasi Jadual Pembayaran

Fail: `database/migrations/TIMESTAMP_create_pembayarans_table.php`

Lajur utama:
- `id` - Primary key
- `pembayar_id` - unsignedBigInteger, foreign key
- `jenis_zakat_id` - unsignedBigInteger, foreign key
- `jumlah` - Decimal(12,2)
- `tarikh_bayar` - Date
- `cara_bayar` - Enum(tunai, kad, fpx, online)
- `no_resit` - String(50), unique
- `status` - Enum(pending, sah, batal), default pending
- `keterangan` - Text, nullable
- `timestamps` - created_at, updated_at

Foreign Keys:
- pembayar_id -> pembayars.id (onDelete: cascade)
- jenis_zakat_id -> jenis_zakats.id (onDelete: cascade)

Indeks:
- pembayar_id, jenis_zakat_id, tarikh_bayar, status

Lihat `/rujukan/migrations-create_pembayarans_table.php` untuk kod lengkap.

### Menjalankan Migrasi

```bash
# Jalankan semua migrasi yang belum dijalankan
php artisan migrate

# Batalkan migrasi terakhir
php artisan migrate:rollback

# Batalkan semua migrasi
php artisan migrate:reset

# Batalkan dan jalankan semua migrasi semula
php artisan migrate:refresh

# Jalankan migrasi dengan seed
php artisan migrate:seed
```

---

## Modul 3.5: Pengesahan Data (Validation)

### Mengapa Pengesahan Penting?

Pengesahan memastikan bahawa data yang dihantar pengguna adalah sah, lengkap, dan memenuhi syarat-syarat perniagaan kami sebelum disimpan dalam pangkalan data.

### Peraturan Pengesahan Lazim

| Peraturan | Keterangan |
|-----------|-----------|
| `required` | Medan harus diisi |
| `unique:jadual,lajur` | Nilai harus unik dalam lajur tertentu |
| `email` | Format email yang sah |
| `numeric` | Hanya angka |
| `min:x` / `max:x` | Panjang minimum/maksimum |
| `regex:/pattern/` | Padanan corak regex |
| `confirmed` | Medan perlu sama dengan medan_confirmation |
| `date_format:d/m/Y` | Format tarikh tertentu |
| `in:nilai1,nilai2` | Salah satu daripada nilai yang diberikan |

### Contoh Pengesahan Pembayar

```php
$validated = $request->validate([
    'nama' => 'required|string|max:100',
    'no_ic' => 'required|regex:/^\d{6}-\d{2}-\d{4}$/|unique:pembayars,no_ic',
    'alamat' => 'required|string|max:255',
    'no_tel' => 'required|regex:/^0\d{1}-\d{7,8}$/',
    'email' => 'required|email|unique:pembayars,email',
    'pekerjaan' => 'required|string|max:50',
    'pendapatan_bulanan' => 'required|numeric|min:0',
], [
    'nama.required' => 'Nama pembayar diperlukan.',
    'no_ic.regex' => 'Format No. K/P tidak sah. Gunakan: 123456-12-1234',
    'no_ic.unique' => 'No. K/P ini sudah terdaftar.',
    'email.email' => 'Email tidak sah.',
    'email.unique' => 'Email ini sudah digunakan.',
]);

Pembayar::create($validated);
```

---

## Lab 3.1: Cipta Model dan Migrasi

Dalam lab ini, kita akan membuat model dan migrasi untuk tiga jadual utama.

### Langkah 1: Buat Model Pembayar dengan Migrasi

```bash
cd /path/to/sistem-zakat
php artisan make:model Pembayar -m
```

Ini akan membuat:
- `app/Models/Pembayar.php` (Model)
- `database/migrations/TIMESTAMP_create_pembayars_table.php` (Migrasi)

Isi fail migrasi dengan kod dari `/rujukan/migrations-create_pembayars_table.php`
Isi fail model dengan kod dari `/rujukan/models-Pembayar.php`

### Langkah 2: Buat Model JenisZakat dengan Migrasi

```bash
php artisan make:model JenisZakat -m
```

Isi dengan kod dari `/rujukan/` folder.

### Langkah 3: Buat Model Pembayaran dengan Migrasi

```bash
php artisan make:model Pembayaran -m
```

Isi dengan kod dari `/rujukan/` folder.

### Langkah 4: Jalankan Migrasi

```bash
php artisan migrate
```

Anda akan melihat output yang menunjukkan jadual telah dibuat.

---

## Lab 3.2: Cipta Layout dan Views

Buat struktur folder di `resources/views/`:

```
resources/views/
├── layouts/
│   └── app.blade.php (Layout utama)
└── pembayar/
    ├── index.blade.php (Senarai pembayar)
    ├── create.blade.php (Borang tambah)
    ├── show.blade.php (Lihat detail)
    └── edit.blade.php (Borang edit)
```

Semua fail view lengkap tersedia di folder `/lab/views/`.

---

## Lab 3.3: Sambung Controller ke Model dan View

Kemaskini `app/Http/Controllers/PembayarController.php` untuk menggunakan:
- Model Pembayar
- View yang telah dibuat
- Pengesahan data
- Operasi CRUD lengkap (Create, Read, Update, Delete)

Kod lengkap tersedia di `/lab/controllers/PembayarController.php`.

---

## Lab 3.4: Seeder Data Jenis Zakat

### Langkah 1: Buat Seeder

```bash
php artisan make:seeder JenisZakatSeeder
```

### Langkah 2: Isi Seeder

Buka `database/seeders/JenisZakatSeeder.php` dan tambahkan enam jenis zakat.

### Langkah 3: Jalankan Seeder

```bash
php artisan db:seed --class=JenisZakatSeeder
```

Kod lengkap tersedia di `/rujukan/seeders-JenisZakatSeeder.php`.

---

## Ringkasan Hari 3

### Apa yang Kita Pelajari

1. **Blade Template Engine**
   - Mencetak pembolehubah dengan `{{ }}`
   - Pernyataan bersyarat dengan @if, @unless, @empty
   - Gelung dengan @foreach, @forelse
   - Pewarisan templat dengan @extends, @section, @yield

2. **Layout Utama**
   - Membuat layout induk untuk semua halaman
   - Navigasi, header, footer

3. **Eloquent ORM dan Model**
   - Model Pembayar, JenisZakat, Pembayaran
   - Kaitan antara model
   - Kaedah custom dan scope

4. **Migrasi Pangkalan Data**
   - Membuat struktur jadual
   - Foreign keys dan indeks
   - Menjalankan migrasi

5. **Pengesahan Data**
   - Memastikan integriti data
   - Peraturan pengesahan
   - Mesej ralat yang diperibumikan

6. **View Blade Lengkap**
   - Index, Create, Show, Edit

7. **Controller CRUD**
   - Semua operasi CRUD
   - Pengesahan dan penyimpanan

8. **Seeder Data**
   - Data awal untuk pengujian

### Direktori Fail yang Dibuat

```
app/Models/
├── Pembayar.php
├── JenisZakat.php
└── Pembayaran.php

resources/views/
├── layouts/app.blade.php
└── pembayar/
    ├── index.blade.php
    ├── create.blade.php
    ├── show.blade.php
    └── edit.blade.php

database/migrations/
├── TIMESTAMP_create_pembayars_table.php
├── TIMESTAMP_create_jenis_zakats_table.php
└── TIMESTAMP_create_pembayarans_table.php

database/seeders/
├── JenisZakatSeeder.php
└── PembayarSeeder.php
```

### Menguji Aplikasi

```bash
# Jalankan pelayan pembangunan
php artisan serve

# Buka pelayar dan layari
http://localhost:8000/pembayar
```

---

## Persediaan untuk Hari 4

Pada **Hari 4** (hari terakhir), kami akan:

1. **Melengkapkan CRUD Jenis Zakat dan Pembayaran**
2. **Dashboard Interaktif** dengan statistik dan graf
3. **Laporan dan Eksport** ke PDF dan Excel
4. **Keselamatan** dengan autentikasi dan peranan
5. **Penyempurnaan UI/UX**

---

## Sumber Bacaan Lanjutan

- **Blade Documentation**: https://laravel.com/docs/blade
- **Eloquent ORM**: https://laravel.com/docs/eloquent
- **Validation**: https://laravel.com/docs/validation
- **Migrations**: https://laravel.com/docs/migrations

---

**Tahniah kerana menyelesaikan Hari 3!**

Anda kini mempunyai aplikasi Laravel yang berfungsi dengan pangkalan data, model, view, dan controller yang lengkap. Persiapkan diri untuk Hari 4 di mana kita akan menambahkan lebih banyak ciri dan mencantikkan antara muka!

---

Dokumen ini disediakan untuk Kursus Laravel 4 Hari | Pusat Zakat Negeri Kedah
