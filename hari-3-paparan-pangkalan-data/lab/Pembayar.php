<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| Model: Pembayar Zakat
|--------------------------------------------------------------------------
| Dicipta dengan: php artisan make:model Pembayar
|
| Model ini mewakili jadual 'pembayars' dalam pangkalan data.
| Setiap pembayar boleh mempunyai banyak pembayaran (hasMany).
|
*/

class Pembayar extends Model
{
    use HasFactory;

    /**
     * Medan yang boleh diisi secara massa (mass assignment).
     * Hanya medan dalam senarai ini yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'nama',
        'no_ic',
        'alamat',
        'no_tel',
        'email',
        'pekerjaan',
        'pendapatan_bulanan',
    ];

    /**
     * Penukaran jenis data automatik (casting).
     * Laravel akan menukar nilai ini secara automatik.
     */
    protected $casts = [
        'pendapatan_bulanan' => 'decimal:2',
    ];

    // =============================================
    // HUBUNGAN (Relationships) — Hari 4
    // =============================================

    /**
     * Seorang pembayar mempunyai banyak pembayaran.
     * Contoh: $pembayar->pembayarans
     */
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    // =============================================
    // SKOP (Scopes) — Pertanyaan yang kerap digunakan
    // =============================================

    /**
     * Cari pembayar mengikut nama atau IC.
     * Contoh: Pembayar::carian('Ahmad')->get()
     */
    public function scopeCarian($query, $carian)
    {
        return $query->where('nama', 'like', "%{$carian}%")
                     ->orWhere('no_ic', 'like', "%{$carian}%");
    }

    /**
     * Pembayar dengan pendapatan melebihi jumlah tertentu.
     * Contoh: Pembayar::pendapatanMelebihi(3000)->get()
     */
    public function scopePendapatanMelebihi($query, $jumlah)
    {
        return $query->where('pendapatan_bulanan', '>=', $jumlah);
    }

    // =============================================
    // ACCESSOR — Format data untuk paparan
    // =============================================

    /**
     * Format No. IC dengan sengkang.
     * Contoh: 850101145678 → 850101-14-5678
     */
    public function getIcFormatAttribute()
    {
        $ic = $this->no_ic;
        if (strlen($ic) === 12) {
            return substr($ic, 0, 6) . '-' . substr($ic, 6, 2) . '-' . substr($ic, 8, 4);
        }
        return $ic;
    }

    /**
     * Format pendapatan dengan 'RM'.
     * Contoh: 3500.00 → RM 3,500.00
     */
    public function getPendapatanFormatAttribute()
    {
        return 'RM ' . number_format($this->pendapatan_bulanan, 2);
    }

    /**
     * Jumlah keseluruhan zakat yang telah dibayar.
     * Contoh: $pembayar->jumlah_bayaran → RM 500.00
     */
    public function getJumlahBayaranAttribute()
    {
        return $this->pembayarans()
                    ->where('status', 'sah')
                    ->sum('jumlah');
    }
}
