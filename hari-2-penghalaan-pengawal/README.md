# Kursus Laravel 4 Hari | Pusat Zakat Negeri Kedah

## Hari 2: Penghalaan (Routing) dan Pengawal (Controllers)

**Tarikh:** Hari ke-2 dari 4 hari
**Tema Projek:** Sistem Pengurusan Zakat
**Organisasi:** Pusat Zakat Negeri Kedah
**Prasyarat:** Projek `sistem-zakat` dari Hari 1, Laragon sedang berjalan

---

## Objektif Hari 2

Selepas menyelesaikan hari ini, anda akan dapat:

✅ Memahami cara mengatur penghalaan (routing) dalam Laravel
✅ Menentukan kaedah HTTP (GET, POST, PUT, DELETE)
✅ Membuat pengawal (controller) dengan kaedah sumber (resource methods)
✅ Menggunakan middleware untuk keselamatan asas
✅ Mengendalikan permintaan (request) dan tindak balas (response)
✅ Membuat laluan CRUD lengkap untuk entiti Pembayar Zakat

---

## Modul 2.1: Asas Penghalaan (Routing)

### Apa itu Penghalaan?

Penghalaan (routing) adalah proses menentukan bagaimana aplikasi anda merespons kepada permintaan URL dari pelanggan. Setiap URL yang diakses melalui penyemak imbas mesti dirancang dalam laluan.

Dalam Laravel, semua penghalaan ditakrifkan dalam fail `routes/web.php`.

### Fail Penghalaan Utama

```
projek-laravel/
├── routes/
│   ├── web.php          ← Semua penghalaan web berada di sini
│   └── api.php          ← Untuk API (tidak digunakan hari ini)
```

### Kaedah HTTP Asas

| Kaedah | Tujuan | Contoh |
|--------|--------|--------|
| **GET** | Mengambil data, paparan halaman | GET `/pembayar` (lihat senarai pembayar) |
| **POST** | Hantar data baru, buat rekod | POST `/pembayar` (simpan pembayar baru) |
| **PUT/PATCH** | Kemaskini data sedia ada | PUT `/pembayar/1` (ubah pembayar ID 1) |
| **DELETE** | Hapus data | DELETE `/pembayar/1` (buang pembayar ID 1) |

### Contoh Penghalaan Asas

#### 1. Penghalaan GET Ringkas

```php
Route::get('/pembayar', function () {
    return 'Senarai semua pembayar zakat';
});
```

**Penjelasan:**
- `Route::get()` - Mendengar permintaan GET
- `/pembayar` - Laluan URL
- `function()` - Fungsi anonimous yang mengembalikan respons

#### 2. Penghalaan Dengan Parameter

```php
// Melihat satu pembayar mengikut ID
Route::get('/pembayar/{id}', function ($id) {
    return "Melihat pembayar dengan ID: $id";
});

// Melihat laporan pembayar dengan tahun
Route::get('/pembayar/{id}/laporan/{tahun}', function ($id, $tahun) {
    return "Laporan pembayar $id untuk tahun $tahun";
});
```

**Penjelasan:**
- `{id}` - Parameter dinamik, boleh berupa sebarang nilai
- Parameter mesti dihantar kepada fungsi dalam urutan yang sama

#### 3. Kaedah Penghalaan Lain

```php
// POST - Untuk menghantar borang
Route::post('/pembayar', function () {
    return 'Pembayar baru disimpan';
});

// PUT - Untuk mengubah semua medan
Route::put('/pembayar/{id}', function ($id) {
    return "Pembayar $id telah dikemas kini";
});

// DELETE - Untuk menghapus
Route::delete('/pembayar/{id}', function ($id) {
    return "Pembayar $id telah dihapus";
});
```

### Laluan Bernama (Named Routes)

Laluan bernama memudahkan anda mereferensikan laluan dalam kod tanpa mengetik URL secara langsung.

```php
// Takrifkan laluan dengan nama
Route::get('/pembayar/{id}', function ($id) {
    return "Paparan pembayar $id";
})->name('pembayar.show');

// Dalam templat atau kod, gunakan helper route()
echo route('pembayar.show', ['id' => 1]);
// Keluaran: /pembayar/1
```

