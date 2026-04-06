# Kursus Laravel 4 Hari — Sistem Pengurusan Zakat

Kursus latihan amali Laravel untuk pemula, sepenuhnya dalam Bahasa Melayu. Sepanjang 4 hari, peserta akan membina **Sistem Pengurusan Zakat** untuk **Pusat Zakat Negeri Kedah** — sebuah aplikasi web CRUD lengkap menggunakan Laravel.

## Projek: Sistem Pengurusan Zakat Kedah

Aplikasi ini menguruskan:

- **Pembayar Zakat** — Daftar, kemaskini, cari, dan padam rekod pembayar
- **Jenis Zakat** — Zakat Fitrah, Zakat Pendapatan, Zakat Perniagaan, Zakat Wang Simpanan, dll.
- **Pembayaran** — Rekod bayaran, resit, status, laporan
- **Pengguna/Admin** — Log masuk kakitangan Pusat Zakat untuk akses sistem

## Ringkasan Kursus

| Hari | Topik | Fokus | Hasil |
|------|-------|-------|-------|
| [**Hari 1**](./hari-1-persediaan/) | Persediaan & Projek | Laragon, MySQL, VS Code, cipta projek `sistem-zakat` | Projek Laravel berjalan + DB `zakat_kedah` |
| [**Hari 2**](./hari-2-penghalaan-pengawal/) | Penghalaan & Pengawal | Routes, Controllers, Middleware, CRUD Pembayar | Pengawal PembayarController siap dengan 7 kaedah |
| [**Hari 3**](./hari-3-paparan-pangkalan-data/) | Paparan & Pangkalan Data | Blade, Eloquent, Migrations, Validation | Borang & senarai pembayar berfungsi penuh |
| [**Hari 4**](./hari-4-lanjutan-projek/) | Auth, API & Siap Guna | Authentication, API, Hubungan Model, Deploy | Sistem lengkap dengan login + API |

## Keperluan Sistem

Sebelum memulakan kursus, pastikan anda mempunyai:

- Windows 10/11 (64-bit)
- Minimum 4GB RAM (8GB disyorkan)
- 2GB ruang cakera kosong
- Sambungan internet

## Perisian yang Diperlukan

