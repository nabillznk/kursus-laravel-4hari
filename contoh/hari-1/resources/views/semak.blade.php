@extends('layouts.app')

@section('tajuk', 'Semakan Sistem — Sistem Zakat Kedah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Pengepala --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Semakan Sistem</h1>
        <p class="text-sm text-gray-500 mt-1">Maklumat persekitaran dan status sistem semasa.</p>
    </div>

    {{-- Kad Semakan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Versi PHP --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Versi PHP</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $versi_php }}</p>
                </div>
            </div>
        </div>

        {{-- Versi Laravel --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Versi Laravel</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $versi_laravel }}</p>
                </div>
            </div>
        </div>

        {{-- Pangkalan Data --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center
                            {{ $pangkalan_data_ok ? 'bg-emerald-100' : 'bg-red-100' }}">
                    @if ($pangkalan_data_ok)
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pangkalan Data</p>
                    <p class="text-sm font-medium mt-0.5 {{ $pangkalan_data_ok ? 'text-emerald-700' : 'text-red-700' }}">
                        {{ $pangkalan_data_ok ? 'Berjaya' : 'Gagal' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ $pangkalan_data_mesej }}</p>
                </div>
            </div>
        </div>

        {{-- Pelayan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pelayan</p>
                    <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $pelayan }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Maklumat Tambahan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Maklumat Tambahan</h2>
        </div>
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Persekitaran</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     {{ $persekitaran === 'production' ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ ucfirst($persekitaran) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Masa Semakan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ now()->format('d/m/Y, h:i:s A') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sistem Operasi</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ PHP_OS }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sambungan PHP</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @foreach (['pdo_mysql', 'mbstring', 'openssl', 'tokenizer'] as $ext)
                            <span class="inline-flex items-center mr-1 mb-1 px-2 py-0.5 rounded text-xs
                                         {{ extension_loaded($ext) ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                {{ $ext }}
                                @if (extension_loaded($ext))
                                    &#10003;
                                @else
                                    &#10007;
                                @endif
                            </span>
                        @endforeach
                    </dd>
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
            Kembali ke Laman Utama
        </a>
    </div>

</div>
@endsection