**Faedah:**
- Jika anda menukar URL, anda tidak perlu mengubah semua pautan dalam kod
- Lebih mudah dibaca dan diurus

### Kumpulan Penghalaan (Route Groups)

Kumpulkan penghalaan dengan prefiks atau middleware yang sama:

```php
Route::group(['prefix' => 'api'], function () {
    Route::get('/pembayar', function () {
        return 'Senarai pembayar (API)';
    });

    Route::get('/pembayar/{id}', function ($id) {
        return "Pembayar $id (API)";
    });
});

// URL yang terhasil:
// GET /api/pembayar
// GET /api/pembayar/{id}
```

---

## Modul 2.2: Pengawal (Controllers)

### Apa itu Pengawal?

Pengawal adalah kelas yang mengandungi logik untuk mengendalikan permintaan dan mengembalikan tindak balas. Daripada menulis fungsi langsung dalam penghalaan, kami menggunakan pengawal untuk mengorganisir kod dengan lebih baik.

### Struktur Pengawal Sumber (Resource Controller)

Dalam sistem REST, pengawal sumber biasanya mempunyai 7 kaedah:

| Kaedah | Laluan | Penerangan |
|--------|--------|-----------|
| **index** | GET `/pembayar` | Paparan senarai semua pembayar |
| **create** | GET `/pembayar/create` | Paparan borang untuk buat pembayar baru |
| **store** | POST `/pembayar` | Simpan pembayar baru ke pangkalan data |
| **show** | GET `/pembayar/{id}` | Paparan satu pembayar |
| **edit** | GET `/pembayar/{id}/edit` | Paparan borang untuk ubah pembayar |
| **update** | PUT `/pembayar/{id}` | Simpan perubahan pembayar ke pangkalan data |
| **destroy** | DELETE `/pembayar/{id}` | Hapus pembayar dari pangkalan data |

### Membuat Pengawal Dengan Artisan

Laravel menyediakan perintah Artisan untuk membuat pengawal secara automatik:

```bash
php artisan make:controller PembayarController --resource
```

Perintah ini membuat fail:
```
app/Http/Controllers/PembayarController.php
```

Dengan 7 kaedah sudah persediaan.

### Kod Lengkap PembayarController

