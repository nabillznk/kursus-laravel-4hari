@extends('layouts.app')

@section('title', $pembayar->nama)

@section('content')
    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('pembayar.index') }}" class="hover:text-emerald-600">Senarai Pembayar</a>
        <span class="mx-1">/</span>
        <span class="text-gray-800">{{ $pembayar->nama }}</span>
    </nav>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">{{ $pembayar->nama }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('pembayar.edit', $pembayar) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                Kemaskini
            </a>
            <form action="{{ route('pembayar.destroy', $pembayar) }}" method="POST"
                  onsubmit="return confirm('Adakah anda pasti mahu memadam pembayar ini?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    Padam
                </button>
            </form>
            <a href="{{ route('pembayar.index') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition">
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-500">Nama Penuh</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->nama }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">No. Kad Pengenalan</p>
                <p class="text-lg font-medium text-gray-900 font-mono">{{ $pembayar->ic_format }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Alamat</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->alamat }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">No. Telefon</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->no_tel }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">E-mel</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->email ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Pekerjaan</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->pekerjaan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Pendapatan Bulanan</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->pendapatan_format }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tarikh Didaftarkan</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tarikh Dikemaskini</p>
                <p class="text-lg font-medium text-gray-900">{{ $pembayar->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
@endsection
