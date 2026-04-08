<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayar;
use Illuminate\Http\Request;

class PembayarApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayar::query();

        if ($request->filled('carian')) {
            $query->carian($request->carian);
        }

        $pembayars = $query->latest()->paginate(10);

        return response()->json([
            'status'  => 'success',
            'message' => 'Senarai pembayar berjaya dipaparkan.',
            'data'    => $pembayars,
        ]);
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
        ]);

        $pembayar = Pembayar::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pembayar berjaya ditambah.',
            'data'    => $pembayar,
        ], 201);
    }

    public function show(Pembayar $pembayar)
    {
        $pembayar->load('pembayarans.jenisZakat');

        return response()->json([
            'status'  => 'success',
            'message' => 'Maklumat pembayar berjaya dipaparkan.',
            'data'    => $pembayar,
        ]);
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
        ]);

        $pembayar->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Maklumat pembayar berjaya dikemaskini.',
            'data'    => $pembayar,
        ]);
    }

    public function destroy(Pembayar $pembayar)
    {
        $pembayar->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pembayar berjaya dipadam.',
            'data'    => null,
        ]);
    }
}
