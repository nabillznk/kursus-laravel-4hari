<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| Model: Jenis Zakat
|--------------------------------------------------------------------------
| Dicipta dengan: php artisan make:model JenisZakat
|
| Model ini mewakili jadual 'jenis_zakats' dalam pangkalan data.
| Contoh: Zakat Fitrah, Zakat Pendapatan, Zakat Perniagaan, dll.
|
*/

class JenisZakat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kadar',
        'penerangan',
        'is_aktif',
    ];

    protected $casts = [
        'kadar'    => 'decimal:2',
        'is_aktif' => 'boolean',
    ];

    // =============================================
    // HUBUNGAN
    // =============================================

    /**
     * Satu jenis zakat mempunyai banyak pembayaran.
     * Contoh: $jenisZakat->pembayarans
     */
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    // =============================================
    // SKOP
    // =============================================

    /**
     * Hanya jenis zakat yang aktif.
     * Contoh: JenisZakat::aktif()->get()
     */
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    // =============================================
    // ACCESSOR
    // =============================================

    /**
     * Format kadar untuk paparan.
     * Contoh: 2.50 → "2.50%"
     */
    public function getKadarFormatAttribute()
    {
        return number_format($this->kadar, 2) . '%';
    }

    /**
     * Jumlah kutipan untuk jenis zakat ini.
     */
    public function getJumlahKutipanAttribute()
    {
        return $this->pembayarans()
                    ->where('status', 'sah')
                    ->sum('jumlah');
    }
}