Berikut adalah kod lengkap untuk `PembayarController` dengan penjelasan setiap kaedah:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PembayarController extends Controller
{
    /**
     * Paparkan senarai semua pembayar zakat
     * GET /pembayar
     */
    public function index()
    {
        // Di sini kita akan mengambil semua pembayar dari pangkalan data
        // Untuk sekarang, kembalikan teks
        return view('pembayar.index');
    }

    /**
     * Paparkan borang untuk buat pembayar baru
     * GET /pembayar/create
     */
    public function create()
    {
        // Kembalikan paparan borang penciptaan
        return view('pembayar.create');
    }

    /**
     * Simpan pembayar baru ke pangkalan data
     * POST /pembayar
     *
     * @param Request $request Permintaan dari borang
     */
    public function store(Request $request)
    {
        // Sah-kan data
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'no_ic' => 'required|unique:pembayars|regex:/^\d{12}$/',
            'alamat' => 'required|string',
            'no_tel' => 'required|regex:/^\d{10,11}$/',
            'email' => 'required|email|unique:pembayars',
            'pekerjaan' => 'required|string',
            'pendapatan_bulanan' => 'required|numeric|min:0',
        ]);

        // Simpan ke pangkalan data
        // Pembayar::create($validated);

        return redirect()->route('pembayar.index')
                       ->with('success', 'Pembayar baru telah ditambah.');
    }

    /**
     * Paparkan satu pembayar mengikut ID
     * GET /pembayar/{id}
     *
     * @param int $id ID pembayar
     */
    public function show($id)
    {
        // Di sini kita akan mengambil pembayar dari pangkalan data
        // $pembayar = Pembayar::find($id);

        // Kembalikan paparan untuk pembayar satu
        return view('pembayar.show', ['id' => $id]);
    }

    /**
     * Paparkan borang untuk ubah pembayar sedia ada
     * GET /pembayar/{id}/edit
     *
     * @param int $id ID pembayar
     */
    public function edit($id)
    {
        // Ambil pembayar dari pangkalan data
        // $pembayar = Pembayar::find($id);

        // Kembalikan paparan borang pengeditan
        return view('pembayar.edit', ['id' => $id]);
    }

    /**
     * Kemaskini pembayar dalam pangkalan data
     * PUT /pembayar/{id}
     *
     * @param Request $request Permintaan dengan data baru
     * @param int $id ID pembayar
     */
    public function update(Request $request, $id)
    {
        // Sah-kan data
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'no_ic' => 'required|unique:pembayars,no_ic,' . $id . '|regex:/^\d{12}$/',
            'alamat' => 'required|string',
            'no_tel' => 'required|regex:/^\d{10,11}$/',
            'email' => 'required|email|unique:pembayars,email,' . $id,
            'pekerjaan' => 'required|string',
            'pendapatan_bulanan' => 'required|numeric|min:0',
        ]);

        // Kemaskini rekod dalam pangkalan data
        // $pembayar = Pembayar::find($id);
        // $pembayar->update($validated);

        return redirect()->route('pembayar.show', $id)
                       ->with('success', 'Pembayar telah dikemas kini.');
    }

    /**
     * Hapus pembayar dari pangkalan data
     * DELETE /pembayar/{id}
     *
     * @param int $id ID pembayar
     */
    public function destroy($id)
    {
        // Cari dan hapus pembayar
        // $pembayar = Pembayar::find($id);
        // $pembayar->delete();

        return redirect()->route('pembayar.index')
                       ->with('success', 'Pembayar telah dihapus.');
    }
}
```

### Penjelasan Setiap Kaedah

#### 1. `index()` - Senarai Pembayar

```php
public function index()
{
    return view('pembayar.index');
}
```

- Mengambil semua pembayar dari pangkalan data
- Mengembalikan paparan dengan senarai pembayar
- Dipanggil oleh: `GET /pembayar`

#### 2. `create()` - Paparan Borang Buat

```php
public function create()
{
    return view('pembayar.create');
}
```

- Mengembalikan paparan borang kosong untuk input data baru
- Dipanggil oleh: `GET /pembayar/create`

#### 3. `store()` - Simpan Pembayar Baru

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'nama' => 'required|string|max:100',
        'no_ic' => 'required|unique:pembayars|regex:/^\d{12}$/',
        // ... peraturan pengesahan lain
    ]);

    // Simpan ke pangkalan data
    return redirect()->route('pembayar.index')
                   ->with('success', 'Pembayar baru telah ditambah.');
}
```

- Menerima data dari borang (POST)
- Mengesahkan data
- Menyimpan ke pangkalan data
- Mengalihkan ke halaman senarai dengan mesej kejayaan
- Dipanggil oleh: `POST /pembayar`

#### 4. `show()` - Paparkan Satu Pembayar

```php
public function show($id)
{
    return view('pembayar.show', ['id' => $id]);
}
```

- Mengambil satu pembayar berdasarkan ID
- Mengembalikan paparan dengan butiran pembayar
- Dipanggil oleh: `GET /pembayar/{id}`

#### 5. `edit()` - Paparan Borang Ubah

```php
public function edit($id)
{
    return view('pembayar.edit', ['id' => $id]);
}
```

- Mengambil pembayar berdasarkan ID
- Mengembalikan paparan borang yang sudah dipenuhi dengan data sedia ada
- Dipanggil oleh: `GET /pembayar/{id}/edit`

#### 6. `update()` - Kemaskini Pembayar

```php
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'nama' => 'required|string|max:100',
        // ... peraturan pengesahan lain
    ]);

    // Kemaskini dalam pangkalan data
    return redirect()->route('pembayar.show', $id)
                   ->with('success', 'Pembayar telah dikemas kini.');
}
```

- Menerima data dari borang (PUT)
- Mengesahkan data
- Kemaskini pangkalan data
- Mengalihkan ke paparan pembayar yang dikemas kini
- Dipanggil oleh: `PUT /pembayar/{id}`

#### 7. `destroy()` - Hapus Pembayar

```php
public function destroy($id)
{
    // Hapus dari pangkalan data
    return redirect()->route('pembayar.index')
                   ->with('success', 'Pembayar telah dihapus.');
}
```

- Menghapus pembayar berdasarkan ID
- Mengalihkan kembali ke senarai dengan mesej kejayaan
- Dipanggil oleh: `DELETE /pembayar/{id}`

