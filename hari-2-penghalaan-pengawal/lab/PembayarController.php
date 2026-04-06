<?php

namespace App\Http\Controllers;

use App\Models\Pembayar;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| PembayarController — Pengawal CRUD Pembayar Zakat
|--------------------------------------------------------------------------
| Dicipta dengan: php artisan make:controller PembayarController --resource
|
| Pengawal ini mengendalikan semua operasi CRUD untuk pembayar zakat:
| index, create, store, show, edit, update, destroy
|
*/

class PembayarController extends Controller
{
    /**
     * Papar senarai semua pembayar zakat.
     *
     * URL:    GET /pembayar
     * Contoh: http://sistem-zakat.test/pembayar
     */
    public function index(Request $request)
    {
        // Carian mengikut nama atau IC (jika ada parameter carian)
        $carian = $request->query('carian');

        $pembayars = Pembayar::when($carian, function ($query, $carian) {
                $query->where('nama', 'like', "%{$carian}%")
                      ->orWhere('no_ic', 'like', "%{$carian}%");
            })
            ->orderBy('nama')
            ->paginate(15);

        return view('pembayar.index', compact('pembayars', 'carian'));
    }

    /**
     * Papar borang untuk mendaftar pembayar baru.
     *
     * URL:    GET /pembayar/create
     * Contoh: http://sistem-zakat.test/pembayar/create
     */
    public function create()
    {
        return view('pembayar.create');
    }

    /**
     * Simpan pembayar baru ke dalam pangkalan data.
     *
     * URL:    POST /pembayar
     * Data:   Dari borang pendaftaran
     */
    public function store(Request $request)
    {
        // Pengesahan data (Validation)
        $validated = $request->validate([
            'nama'                => 'required|string|max:255',
            'no_ic'               => 'required|string|size:12|unique:pembayars,no_ic',
            'alamat'              => 'required|string',
            'no_tel'              => 'required|string|max:15',
            'email'               => 'nullable|email|max:255',
            'pekerjaan'           => 'nullable|string|max:255',
            'pendapatan_bulanan'  => 'nullable|numeric|min:0',
        ], [
            // Mesej ralat dalam Bahasa Melayu
            'nama.required'       => 'Nama pembayar wajib diisi.',
            'no_ic.required'      => 'No. Kad Pengenalan wajib diisi.',
            'no_ic.size'          => 'No. IC mestilah 12 digit (tanpa sengkang).',
            'no_ic.unique'        => 'No. IC ini sudah didaftarkan.',
            'alamat.required'     => 'Alamat wajib diisi.',
            'no_tel.required'     => 'No. telefon wajib diisi.',
            'email.email'         => 'Format e-mel tidak sah.',
            'pendapatan_bulanan.numeric' => 'Pendapatan mestilah dalam angka.',
        ]);

        // Simpan ke pangkalan data
        Pembayar::create($validated);

        // Redirect ke senarai dengan mesej kejayaan
        return redirect()
            ->route('pembayar.index')
            ->with('success', 'Pembayar berjaya didaftarkan!');
    }

    /**
     * Papar maklumat seorang pembayar.
     *
     * URL:    GET /pembayar/{id}
     * Contoh: http://sistem-zakat.test/pembayar/1
     */
    public function show(Pembayar $pembayar)
    {
        // Muatkan pembayaran berkaitan (Hari 4)
        $pembayar->load('pembayarans.jenisZakat');

        return view('pembayar.show', compact('pembayar'));
    }

    /**
     * Papar borang untuk mengemaskini pembayar.
     *
     * URL:    GET /pembayar/{id}/edit
     * Contoh: http://sistem-zakat.test/pembayar/1/edit
     */
    public function edit(Pembayar $pembayar)
    {
        return view('pembayar.edit', compact('pembayar'));
    }

    /**
     * Kemaskini maklumat pembayar dalam pangkalan data.
     *
     * URL:    PUT /pembayar/{id}
     * Data:   Dari borang kemaskini
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
        ]);

        $pembayar->update($validated);

        return redirect()
            ->route('pembayar.show', $pembayar)
            ->with('success', 'Maklumat pembayar berjaya dikemaskini!');
    }

    /**
     * Padam pembayar dari pangkalan data.
     *
     * URL:    DELETE /pembayar/{id}
     * Nota:   Menggunakan borang dengan @method('DELETE')
     */
    public function destroy(Pembayar $pembayar)
    {
        $pembayar->delete();

        return redirect()
            ->route('pembayar.index')
            ->with('success', 'Pembayar berjaya dipadamkan.');
    }
}
