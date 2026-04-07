<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembayar extends Model
{
    use HasFactory;

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
     * Penukaran jenis data automatik.
     */
    protected $casts = [
        'pendapatan_bulanan' => 'decimal:2',
    ];

    /**
     * Skop carian — cari mengikut nama atau no IC.
     */
    public function scopeCarian($query, $carian)
    {
        return $query->where('nama', 'like', "%{$carian}%")
            ->orWhere('no_ic', 'like', "%{$carian}%");
    }

    /**
     * Aksesor: Format no IC dengan sengkang (850101-14-5678).
     */
    public function getIcFormatAttribute(): string
    {
        $ic = $this->no_ic;

        if (strlen($ic) === 12) {
            return substr($ic, 0, 6) . '-' . substr($ic, 6, 2) . '-' . substr($ic, 8, 4);
        }

        return $ic;
    }

    /**
     * Aksesor: Format pendapatan bulanan (RM 3,500.00).
     */
    public function getPendapatanFormatAttribute(): string
    {
        if ($this->pendapatan_bulanan === null) {
            return 'Tiada maklumat';
        }

        return 'RM ' . number_format($this->pendapatan_bulanan, 2);
    }

    // ──────────────────────────────────────────────
    // Hubungan (Relationships)
    // ──────────────────────────────────────────────

    /**
     * Seorang pembayar mempunyai banyak pembayaran.
     * (One-to-Many: Pembayar hasMany Pembayaran)
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Aksesor: Jumlah keseluruhan bayaran sah pembayar.
     */
    public function getJumlahBayaranAttribute(): float
    {
        return (float) $this->pembayarans()->where('status', 'sah')->sum('jumlah');
    }
}