---

## Modul 2.3: Middleware

### Apa itu Middleware?

Middleware adalah lapisan yang melintasi permintaan sebelum sampai ke pengawal anda. Ia digunakan untuk:

- Pengesahan (authentication)
- Pembenaranan (authorization)
- Pencatatan (logging)
- Pengesahan token CSRF
- Kompresi respons

### Middleware Asas dalam Laravel

Laravel sudah mempunyai beberapa middleware bawaan:

| Middleware | Tujuan |
|-----------|--------|
| `auth` | Periksa sama ada pengguna sudah log masuk |
| `auth:sanctum` | Pengesahan API |
| `verified` | Periksa sama ada email sudah disahkan |
| `cors` | Hantar semula permintaan lintas asal |
| `throttle` | Hadkan bilangan permintaan |

### Menggunakan Middleware dalam Penghalaan

```php
// Menggunakan middleware tunggal
Route::get('/pembayar', [PembayarController::class, 'index'])->middleware('auth');

// Menggunakan berbilang middleware
Route::put('/pembayar/{id}', [PembayarController::class, 'update'])
    ->middleware('auth', 'verified');

// Menggunakan dalam kumpulan penghalaan
Route::middleware(['auth'])->group(function () {
    Route::get('/pembayar', [PembayarController::class, 'index']);
    Route::post('/pembayar', [PembayarController::class, 'store']);
    Route::put('/pembayar/{id}', [PembayarController::class, 'update']);
    Route::delete('/pembayar/{id}', [PembayarController::class, 'destroy']);
});
```

### Membuat Middleware Kustom

Untuk membuat middleware kustom, gunakan Artisan:

```bash
php artisan make:middleware CatatanAkses
```

Ini menghasilkan fail di `app/Http/Middleware/CatatanAkses.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CatatanAkses
{
    /**
     * Tangani permintaan masuk.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Tindakan SEBELUM permintaan sampai ke pengawal
        \Log::info('Pengguna mengakses: ' . $request->path());

        $response = $next($request);

        // Tindakan SELEPAS permintaan diproses
        \Log::info('Respons telah dikirim');

        return $response;
    }
}
```

### Mendaftarkan Middleware Kustom

Daftarkan di `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ... middleware lain
    'catatan' => \App\Http\Middleware\CatatanAkses::class,
];
```

Kemudian gunakan:

```php
Route::get('/pembayar', [PembayarController::class, 'index'])->middleware('catatan');
```

---

## Modul 2.4: Permintaan & Tindak Balas (Request & Response)

### Objek Permintaan (Request)

Objek `Request` mengandungi semua maklumat tentang permintaan dari pelanggan:

```php
public function store(Request $request)
{
    // Ambil satu medan dari borang
    $nama = $request->input('nama');

    // Ambil semua data dari borang
    $semua = $request->all();

    // Ambil data tertentu saja
    $data = $request->only('nama', 'email');

    // Ambil semua kecuali medan tertentu
    $data = $request->except('_token');

    // Periksa sama ada medan wujud
    if ($request->has('nama')) {
        // Medan 'nama' ada
    }

    // Dapatkan nilai default jika medan tiada
    $pekerjaan = $request->input('pekerjaan', 'Tidak dinyatakan');
}
```

### Pengesahan Data (Validation)

Pengesahan memastikan data yang diterima adalah sah dan sesuai:

```php
public function store(Request $request)
{
    // Pengesahan ringkas
    $validated = $request->validate([
        'nama' => 'required|string|max:100',
        'no_ic' => 'required|unique:pembayars|regex:/^\d{12}$/',
        'email' => 'required|email|unique:pembayars',
        'pendapatan_bulanan' => 'required|numeric|min:0',
    ]);

    // Jika pengesahan gagal, Laravel akan otomatik mengembalikan ke halaman sebelumnya
    // dengan mesej ralat dan data borang yang dihantar
}
```

### Peraturan Pengesahan Biasa

