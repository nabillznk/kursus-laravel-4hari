<?php

namespace App\Http\Controllers;

use App\Events\PembayaranDibuat;
use App\Models\JenisZakat;
use App\Models\Pembayar;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with(['pembayar', 'jenisZakat']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('carian')) {
            $carian = $request->carian;
            $query->whereHas('pembayar', function ($q) use ($carian) {
                $q->where('nama', 'like', "%{$carian}%");
            });
        }

        $pembayarans = $query->latest('tarikh_bayar')->paginate(10)->withQueryString();

        return view('pembayaran.index', compact('pembayarans'));
    }

    public function create()
    {
        $pembayars  = Pembayar::orderBy('nama')->get();
        $jenisZakats = JenisZakat::aktif()->orderBy('nama')->get();

        // Auto-generate no resit
        $lastResit = Pembayaran::latest('id')->value('no_resit');
        if ($lastResit) {
            $lastNum = (int) substr($lastResit, -4);
            $noResit = 'ZK-' . date('Y') . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $noResit = 'ZK-' . date('Y') . '-0001';
        }

        return view('pembayaran.create', compact('pembayars', 'jenisZakats', 'noResit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pembayar_id'    => 'required|exists:pembayars,id',
            'jenis_zakat_id' => 'required|exists:jenis_zakats,id',
            'jumlah'         => 'required|numeric|min:0.01',
            'tarikh_bayar'   => 'required|date',
            'cara_bayar'     => 'required|in:tunai,kad,fpx,online',
            'no_resit'       => 'required|string|unique:pembayarans,no_resit',
            'status'         => 'required|in:pending,sah,batal',
        ], [
            'pembayar_id.required'    => 'Sila pilih pembayar.',
            'jenis_zakat_id.required' => 'Sila pilih jenis zakat.',
            'jumlah.required'         => 'Sila masukkan jumlah bayaran.',
            'jumlah.min'              => 'Jumlah mestilah lebih daripada sifar.',
            'tarikh_bayar.required'   => 'Sila masukkan tarikh bayaran.',
            'cara_bayar.required'     => 'Sila pilih cara bayaran.',
            'no_resit.required'       => 'Sila masukkan nombor resit.',
            'no_resit.unique'         => 'Nombor resit ini telah wujud.',
        ]);

        $pembayaran = Pembayaran::create($validated);

        PembayaranDibuat::dispatch($pembayaran);

        return redirect()->route('pembayaran.index')
            ->with('success', 'Pembayaran berjaya direkodkan.');
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['pembayar', 'jenisZakat']);
        return view('pembayaran.show', compact('pembayaran'));
    }

    public function edit(Pembayaran $pembayaran)
    {
        $pembayars   = Pembayar::orderBy('nama')->get();
        $jenisZakats = JenisZakat::aktif()->orderBy('nama')->get();

        return view('pembayaran.edit', compact('pembayaran', 'pembayars', 'jenisZakats'));
    }

    public function update(Request $request, Pembayaran $pembayaran)
    {
        $validated = $request->validate([
            'pembayar_id'    => 'required|exists:pembayars,id',
            'jenis_zakat_id' => 'required|exists:jenis_zakats,id',
            'jumlah'         => 'required|numeric|min:0.01',
            'tarikh_bayar'   => 'required|date',
            'cara_bayar'     => 'required|in:tunai,kad,fpx,online',
            'no_resit'       => 'required|string|unique:pembayarans,no_resit,' . $pembayaran->id,
            'status'         => 'required|in:pending,sah,batal',
        ], [
            'pembayar_id.required'    => 'Sila pilih pembayar.',
            'jenis_zakat_id.required' => 'Sila pilih jenis zakat.',
            'jumlah.required'         => 'Sila masukkan jumlah bayaran.',
            'tarikh_bayar.required'   => 'Sila masukkan tarikh bayaran.',
            'cara_bayar.required'     => 'Sila pilih cara bayaran.',
            'no_resit.required'       => 'Sila masukkan nombor resit.',
            'no_resit.unique'         => 'Nombor resit ini telah wujud.',
        ]);

        $pembayaran->update($validated);

        return redirect()->route('pembayaran.index')
            ->with('success', 'Pembayaran berjaya dikemaskini.');
    }

    public function destroy(Pembayaran $pembayaran)
    {
        $pembayaran->delete();

        return redirect()->route('pembayaran.index')
            ->with('success', 'Pembayaran berjaya dipadam.');
    }
}
