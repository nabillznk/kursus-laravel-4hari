<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisZakat extends Model
{
    protected $fillable = [
        'nama',
        'kadar',
        'penerangan',
        'is_aktif',
    ];

    protected $casts = [
        'kadar'    => 'decimal:4',
        'is_aktif' => 'boolean',
    ];

    // ── Hubungan ──

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    // ── Skop ──

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }
}
