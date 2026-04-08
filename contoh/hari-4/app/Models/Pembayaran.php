<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $fillable = [
        'pembayar_id',
        'jenis_zakat_id',
        'jumlah',
        'tarikh_bayar',
        'cara_bayar',
        'no_resit',
        'status',
    ];

    protected $casts = [
        'jumlah'       => 'decimal:2',
        'tarikh_bayar' => 'date',
    ];

    // ── Hubungan ──

    public function pembayar(): BelongsTo
    {
        return $this->belongsTo(Pembayar::class);
    }

    public function jenisZakat(): BelongsTo
    {
        return $this->belongsTo(JenisZakat::class);
    }

    // ── Accessor ──

    public function getJumlahFormatAttribute(): string
    {
        return 'RM ' . number_format($this->jumlah, 2);
    }

    // ── Skop ──

    public function scopeSah($query)
    {
        return $query->where('status', 'sah');
    }
}