| Peraturan | Penerangan | Contoh |
|----------|-----------|--------|
| `required` | Medan mesti ada dan tidak kosong | `'nama' => 'required'` |
| `string` | Nilai mesti teks | `'nama' => 'string'` |
| `email` | Nilai mesti format email sah | `'email' => 'email'` |
| `max:100` | Panjang maksimum 100 watak | `'nama' => 'max:100'` |
| `min:0` | Nilai minimum 0 | `'pendapatan' => 'min:0'` |
| `unique:table` | Nilai unik dalam jadual | `'no_ic' => 'unique:pembayars'` |
| `regex:/^\d{12}$/` | Padanan dengan corak regex | `'no_ic' => 'regex:/^\d{12}$/'` |

### Tindak Balas (Response)

Laravel memberikan beberapa cara untuk menghantar tindak balas:

#### 1. Mengembalikan Paparan

```php
return view('pembayar.index');
```

#### 2. Mengembalikan JSON

```php
return response()->json([
    'status' => 'kejayaan',
    'mesej' => 'Pembayar baru telah ditambah',
    'data' => $pembayar
]);
```

#### 3. Mengalihkan ke Laluan Lain

```php
return redirect()->route('pembayar.index');
```

#### 4. Mengalihkan dengan Mesej

```php
return redirect()->route('pembayar.show', $id)
                ->with('success', 'Pembayar telah dikemas kini.');
```

#### 5. Mengembalikan Teks Biasa

```php
return 'Senarai pembayar';
```

---

## Lab 2.1: Cipta Penghalaan untuk Sistem Zakat

### Langkah 1: Buka Fail Penghalaan

Buka fail `routes/web.php`:

```bash
# Dengan text editor anda
# Cari fail di: projek-laravel/routes/web.php
```

### Langkah 2: Bersihkan Penghalaan Lama

Buang semua penghalaan lama (selain yang diperlukan untuk dashboard):

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayarController;

Route::get('/', function () {
    return view('welcome');
});
```

### Langkah 3: Tambah Penghalaan Sumber untuk Pembayar

Tambahkan penghalaan sumber ini untuk pengawal `PembayarController`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayarController;

Route::get('/', function () {
    return view('welcome');
});

// Penghalaan CRUD untuk Pembayar Zakat
Route::resource('pembayar', PembayarController::class);
```

**Penjelasan:**

`Route::resource('pembayar', PembayarController::class)` akan secara otomatik mencipta 7 penghalaan:

| Kaedah HTTP | Laluan | Nama Penghalaan | Kaedah Pengawal |
|-------------|--------|-----------------|-----------------|
| GET | `/pembayar` | `pembayar.index` | `index()` |
| GET | `/pembayar/create` | `pembayar.create` | `create()` |
| POST | `/pembayar` | `pembayar.store` | `store()` |
| GET | `/pembayar/{pembayar}` | `pembayar.show` | `show()` |
| GET | `/pembayar/{pembayar}/edit` | `pembayar.edit` | `edit()` |
| PUT/PATCH | `/pembayar/{pembayar}` | `pembayar.update` | `update()` |
| DELETE | `/pembayar/{pembayar}` | `pembayar.destroy` | `destroy()` |

### Langkah 4: Simpan Fail

Simpan fail `routes/web.php`.

---

## Lab 2.2: Cipta PembayarController

### Langkah 1: Buat Pengawal Dengan Artisan

Buka terminal dan jalankan:

```bash
php artisan make:controller PembayarController --resource
```

Keluaran yang dijangka:

```
Controller created successfully.
```

Ini akan mencipta fail:
```
app/Http/Controllers/PembayarController.php
```

### Langkah 2: Buka Fail Pengawal

Buka fail `app/Http/Controllers/PembayarController.php` dengan text editor anda.

### Langkah 3: Lengkapkan Kaedah index()

Gantikan kaedah `index()` dengan kod ini:

```php
public function index()
{
    // Pada hari ini, kita hanya akan mengembalikan paparan
    // Pada hari 3, kita akan mengambil data dari pangkalan data

    return view('pembayar.index');
}
```

### Langkah 4: Lengkapkan Kaedah create()

```php
public function create()
{
    return view('pembayar.create');
}
```

### Langkah 5: Lengkapkan Kaedah store()

