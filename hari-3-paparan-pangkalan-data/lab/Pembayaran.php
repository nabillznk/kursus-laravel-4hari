<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model: Pembayaran Zakat
|--------------------------------------------------------------------------
| Dicipta dengan: php artisan make:model Pembayaran
|
| Model ini mewakili jadual 'pembayarans' — rekod setiap transaksi
| pembayaran zakat. Setiap pembayaran merujuk kepada seorang pembayar
| dan satu jenis zakat.
|
*/

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'pembayar_id',
        'jenis_zakat_id',
        'jumlah',
        'tarikh_bayar',
        'cara_bayar',
        'no_resit',
        'status',
        'catatan',
    ];

    protected $casts = [
        'jumlah'       => 'decimal:2',
        'tarikh_bayar' => 'date',
    ];

    // =============================================
    // HUBUNGAN
    // =============================================

    /**
     * Pembayaran ini milik seorang pembayar.
     * Contoh: $pembayaran->pembayar->nama
     */
    public function pembayar()
    {
        return $this->belongsTo(Pembayar::class);
    }

    /**
     * Pembayaran ini untuk satu jenis zakat.
     * Contoh: $pembayaran->jenisZakat->nama
     */
    public function jenisZakat()
    {
        return $this->belongsTo(JenisZakat::class);
    }

    // =============================================
    // SKOP
    // =============================================

    /**
     * Pembayaran yang sah sahaja.
     * Contoh: Pembayaran::sah()->get()
     */
    public function scopeSah($query)
    {
        return $query->where('status', 'sah');
    }

    /**
     * Pembayaran terkini.
     * Contoh: Pembayaran::terkini()->take(5)->get()
     */
    public function scopeTerkini($query)
    {
        return $query->orderBy('tarikh_bayar', 'desc');
    }

    /**
     * Tapis mengikut bulan dan tahun.
     * Contoh: Pembayaran::bulan(3, 2026)->get()
     */
    public function scopeBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tarikh_bayar', $bulan)
                     ->whereYear('tarikh_bayar', $tahun);
    }

    // =============================================
    // ACCESSOR
    // =============================================

    /**
     * Format jumlah dengan 'RM'.
     * Contoh: 150.00 → "RM 150.00"
     */
    public function getJumlahFormatAttribute()
    {
        return 'RM ' . number_format($this->jumlah, 2);
    }

    /**
     * Format tarikh dalam format Malaysia.
     * Contoh: "06 April 2026"
     */
    public function getTarikhFormatAttribute()
    {
        return $this->tarikh_bayar->format('d F Y');
    }

    /**
     * Label status dengan warna.
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'sah'     => 'Sah',
            'pending' => 'Menunggu',
            'batal'   => 'Batal',
            default   => $this->status,
        };
    }

    /**
     * Label cara bayar.
     */
    public function getCaraBayarLabelAttribute()
    {
        return match ($this->cara_bayar) {
            'tunai'  => 'Tunai',
            'kad'    => 'Kad Kredit/Debit',
            'fpx'    => 'FPX',
            'online' => 'Bayaran Dalam Talian',
            default  => $this->cara_bayar,
        };
    }

    // =============================================
    // KAEDAH STATIK
    // =============================================

    /**
     * Jana nombor resit unik.
     * Format: ZK-YYYYMMDD-XXXXX
     * Contoh: ZK-20260406-A3F2B
     */
    public static function janaNoResit()
    {
        $tarikh = now()->format('Ymd');
        $rawak  = strtoupper(Str::random(5));
        return "ZK-{$tarikh}-{$rawak}";
    }
}
