<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembayar extends Model
{
    protected $fillable = [
        'nama',
        'no_ic',
        'alamat',
        'no_tel',
        'email',
        'pekerjaan',
        'pendapatan_bulanan',
        'gambar',
    ];

    protected $casts = [
        'pendapatan_bulanan' => 'decimal:2',
    ];

    // ── Hubungan ──

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    // ── Skop ──

    public function scopeCarian($query, string $carian)
    {
        return $query->where('nama', 'like', "%{$carian}%")
                     ->orWhere('no_ic', 'like', "%{$carian}%")
                     ->orWhere('email', 'like', "%{$carian}%");
    }

    public function scopePendapatanMelebihi($query, float $jumlah)
    {
        return $query->where('pendapatan_bulanan', '>', $jumlah);
    }

    // ── Accessor ──

    public function getIcFormatAttribute(): string
    {
        $ic = $this->no_ic;
        if (strlen($ic) === 12) {
            return substr($ic, 0, 6) . '-' . substr($ic, 6, 2) . '-' . substr($ic, 8, 4);
        }
        return $ic;
    }

    public function getPendapatanFormatAttribute(): string
    {
        return 'RM ' . number_format($this->pendapatan_bulanan ?? 0, 2);
    }

    public function getJumlahBayaranAttribute(): string
    {
        $jumlah = $this->pembayarans()->where('status', 'sah')->sum('jumlah');
        return 'RM ' . number_format($jumlah, 2);
    }
}