```php
public function store(Request $request)
{
    // Sah-kan data dari borang
    $validated = $request->validate([
        'nama' => 'required|string|max:100',
        'no_ic' => 'required|unique:pembayars|regex:/^\d{12}$/',
        'alamat' => 'required|string',
        'no_tel' => 'required|regex:/^\d{10,11}$/',
        'email' => 'required|email|unique:pembayars',
        'pekerjaan' => 'required|string',
        'pendapatan_bulanan' => 'required|numeric|min:0',
    ]);

    // Untuk sekarang, hanya alihkan kembali dengan mesej
    // Pada hari 3, kita akan simpan ke pangkalan data

    return redirect()->route('pembayar.index')
                    ->with('success', 'Pembayar baru telah ditambah.');
}
```

### Langkah 6: Lengkapkan Kaedah show()

```php
public function show($id)
{
    // Pada hari 3, kita akan mengambil pembayar berdasarkan ID

    return view('pembayar.show', ['id' => $id]);
}
```

### Langkah 7: Lengkapkan Kaedah edit()

```php
public function edit($id)
{
    // Pada hari 3, kita akan mengambil pembayar berdasarkan ID

    return view('pembayar.edit', ['id' => $id]);
}
```

### Langkah 8: Lengkapkan Kaedah update()

```php
public function update(Request $request, $id)
{
    // Sah-kan data dari borang
    $validated = $request->validate([
        'nama' => 'required|string|max:100',
        'no_ic' => 'required|unique:pembayars,no_ic,' . $id . '|regex:/^\d{12}$/',
        'alamat' => 'required|string',
        'no_tel' => 'required|regex:/^\d{10,11}$/',
        'email' => 'required|email|unique:pembayars,email,' . $id,
        'pekerjaan' => 'required|string',
        'pendapatan_bulanan' => 'required|numeric|min:0',
    ]);

    // Untuk sekarang, hanya alihkan dengan mesej
    // Pada hari 3, kita akan kemaskini pangkalan data

    return redirect()->route('pembayar.show', $id)
                    ->with('success', 'Pembayar telah dikemas kini.');
}
```

### Langkah 9: Lengkapkan Kaedah destroy()

```php
public function destroy($id)
{
    // Untuk sekarang, hanya alihkan dengan mesej
    // Pada hari 3, kita akan padam dari pangkalan data

    return redirect()->route('pembayar.index')
                    ->with('success', 'Pembayar telah dihapus.');
}
```

### Langkah 10: Simpan Fail Pengawal

Simpan fail `app/Http/Controllers/PembayarController.php`.

---

## Lab 2.3: Uji Penghalaan dan Pengawal

### Langkah 1: Lihat Senarai Penghalaan

Buka terminal dan jalankan:

```bash
php artisan route:list
```

Keluaran yang dijangka (disaring untuk pembayar):

```
GET|HEAD  /pembayar                   pembayar.index         PembayarController@index
GET|HEAD  /pembayar/create            pembayar.create        PembayarController@create
POST      /pembayar                   pembayar.store         PembayarController@store
GET|HEAD  /pembayar/{pembayar}        pembayar.show          PembayarController@show
GET|HEAD  /pembayar/{pembayar}/edit   pembayar.edit          PembayarController@edit
PUT|PATCH /pembayar/{pembayar}        pembayar.update        PembayarController@update
DELETE    /pembayar/{pembayar}        pembayar.destroy       PembayarController@destroy
```

**Perkara Penting:**
- Semua 7 penghalaan sudah terdaftar
- Setiap penghalaan mempunyai nama unik (cth: `pembayar.index`)
- Kaedah HTTP ditunjukkan di sebelah kiri

### Langkah 2: Uji Penghalaan Dalam Penyemak Imbas

Pastikan Laragon sedang berjalan, kemudian buka penyemak imbas anda.

#### Uji 1: Senarai Pembayar

```
URL: http://localhost/sistem-zakat/public/pembayar
Kaedah: GET
```

**Jangkaan:**
- Halaman akan meminta paparan `pembayar.index`
- Anda akan melihat ralat karena paparan belum dibuat (normal untuk hari ini)

#### Uji 2: Borang Buat Pembayar

```
URL: http://localhost/sistem-zakat/public/pembayar/create
Kaedah: GET
```

**Jangkaan:**
- Halaman akan meminta paparan `pembayar.create`
- Ralat paparan akan muncul (normal)

#### Uji 3: Paparkan Satu Pembayar

```
URL: http://localhost/sistem-zakat/public/pembayar/1
Kaedah: GET
```