| Perisian | Tujuan | Pautan Muat Turun |
|----------|--------|-------------------|
| Laragon | Apache + PHP + MySQL | [laragon.org](https://laragon.org/download/) |
| VS Code | Editor kod | [code.visualstudio.com](https://code.visualstudio.com/) |
| Node.js | Aset frontend (Vite) | [nodejs.org](https://nodejs.org/) |
| Chrome | Pelayar & DevTools | [google.com/chrome](https://www.google.com/chrome/) |

> **Nota:** Composer, Git, dan PHP sudah disertakan dalam Laragon. Tidak perlu pasang berasingan.

## Struktur Repositori

```
kursus-laravel-4hari/
├── README.md                          # Fail ini
├── hari-1-persediaan/
│   ├── README.md                      # Nota lengkap Hari 1
│   ├── lab/
│   │   └── env-contoh.txt             # Contoh fail .env untuk sistem zakat
│   └── rujukan/
│       └── senarai-semak.md           # Senarai semak persediaan
├── hari-2-penghalaan-pengawal/
│   ├── README.md                      # Nota lengkap Hari 2
│   ├── lab/
│   │   ├── web.php                    # Contoh fail routes (zakat)
│   │   └── PembayarController.php     # Pengawal CRUD Pembayar
│   └── rujukan/
│       └── arahan-http.md             # Rujukan kaedah HTTP
├── hari-3-paparan-pangkalan-data/
│   ├── README.md                      # Nota lengkap Hari 3
│   ├── lab/
│   │   ├── app.blade.php              # Layout utama sistem zakat
│   │   ├── create_pembayars_table.php # Migrasi jadual pembayar
│   │   ├── create_jenis_zakats_table.php # Migrasi jadual jenis zakat
│   │   ├── create_pembayarans_table.php  # Migrasi jadual pembayaran
│   │   ├── Pembayar.php               # Model Pembayar
│   │   ├── JenisZakat.php             # Model JenisZakat
│   │   └── Pembayaran.php            # Model Pembayaran
│   └── rujukan/
│       └── blade-cheatsheet.md        # Rujukan Blade
├── hari-4-lanjutan-projek/
│   ├── README.md                      # Nota lengkap Hari 4
│   ├── lab/
│   │   ├── api.php                    # Laluan API zakat
│   │   └── PembayarApiController.php  # Pengawal API
│   └── rujukan/
│       └── artisan-cheatsheet.md      # Rujukan arahan Artisan
└── slaid/
    └── README.md                      # Pautan ke slaid pembentangan
```

## Cara Menggunakan Repositori Ini

1. **Clone** repositori ini:
   ```bash
   git clone https://github.com/habibtalib/kursus-laravel-4hari.git
   ```

2. Buka folder hari yang berkenaan (contoh: `hari-1-persediaan/`)

3. Baca `README.md` dalam setiap folder untuk nota lengkap

4. Ikut arahan dalam bahagian **Lab** untuk latihan amali

5. Rujuk folder `lab/` untuk contoh kod yang boleh disalin

6. Rujuk folder `rujukan/` untuk cheatsheet dan senarai semak

## Entiti Utama Projek

### 1. Pembayar Zakat (`pembayars`)
| Medan | Jenis | Penerangan |
|-------|-------|------------|
| nama | string | Nama penuh pembayar |
| no_ic | string | No. Kad Pengenalan (unik) |
| alamat | text | Alamat penuh |
| no_tel | string | No. telefon |
| email | string | Alamat e-mel |
| pekerjaan | string | Jenis pekerjaan |
| pendapatan_bulanan | decimal | Pendapatan sebulan (RM) |

### 2. Jenis Zakat (`jenis_zakats`)
| Medan | Jenis | Penerangan |
|-------|-------|------------|
| nama | string | Nama jenis zakat |
| kadar | decimal | Kadar/peratusan zakat |
| penerangan | text | Penerangan ringkas |
| is_aktif | boolean | Status aktif/tidak aktif |

### 3. Pembayaran (`pembayarans`)
| Medan | Jenis | Penerangan |
|-------|-------|------------|
| pembayar_id | foreign | FK ke jadual pembayar |
| jenis_zakat_id | foreign | FK ke jadual jenis zakat |
| jumlah | decimal | Jumlah bayaran (RM) |
| tarikh_bayar | date | Tarikh pembayaran dibuat |
| cara_bayar | enum | tunai / kad / fpx / online |
| no_resit | string | Nombor resit (unik) |
| status | enum | pending / sah / batal |

## Sambungan VS Code Disyorkan

| Sambungan | Tujuan |
|-----------|--------|
| PHP Intelephense | Autocomplete & diagnostik PHP |
| Laravel Blade Snippets | Penyerlahan sintaks Blade |
| Laravel Snippets | Snippet pantas Laravel |
| DotENV | Penyerlahan fail .env |
| Thunder Client | Ujian API dalam VS Code |

## Komuniti & Sumber Tambahan

- [Dokumentasi Rasmi Laravel](https://laravel.com/docs)
- [Laracasts](https://laracasts.com) — Video tutorial Laravel
- [Laravel News](https://laravel-news.com) — Berita & tutorial
- [Laravel Malaysia (Facebook)](https://www.facebook.com/groups/laravel.my/) — Komuniti tempatan
- [Lembaga Zakat Negeri Kedah](https://www.zakatkedah.com.my/) — Rujukan domain

## Penyumbang

Disediakan oleh **Habib** — [bespokesb.com](https://bespokesb.com)

## Lesen

Repositori ini dilesenkan di bawah [MIT License](LICENSE).
