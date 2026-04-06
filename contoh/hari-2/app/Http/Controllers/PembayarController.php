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
