@extends('layouts.app')

@section('title', 'Maklumat Pembayar')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Maklumat Pembayar</h1>
        <div class="flex space-x-2 mt-2 sm:mt-0">
            <a href="{{ route('pembayar.edit', $pembayar) }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition">Edit</a>
            <a href="{{ route('pembayar.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition">Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Detail Card --}}
        <div class="lg:col-span-1">
            <x-card title="Profil Pembayar">
                @if($pembayar->gambar)
                    <div class="flex justify-center mb-4">
                        <img src="{{ Storage::url($pembayar->gambar) }}" alt="{{ $pembayar->nama }}" class="w-32 h-32 rounded-full object-cover border-4 border-emerald-200 shadow">
                    </div>
                @endif
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Nama</dt>
                        <dd class="text-gray-800 font-medium">{{ $pembayar->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">No. IC</dt>
                        <dd class="text-gray-800">{{ $pembayar->ic_format }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Alamat</dt>
                        <dd class="text-gray-800">{{ $pembayar->alamat }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">No. Telefon</dt>
                        <dd class="text-gray-800">{{ $pembayar->no_tel }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">E-mel</dt>
                        <dd class="text-gray-800">{{ $pembayar->email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Pekerjaan</dt>
                        <dd class="text-gray-800">{{ $pembayar->pekerjaan ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Pendapatan Bulanan</dt>
                        <dd class="text-gray-800">{{ $pembayar->pendapatan_format }}</dd>
                    </div>
                    <div class="pt-3 border-t">
                        <dt class="text-xs font-medium text-gray-500 uppercase">Jumlah Bayaran (Sah)</dt>
                        <dd class="text-lg font-bold text-emerald-600">{{ $pembayar->jumlah_bayaran }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>

        {{-- Pembayaran List --}}
        <div class="lg:col-span-2">
            <x-card title="Senarai Pembayaran">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 text-gray-600 font-medium">No. Resit</th>
                                <th class="text-left py-2 text-gray-600 font-medium">Jenis Zakat</th>
                                <th class="text-right py-2 text-gray-600 font-medium">Jumlah</th>
                                <th class="text-left py-2 text-gray-600 font-medium">Tarikh</th>
                                <th class="text-center py-2 text-gray-600 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pembayar->pembayarans as $p)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 font-mono text-xs">{{ $p->no_resit }}</td>
                                    <td class="py-2">{{ $p->jenisZakat->nama ?? '-' }}</td>
                                    <td class="py-2 text-right font-medium">{{ $p->jumlah_format }}</td>
                                    <td class="py-2">{{ $p->tarikh_bayar->format('d/m/Y') }}</td>
                                    <td class="py-2 text-center">
                                        <x-badge :type="$p->status">{{ ucfirst($p->status) }}</x-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-gray-400">Tiada pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
