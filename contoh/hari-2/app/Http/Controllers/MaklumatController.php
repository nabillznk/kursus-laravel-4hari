<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

class MaklumatController extends Controller
{
    /**
     * Papar senarai semua laluan berdaftar.
     */
    public function laluan()
    {
        $laluans = collect(Route::getRoutes())->map(function ($route) {
            return [
                'nama' => $route->getName() ?? '-',
                'kaedah' => $route->methods(),
                'uri' => $route->uri(),
                'pengawal' => $route->getActionName(),
            ];
        });

        return view('maklumat.laluan', compact('laluans'));
    }
}