**Jangkaan:**
- Parameter `{id}` akan ditangkap sebagai `1`
- Halaman akan meminta paparan `pembayar.show`
- Ralat paparan akan muncul (normal)

### Langkah 3: Periksa Penghalaan dengan Perintah Artisan

Untuk melihat penghalaan tertentu:

```bash
php artisan route:list --name=pembayar
```

Untuk melihat penghalaan dengan URI tertentu:

```bash
php artisan route:list --path=pembayar
```

---

## Ringkasan Hari 2

### Apa yang Anda Belajar

✅ **Penghalaan (Routing):**
- Cara mentakrifkan penghalaan dalam `routes/web.php`
- Kaedah HTTP: GET, POST, PUT, DELETE
- Parameter dinamik: `{id}`
- Laluan bernama: `.name('pembayar.show')`
- Penghalaan sumber: `Route::resource()`

✅ **Pengawal (Controllers):**
- Membuat pengawal dengan Artisan
- 7 kaedah sumber: index, create, store, show, edit, update, destroy
- Struktur kaedah dan tujuannya

✅ **Middleware:**
- Konsep middleware sebagai lapisan permintaan
- Middleware bawaan: `auth`, `verified`, `throttle`
- Menggunakan middleware dalam penghalaan

✅ **Permintaan & Tindak Balas:**
- Objek `Request` dan kaedahnya
- Pengesahan data dengan `validate()`
- Tindak balas: `view()`, `redirect()`, `json()`

✅ **Lab Praktikal:**
- Membuat penghalaan sumber untuk Pembayar
- Membuat PembayarController dengan 7 kaedah
- Menguji penghalaan dalam penyemak imbas

### Fail yang Telah Diubah/Dibuat

| Fail | Status | Deskripsi |
|------|--------|-----------|
| `routes/web.php` | ✏️ Diubah | Tambah `Route::resource('pembayar', PembayarController::class)` |
| `app/Http/Controllers/PembayarController.php` | ✨ Dibuat | Pengawal lengkap dengan 7 kaedah |

### Persediaan untuk Hari 3

Pada Hari 3, kita akan:

📚 **Membuat Lapangan (Views) dengan Blade**
- Membuat templat untuk senarai pembayar
- Membuat borang buat dan ubah pembayar
- Menggunakan Blade directive seperti `@if`, `@foreach`, `@auth`

🗄️ **Bekerja dengan Model dan Pangkalan Data**
- Membuat Model `Pembayar`
- Membuat dan menjalankan Migration
- Mengambil data dari pangkalan data dalam pengawal
- Menyimpan dan memkemaskini data

🔗 **Integrasi Lengkap**
- Penghalaan → Pengawal → Model → Pangkalan Data → Paparan

Persediaan:
- Pastikan anda memahami 7 kaedah sumber
- Bersiaplah untuk mempelajari Blade templating
- Baca dokumentasi Laravel tentang Migrations dan Models

---

## Soalan Pelajaran

Untuk memastikan anda memahami hari ini, jawab soalan berikut:

1. **Penghalaan:** Apakah perbezaan antara `GET /pembayar` dan `POST /pembayar`?

2. **Pengawal:** Kaedah pengawal manakah yang tidak menerima parameter `{id}`?

3. **Middleware:** Berikan satu contoh middleware bawaan Laravel dan tujuannya.

4. **Pengesahan:** Bagaimana anda akan mengesahkan bahawa medan `no_ic` mempunyai tepat 12 digit?

5. **Penghalaan Sumber:** Berapa banyak penghalaan yang dibuat oleh `Route::resource('pembayar', PembayarController::class)`?

---

## Rujukan Berguna

- [Laravel Routing Documentation](https://laravel.com/docs/routing)
- [Laravel Controllers Documentation](https://laravel.com/docs/controllers)
- [Laravel Middleware Documentation](https://laravel.com/docs/middleware)
- [Laravel Validation Documentation](https://laravel.com/docs/validation)
- [Laravel Responses Documentation](https://laravel.com/docs/responses)

---

**Selamat belajar! Jangan ragu untuk bereksperimen dengan penghalaan dan pengawal anda.**

Untuk bantuan, lihat dokumentasi Laravel atau tanya ahli anda.
