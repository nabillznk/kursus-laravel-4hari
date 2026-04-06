<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayar;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Controller: Pembayar Zakat
|--------------------------------------------------------------------------
| Dicipta dengan: php artisan make:controller Api/PembayarApiController --api
|
| Pengawal ini mengendalikan semua permintaan API untuk data pembayar.
| Semua respons dalam format JSON.
|
| Uji dengan Thunder Client atau curl:
|   GET    http://sistem-zakat.test/api/pembayar
|   GET    http://sistem-zakat.test/api/pembayar/1
|   POST   http://sistem-zakat.test/api/pembayar
|   PUT    http://sistem-zakat.test/api/pembayar/1
|   DELETE http://sistem-zakat.test/api/pembayar/1
|
*/

class PembayarApiController extends Controller
{
    /**
     * Senarai semua pembayar (dengan pagination).
     *
     * GET /api/pembayar
     * GET /api/pembayar?carian=Ahmad
     * GET /api/pembayar?page=2
     */
    public function index(Request $request)
    {
        $query = Pembayar::query();

        // Carian (jika ada parameter 'carian')
        if ($request->has('carian')) {
            $carian = $request->carian;
            $query->where('nama', 'like', "%{$carian}%")
                  ->orWhere('no_ic', 'like', "%{$carian}%");
        }

        $pembayars = $query->orderBy('nama')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $pembayars,
        ]);
    }

    /**
     * Simpan pembayar baru.
     *
     * POST /api/pembayar
     * Content-Type: application/json
     * Body: { "nama": "Ahmad", "no_ic": "850101145678", ... }
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
        ]);

        $pembayar = Pembayar::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pembayar berjaya didaftarkan.',
            'data'    => $pembayar,
        ], 201);
    }

    /**
     * Papar seorang pembayar.
     *
     * GET /api/pembayar/1
     */
    public function show(Pembayar $pembayar)
    {
        // Muatkan pembayaran berkaitan
        $pembayar->load('pembayarans.jenisZakat');

        return response()->json([
            'status' => 'success',
            'data'   => $pembayar,
        ]);
    }

    /**
     * Kemaskini maklumat pembayar.
     *
     * PUT /api/pembayar/1
     * Content-Type: application/json
     * Body: { "nama": "Ahmad bin Ali", ... }
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

        return response()->json([
            'status'  => 'success',
            'message' => 'Maklumat pembayar berjaya dikemaskini.',
            'data'    => $pembayar,
        ]);
    }

    /**
     * Padam pembayar.
     *
     * DELETE /api/pembayar/1
     */
    public function destroy(Pembayar $pembayar)
    {
        $pembayar->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pembayar berjaya dipadamkan.',
        ]);
    }
}
