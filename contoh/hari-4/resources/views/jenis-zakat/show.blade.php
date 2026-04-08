@extends('layouts.app')

@section('title', 'Maklumat Jenis Zakat')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Maklumat Jenis Zakat</h1>
        <div class="flex space-x-2 mt-2 sm:mt-0">
            <a href="{{ route('jenis-zakat.edit', $jenis_zakat) }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition">Edit</a>
            <a href="{{ route('jenis-zakat.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition">Kembali</a>
        </div>
    </div>

    <div class="max-w-xl">
        <x-card>
            <dl class="space-y-4">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Nama</dt>
                    <dd class="text-lg font-semibold text-gray-800">{{ $jenis_zakat->nama }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Kadar</dt>
                    <dd class="text-gray-800">{{ number_format($jenis_zakat->kadar, 2) }}%</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Penerangan</dt>
                    <dd class="text-gray-800">{{ $jenis_zakat->penerangan ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Status</dt>
                    <dd>
                        @if($jenis_zakat->is_aktif)
                            <x-badge type="aktif">Aktif</x-badge>
                        @else
                            <x-badge type="tidak_aktif">Tidak Aktif</x-badge>
                        @endif
                    </dd>
                </div>
                <div class="pt-3 border-t">
                    <dt class="text-xs font-medium text-gray-500 uppercase">Bilangan Pembayaran</dt>
                    <dd class="text-2xl font-bold text-emerald-600">{{ $jenis_zakat->pembayarans_count }}</dd>
                </div>
            </dl>
        </x-card>
    </div>
@endsection
