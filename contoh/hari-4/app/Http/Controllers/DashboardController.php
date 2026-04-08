<?php

namespace App\Http\Controllers;

use App\Models\JenisZakat;
use App\Models\Pembayar;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahPembayar = Pembayar::count();

        $jumlahKutipan = Pembayaran::sah()->sum('jumlah');

        $transaksiBulanIni = Pembayaran::whereMonth('tarikh_bayar', now()->month)
            ->whereYear('tarikh_bayar', now()->year)
            ->count();

        $jenisZakatAktif = JenisZakat::aktif()->count();

        // Jenis zakat paling popular
        $jenisPopular = JenisZakat::withCount('pembayarans')
            ->orderByDesc('pembayarans_count')
            ->first();

        // 5 pembayaran terkini
        $pembayaranTerkini = Pembayaran::with(['pembayar', 'jenisZakat'])
            ->latest('tarikh_bayar')
            ->take(5)
            ->get();

        // Ringkasan bulanan (6 bulan terakhir)
        $ringkasanBulanan = Pembayaran::sah()
            ->where('tarikh_bayar', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(tarikh_bayar, '%Y-%m') as bulan"),
                DB::raw('COUNT(*) as bilangan'),
                DB::raw('SUM(jumlah) as jumlah_kutipan')
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return view('dashboard', compact(
            'jumlahPembayar',
            'jumlahKutipan',
            'transaksiBulanIni',
            'jenisZakatAktif',
            'jenisPopular',
            'pembayaranTerkini',
            'ringkasanBulanan'
        ));
    }
}
