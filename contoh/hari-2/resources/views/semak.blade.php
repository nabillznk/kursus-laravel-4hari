@extends('layouts.app')

@section('title', 'Semakan Sistem')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Semakan Sistem</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- PHP --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-emerald-700 mb-3">Versi PHP</h2>
            <p class="text-3xl font-bold text-gray-800">{{ $semakan['php_version'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Versi PHP yang sedang berjalan</p>
        </div>

        {{-- Laravel --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-emerald-700 mb-3">Versi Laravel</h2>
            <p class="text-3xl font-bold text-gray-800">{{ $semakan['laravel_version'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Versi rangka kerja Laravel</p>
        </div>

        {{-- Pangkalan Data --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-emerald-700 mb-3">Pangkalan Data</h2>
            @if ($semakan['pangkalan_data']['status'])
                <p class="text-xl font-bold text-emerald-600">Bersambung</p>
                <p class="text-sm text-gray-500 mt-1">
                    Pangkalan data: <strong>{{ $semakan['pangkalan_data']['nama'] }}</strong>
                    ({{ $semakan['pangkalan_data']['pemacu'] }})
                </p>
            @else
                <p class="text-xl font-bold text-red-600">Tidak Bersambung</p>
                <p class="text-sm text-red-500 mt-1">{{ $semakan['pangkalan_data']['mesej'] ?? 'Ralat tidak diketahui' }}</p>
            @endif
        </div>

        {{-- Pelayan --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-emerald-700 mb-3">Pelayan Web</h2>
            <p class="text-xl font-bold text-gray-800">{{ $semakan['server'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Pelayan web yang sedang berjalan</p>
        </div>
    </div>
@endsection
