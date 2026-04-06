<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SemakWaktuPejabat
{
    /**
     * Semak sama ada permintaan dibuat dalam waktu pejabat.
     * Waktu pejabat: Isnin-Jumaat, 8:00 pagi - 5:00 petang.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hari = now()->dayOfWeek; // 0 = Ahad, 6 = Sabtu
        $jam = now()->hour;

        // Hari bekerja: Isnin (1) hingga Jumaat (5)
        $hariBekerja = $hari >= 1 && $hari <= 5;

        // Waktu pejabat: 8:00 pagi hingga 5:00 petang
        $waktuPejabat = $jam >= 8 && $jam < 17;

        if (!$hariBekerja || !$waktuPejabat) {
            return response()->view('errors.luar-waktu', [], 403);
        }

        return $next($request);
    }
}
