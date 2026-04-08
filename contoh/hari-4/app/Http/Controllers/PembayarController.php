<?php

namespace App\Http\Controllers;

use App\Models\Pembayar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayarController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayar::query();

        if ($request->filled('carian')) {
            $query->carian($request->carian);
        }

        $pembayars = $query->latest()->paginate(10)->withQueryString();

        return view('pembayar.index', compact('pembayars'));
    }

    public function create()
    {
        return view('pembayar.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'                => 'required|string|max:255',
            'no_ic'               => 'required|string|size:12|unique:pembayars,no_ic',
            'alamat'              => 'required|string',
            'no_tel'              => 'required|string|max:15',
            'email'               => 'nullable|email',
            'pekerjaan'           => 'nullable|string|max:255',
            'pendapatan_bulanan'  => 'nullable|numeric|min:0',
            'gambar'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'nama.required'   => 'Sila masukkan nama pembayar.',
            'no_ic.required'  => 'Sila masukkan nombor IC.',
            'no_ic.size'      => 'Nombor IC mestilah 12 digit.',
            'no_ic.unique'    => 'Nombor IC ini telah didaftarkan.',
            'alamat.required' => 'Sila masukkan alamat.',
            'no_tel.required' => 'Sila masukkan nombor telefon.',
            'email.email'     => 'Format e-mel tidak sah.',
            'gambar.image'    => 'Fail mestilah imej.',
            'gambar.mimes'    => 'Format imej: JPEG, PNG, JPG sahaja.',
            'gambar.max'      => 'Saiz imej maksimum 2MB.',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('pembayar', 'public');
        }

        Pembayar::create($validated);

        return redirect()->route('pembayar.index')
            ->with('success', 'Pembayar berjaya ditambah.');
    }

    public function show(Pembayar $pembayar)
    {
        $pembayar->load(['pembayarans.jenisZakat']);
        return view('pembayar.show', compact('pembayar'));
    }

    public function edit(Pembayar $pembayar)
    {
        return view('pembayar.edit', compact('pembayar'));
    }

    public function update(Request $request, Pembayar $pembayar)
    {
        $validated = $request->validate([
            'nama'                => 'required|string|max:255',
            'no_ic'               => 'required|string|size:12|unique:pembayars,no_ic,' . $pembayar->id,
            'alamat'              => 'required|string',
            'no_tel'              => 'required|string|max:15',
            'email'               => 'nullable|email',
            'pekerjaan'           => 'nullable|string|max:255',
            'pendapatan_bulanan'  => 'nullable|numeric|min:0',
            'gambar'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'nama.required'   => 'Sila masukkan nama pembayar.',
            'no_ic.required'  => 'Sila masukkan nombor IC.',
            'no_ic.size'      => 'Nombor IC mestilah 12 digit.',
            'no_ic.unique'    => 'Nombor IC ini telah didaftarkan.',
            'alamat.required' => 'Sila masukkan alamat.',
            'no_tel.required' => 'Sila masukkan nombor telefon.',
            'email.email'     => 'Format e-mel tidak sah.',
            'gambar.image'    => 'Fail mestilah imej.',
            'gambar.mimes'    => 'Format imej: JPEG, PNG, JPG sahaja.',
            'gambar.max'      => 'Saiz imej maksimum 2MB.',
        ]);

        if ($request->hasFile('gambar')) {
            // Padam gambar lama jika ada
            if ($pembayar->gambar) {
                Storage::disk('public')->delete($pembayar->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('pembayar', 'public');
        }

        $pembayar->update($validated);

        return redirect()->route('pembayar.show', $pembayar)
            ->with('success', 'Maklumat pembayar berjaya dikemaskini.');
    }

    public function destroy(Pembayar $pembayar)
    {
        $pembayar->delete();

        return redirect()->route('pembayar.index')
            ->with('success', 'Pembayar berjaya dipadam.');
    }
}
