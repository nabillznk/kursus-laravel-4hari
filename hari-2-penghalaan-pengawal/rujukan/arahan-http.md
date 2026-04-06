# Rujukan Kaedah HTTP — Sistem Zakat

## Kaedah HTTP dalam Laravel

| Kaedah | Tujuan | Route | Contoh Sistem Zakat |
|--------|--------|-------|---------------------|
| **GET** | Dapatkan/papar data | `Route::get()` | Senarai pembayar, borang |
| **POST** | Hantar data baharu | `Route::post()` | Daftar pembayar baru |
| **PUT/PATCH** | Kemas kini data | `Route::put()` | Kemaskini maklumat IC |
| **DELETE** | Padam data | `Route::delete()` | Padam rekod pembayar |

## Resource Routes (7 Laluan Automatik)

Apabila anda menggunakan `Route::resource('pembayar', PembayarController::class)`:

| Kaedah | URI | Action | Nama Laluan |
|--------|-----|--------|-------------|
| GET | `/pembayar` | index | pembayar.index |
| GET | `/pembayar/create` | create | pembayar.create |
| POST | `/pembayar` | store | pembayar.store |
| GET | `/pembayar/{pembayar}` | show | pembayar.show |
| GET | `/pembayar/{pembayar}/edit` | edit | pembayar.edit |
| PUT/PATCH | `/pembayar/{pembayar}` | update | pembayar.update |
| DELETE | `/pembayar/{pembayar}` | destroy | pembayar.destroy |

## Semua Resource Routes Sistem Zakat

```php
Route::resource('pembayar', PembayarController::class);
Route::resource('pembayaran', PembayaranController::class);
Route::resource('jenis-zakat', JenisZakatController::class);
```

## Arahan Berguna

```bash
# Lihat semua laluan berdaftar
php artisan route:list

# Lihat laluan untuk pembayar sahaja
php artisan route:list --name=pembayar

# Lihat laluan untuk pembayaran
php artisan route:list --name=pembayaran

# Bersihkan cache laluan
php artisan route:clear
```
