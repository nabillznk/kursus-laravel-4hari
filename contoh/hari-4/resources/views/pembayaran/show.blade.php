@extends('layouts.app')

@section('title', 'Maklumat Pembayaran')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Maklumat Pembayaran</h1>
        <div class="flex space-x-2 mt-2 sm:mt-0">
            <a href="{{ route('pembayaran.edit', $pembayaran) }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition">Edit</a>
            <a href="{{ route('pembayaran.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition">Kembali</a>
        </div>
    </div>

    <div class="max-w-xl">
        <x-card>
            <dl class="space-y-4">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">No. Resit</dt>
                    <dd class="text-lg font-mono font-semibold text-gray-800">{{ $pembayaran->no_resit }}</dd>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Pembayar</dt>
                        <dd class="text-gray-800">
                            <a href="{{ route('pembayar.show', $pembayaran->pembayar) }}" class="text-emerald-600 hover:underline">
                                {{ $pembayaran->pembayar->nama }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Jenis Zakat</dt>
                        <dd class="text-gray-800">{{ $pembayaran->jenisZakat->nama }}</dd>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Jumlah</dt>
                        <dd class="text-2xl font-bold text-emerald-600">{{ $pembayaran->jumlah_format }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Tarikh Bayar</dt>
                        <dd class="text-gray-800">{{ $pembayaran->tarikh_bayar->format('d/m/Y') }}</dd>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Cara Bayar</dt>
                        <dd class="text-gray-800 capitalize">{{ $pembayaran->cara_bayar }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Status</dt>
                        <dd>
                            <x-badge :type="$pembayaran->status">{{ ucfirst($pembayaran->status) }}</x-badge>
                        </dd>
                    </div>
                </div>
                <div class="pt-3 border-t text-xs text-gray-400">
                    Dicipta: {{ $pembayaran->created_at->format('d/m/Y H:i') }}
                    &middot;
                    Dikemaskini: {{ $pembayaran->updated_at->format('d/m/Y H:i') }}
                </div>
            </dl>
        </x-card>
    </div>
@endsection
