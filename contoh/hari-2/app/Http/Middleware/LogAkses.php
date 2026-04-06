<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogAkses
{
    /**
     * Log setiap permintaan masuk: kaedah, URL, IP, dan masa.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::channel('akses')->info('Akses masuk', [
            'kaedah' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'masa' => now()->format('Y-m-d H:i:s'),
        ]);

        return $next($request);
    }
}
