@extends('layouts.app')

@section('tajuk', $pembayar->nama . ' — Sistem Zakat Kedah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('pembayar.index') }}" class="hover:text-emerald-600 transition-colors">Pembayar</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">{{ $pembayar->nama }}</span>
    </nav>

    {{-- Pengepala --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $pembayar->nama }}</h1>
            <p class="text-sm text-gray-500 mt-1">Didaftarkan pada {{ $pembayar->created_at->format('d/m/Y, h:i A') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('pembayar.edit', $pembayar) }}"
               class="inline-flex items-center px-4 py-2 bg-amber-50 text-amber-700 text-sm font-medium rounded-lg hover:bg-amber-100 transition-colors duration-200 border border-amber-200">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Kemaskini
            </a>
            <form action="{{ route('pembayar.destroy', $pembayar) }}" method="POST"
                  onsubmit="return confirm('Adakah anda pasti ingin memadamkan pembayar ini?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors duration-200 border border-red-200">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Padam
                </button>
            </form>
        </div>
    </div>

    {{-- Kad Maklumat --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-emerald-50 border-b border-emerald-100 px-6 py-4">
            <h2 class="text-sm font-semibold text-emerald-800 uppercase tracking-wider">Maklumat Peribadi</h2>
        </div>
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Penuh</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $pembayar->nama }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">No. Kad Pengenalan</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $pembayar->no_ic_berformat }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->alamat }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">No. Telefon</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->no_tel }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">E-mel</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->email ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pekerjaan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->pekerjaan ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan Bulanan</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $pembayar->pendapatan_berformat }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tarikh Daftar</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->created_at->format('d/m/Y') }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Dikemaskini</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pembayar->updated_at->format('d/m/Y, h:i A') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Butang Kembali --}}
    <div>
        <a href="{{ route('pembayar.index') }}"
           class="inline-flex items-center text-sm text-gray-600 hover:text-emerald-600 transition-colors duration-200">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Senarai
        </a>
    </div>

</div>
@endsection
