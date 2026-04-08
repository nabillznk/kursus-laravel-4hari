<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisZakat;
use App\Models\Pembayar;
use App\Models\Pembayaran;

class StatistikApiController extends Controller
{
    public function index()
    {
        $jumlahPembayar    = Pembayar::count();
        $jumlahKutipan     = Pembayaran::sah()->sum('jumlah');
        $jumlahTransaksi   = Pembayaran::count();
        $transaksiSah      = Pembayaran::sah()->count();
        $transaksiPending  = Pembayaran::where('status', 'pending')->count();
        $transaksiBatal    = Pembayaran::where('status', 'batal')->count();
        $jenisZakatAktif   = JenisZakat::aktif()->count();

        return response()->json([
            'status'  => 'success',
            'message' => 'Statistik sistem zakat.',
            'data'    => [
                'jumlah_pembayar'     => $jumlahPembayar,
                'jumlah_kutipan'      => (float) $jumlahKutipan,
                'jumlah_transaksi'    => $jumlahTransaksi,
                'transaksi_sah'       => $transaksiSah,
                'transaksi_pending'   => $transaksiPending,
                'transaksi_batal'     => $transaksiBatal,
                'jenis_zakat_aktif'   => $jenisZakatAktif,
            ],
        ]);
    }
}
