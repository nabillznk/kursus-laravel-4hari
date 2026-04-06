<?php

namespace App\Http\Controllers;

use App\Models\Pembayar;
use Illuminate\Http\Request;

/**
 * Pengawal untuk pengurusan pembayar zakat.
 * Mengandungi 7 kaedah CRUD (index, create, store, show, edit, update, destroy).
 */
class PembayarController extends Controller
{
    /**
     * Papar senarai semua pembayar.
     * Menyokong carian mengikut nama atau no IC.
     */
    public function index(Request $request)
    {
        $carian = $request->query('carian');

        $pembayars = Pembayar::query()
            ->when($carian, function ($query, $carian) {
                return $query->carian($carian);
            })
            ->orderBy('nama')
            ->paginate(10)
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
        $disahkan = $request->validate([
            'nama' => 'required|string|max:255',
            'no_ic' => 'required|string|size:12|unique:pembayars,no_ic',
            'alamat' => 'required|string',
            'no_tel' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'pekerjaan' => 'nullable|string|max:255',
            'pendapatan_bulanan' => 'nullable|numeric|min:0',
        ], [
            'nama.required' => 'Sila masukkan nama penuh.',
            'nama.max' => 'Nama tidak boleh melebihi 255 aksara.',
            'no_ic.required' => 'Sila masukkan nombor kad pengenalan.',
            'no_ic.size' => 'Nombor IC mestilah 12 digit.',
            'no_ic.unique' => 'Nombor IC ini telah didaftarkan.',
            'alamat.required' => 'Sila masukkan alamat.',
            'no_tel.required' => 'Sila masukkan nombor telefon.',
            'no_tel.max' => 'Nombor telefon tidak boleh melebihi 15 aksara.',
            'email.email' => 'Format e-mel tidak sah.',
            'pendapatan_bulanan.numeric' => 'Pendapatan mestilah nombor.',
            'pendapatan_bulanan.min' => 'Pendapatan tidak boleh kurang daripada 0.',
        ]);

        Pembayar::create($disahkan);

        return redirect()
            ->route('pembayar.index')
            ->with('success', 'Pembayar berjaya didaftarkan!');
    }

    /**
     * Papar maklumat terperinci seorang pembayar.
     */
    public function show(Pembayar $pembayar)
    {
        return view('pembayar.show', compact('pembayar'));
    }

    /**
     * Papar borang kemaskini maklumat pembayar.
     */
    public function edit(Pembayar $pembayar)
    {
        return view('pembayar.edit', compact('pembayar'));
    }

    /**
     * Kemaskini maklumat pembayar dalam pangkalan data.
     */
    public function update(Request $request, Pembayar $pembayar)
    {
        $disahkan = $request->validate([
            'nama' => 'required|string|max:255',
            'no_ic' => 'required|string|size:12|unique:pembayars,no_ic,' . $pembayar->id,
            'alamat' => 'required|string',
            'no_tel' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'pekerjaan' => 'nullable|string|max:255',
            'pendapatan_bulanan' => 'nullable|numeric|min:0',
        ], [
            'nama.required' => 'Sila masukkan nama penuh.',
            'nama.max' => 'Nama tidak boleh melebihi 255 aksara.',
            'no_ic.required' => 'Sila masukkan nombor kad pengenalan.',
            'no_ic.size' => 'Nombor IC mestilah 12 digit.',
            'no_ic.unique' => 'Nombor IC ini telah didaftarkan oleh pembayar lain.',
            'alamat.required' => 'Sila masukkan alamat.',
            'no_tel.required' => 'Sila masukkan nombor telefon.',
            'no_tel.max' => 'Nombor telefon tidak boleh melebihi 15 aksara.',
            'email.email' => 'Format e-mel tidak sah.',
            'pendapatan_bulanan.numeric' => 'Pendapatan mestilah nombor.',
            'pendapatan_bulanan.min' => 'Pendapatan tidak boleh kurang daripada 0.',
        ]);

        $pembayar->update($disahkan);

        return redirect()
            ->route('pembayar.show', $pembayar)
            ->with('success', 'Maklumat pembayar berjaya dikemaskini!');
    }

    /**
     * Padam pembayar daripada pangkalan data.
     */
    public function destroy(Pembayar $pembayar)
    {
        $nama = $pembayar->nama;
        $pembayar->delete();

        return redirect()
            ->route('pembayar.index')
            ->with('success', "Pembayar \"{$nama}\" berjaya dipadamkan!");
    }
}
