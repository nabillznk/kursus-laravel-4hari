# Rujukan Pantas Arahan Artisan — Sistem Zakat

## Pelayan & Umum

| Arahan | Tujuan |
|--------|--------|
| `php artisan serve` | Mulakan pelayan pembangunan |
| `php artisan tinker` | REPL interaktif untuk uji kod PHP |
| `php artisan list` | Senarai semua arahan yang tersedia |
| `php artisan help migrate` | Bantuan untuk arahan tertentu |

## Cipta Fail (make:) — Sistem Zakat

| Arahan | Cipta |
|--------|-------|
| `php artisan make:model Pembayar -mfs` | Model + Migrasi + Factory + Seeder |
| `php artisan make:model JenisZakat -m` | Model + Migrasi |
| `php artisan make:model Pembayaran -m` | Model + Migrasi |
| `php artisan make:controller PembayarController --resource` | Pengawal CRUD (7 method) |
| `php artisan make:controller PembayaranController --resource` | Pengawal CRUD pembayaran |
| `php artisan make:controller DashboardController` | Pengawal dashboard |
| `php artisan make:controller Api/PembayarApiController --api` | Pengawal API (5 method) |
| `php artisan make:migration create_pembayars_table` | Migrasi jadual pembayar |
| `php artisan make:migration create_jenis_zakats_table` | Migrasi jadual jenis zakat |
| `php artisan make:migration create_pembayarans_table` | Migrasi jadual pembayaran |
| `php artisan make:middleware CekKakitangan` | Middleware tersuai |
| `php artisan make:request StorePembayarRequest` | Form Request validasi |
| `php artisan make:seeder JenisZakatSeeder` | Seeder jenis zakat |

## Pangkalan Data

| Arahan | Tujuan |
|--------|--------|
| `php artisan migrate` | Jalankan semua migrasi |
| `php artisan migrate:rollback` | Batal migrasi terakhir |
| `php artisan migrate:refresh` | Batal semua & jalankan semula |
| `php artisan migrate:fresh` | Padam semua jadual & jalankan semula |
| `php artisan migrate:status` | Semak status migrasi |
| `php artisan db:seed` | Jalankan semua seeder |
| `php artisan db:seed --class=JenisZakatSeeder` | Jalankan seeder jenis zakat |
| `php artisan migrate:fresh --seed` | Reset DB & seed |

## Laluan

| Arahan | Tujuan |
|--------|--------|
| `php artisan route:list` | Senarai semua laluan |
| `php artisan route:list --name=pembayar` | Laluan pembayar sahaja |
| `php artisan route:list --name=pembayaran` | Laluan pembayaran sahaja |
| `php artisan route:cache` | Cache laluan (production) |
| `php artisan route:clear` | Bersihkan cache laluan |

## Cache & Optimasi

| Arahan | Tujuan |
|--------|--------|
| `php artisan config:cache` | Cache konfigurasi |
| `php artisan config:clear` | Bersihkan cache konfigurasi |
| `php artisan cache:clear` | Bersihkan cache aplikasi |
| `php artisan view:clear` | Bersihkan cache paparan |
| `php artisan optimize` | Optimumkan untuk production |
| `php artisan optimize:clear` | Bersihkan semua cache |

## Pengesahan (Breeze)

| Arahan | Tujuan |
|--------|--------|
| `composer require laravel/breeze --dev` | Pasang Breeze |
| `php artisan breeze:install blade` | Pasang dengan Blade |
| `npm install && npm run build` | Bina aset frontend |

## Tinker — Uji Cepat

```bash
php artisan tinker

# Cipta pembayar
>>> App\Models\Pembayar::create(['nama'=>'Ahmad', 'no_ic'=>'850101145678', 'alamat'=>'Alor Setar', 'no_tel'=>'0124567890']);

# Kira jumlah pembayar
>>> App\Models\Pembayar::count();

# Cari pembayar
>>> App\Models\Pembayar::where('nama', 'like', '%Ahmad%')->get();

# Jumlah kutipan zakat (sah)
>>> App\Models\Pembayaran::where('status', 'sah')->sum('jumlah');

# Pembayaran terkini
>>> App\Models\Pembayaran::with('pembayar', 'jenisZakat')->latest('tarikh_bayar')->take(5)->get();
```
