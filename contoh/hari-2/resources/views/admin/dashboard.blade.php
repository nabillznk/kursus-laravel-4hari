@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Pentadbir</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Jumlah Pembayar --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-emerald-500">
            <p class="text-sm text-gray-500 mb-1">Jumlah Pembayar</p>
            <p class="text-3xl font-bold text-emerald-700">{{ \App\Models\Pembayar::count() }}</p>
            <p class="text-sm text-gray-400 mt-2">Pembayar zakat berdaftar</p>
        </div>

        {{-- Placeholder --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500 mb-1">Jenis Zakat</p>
            <p class="text-3xl font-bold text-blue-700">-</p>
            <p class="text-sm text-gray-400 mt-2">Akan ditambah pada Hari 3</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-amber-500">
            <p class="text-sm text-gray-500 mb-1">Jumlah Pembayaran</p>
            <p class="text-3xl font-bold text-amber-700">-</p>
            <p class="text-sm text-gray-400 mt-2">Akan ditambah pada Hari 3</p>
        </div>
    </div>
@endsection
