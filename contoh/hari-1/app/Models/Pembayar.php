<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Pembayar
 *
 * Mewakili pembayar zakat dalam sistem.
 * Setiap pembayar mempunyai maklumat peribadi seperti
 * nama, no IC, alamat, dan pendapatan bulanan.
 */
class Pembayar extends Model
{
    use HasFactory;

    /**
     * Nama jadual dalam pangkalan data.
     */
    protected $table = 'pembayars';

    /**
     * Medan yang boleh diisi secara mass-assignment.
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
     * Penukaran jenis data (casting).
     */
    protected $casts = [
        'pendapatan_bulanan' => 'decimal:2',
    ];

    /**
     * Skop carian — cari mengikut nama atau no IC.
     */
    public function scopeCarian($query, $katakunci)
    {
        return $query->where('nama', 'like', "%{$katakunci}%")
                     ->orWhere('no_ic', 'like', "%{$katakunci}%");
    }

    /**
     * Aksesor: Format no IC dengan sengkang (850101-14-5678).
     */
    public function getNoIcBerformatAttribute(): string
    {
        $ic = $this->no_ic;

        if (strlen($ic) === 12) {
            return substr($ic, 0, 6) . '-' . substr($ic, 6, 2) . '-' . substr($ic, 8, 4);
        }

        return $ic;
    }

    /**
     * Aksesor: Format pendapatan dengan awalan RM.
     */
    public function getPendapatanBerformatAttribute(): string
    {
        if ($this->pendapatan_bulanan) {
            return 'RM ' . number_format($this->pendapatan_bulanan, 2);
        }

        return 'Tidak dinyatakan';
    }
}
