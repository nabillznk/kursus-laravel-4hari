<?php

namespace App\Http\Controllers;

use App\Models\JenisZakat;
use Illuminate\Http\Request;

class JenisZakatController extends Controller
{
    public function index()
    {
        $jenisZakats = JenisZakat::withCount('pembayarans')->latest()->paginate(10);

        return view('jenis-zakat.index', compact('jenisZakats'));
    }

    public function create()
    {
        return view('jenis-zakat.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'kadar'      => 'required|numeric|min:0',
            'penerangan' => 'nullable|string',
            'is_aktif'   => 'boolean',
        ], [
            'nama.required'  => 'Sila masukkan nama jenis zakat.',
            'kadar.required' => 'Sila masukkan kadar zakat.',
            'kadar.numeric'  => 'Kadar mestilah nombor.',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif', true);

        JenisZakat::create($validated);

        return redirect()->route('jenis-zakat.index')
            ->with('success', 'Jenis zakat berjaya ditambah.');
    }

    public function show(JenisZakat $jenis_zakat)
    {
        $jenis_zakat->loadCount('pembayarans');
        return view('jenis-zakat.show', compact('jenis_zakat'));
    }

    public function edit(JenisZakat $jenis_zakat)
    {
        return view('jenis-zakat.edit', compact('jenis_zakat'));
    }

    public function update(Request $request, JenisZakat $jenis_zakat)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'kadar'      => 'required|numeric|min:0',
            'penerangan' => 'nullable|string',
            'is_aktif'   => 'boolean',
        ], [
            'nama.required'  => 'Sila masukkan nama jenis zakat.',
            'kadar.required' => 'Sila masukkan kadar zakat.',
            'kadar.numeric'  => 'Kadar mestilah nombor.',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif', false);

        $jenis_zakat->update($validated);

        return redirect()->route('jenis-zakat.index')
            ->with('success', 'Jenis zakat berjaya dikemaskini.');
    }

    public function destroy(JenisZakat $jenis_zakat)
    {
        $jenis_zakat->delete();

        return redirect()->route('jenis-zakat.index')
            ->with('success', 'Jenis zakat berjaya dipadam.');
    }
}
