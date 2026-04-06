<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SemakController extends Controller
{
    /**
     * Papar halaman semakan sistem.
     */
    public function index()
    {
        $semakan = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP Built-in Server',
            'pangkalan_data' => $this->semakPangkalanData(),
        ];

        return view('semak', compact('semakan'));
    }

    /**
     * Semak sambungan pangkalan data.
     */
    private function semakPangkalanData(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => true,
                'nama' => config('database.connections.' . config('database.default') . '.database'),
                'pemacu' => config('database.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mesej' => $e->getMessage(),
            ];
        }
    }
}
