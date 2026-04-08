@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500 mt-1">Selamat Datang, {{ Auth::user()->name }}!</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
            label="Jumlah Pembayar"
            :value="$jumlahPembayar"
            color="blue"
        />

        <x-stat-card
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            label="Jumlah Kutipan"
            :value="'RM ' . number_format($jumlahKutipan, 2)"
            color="green"
        />

        <x-stat-card
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
            label="Transaksi Bulan Ini"
            :value="$transaksiBulanIni"
            color="yellow"
        />

        <x-stat-card
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
            label="Jenis Zakat Aktif"
            :value="$jenisZakatAktif"
            color="purple"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pembayaran Terkini --}}
        <x-card title="Pembayaran Terkini">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 text-gray-600 font-medium">Pembayar</th>
                            <th class="text-left py-2 text-gray-600 font-medium">Jenis</th>
                            <th class="text-right py-2 text-gray-600 font-medium">Jumlah</th>
                            <th class="text-center py-2 text-gray-600 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayaranTerkini as $p)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2">{{ $p->pembayar->nama ?? '-' }}</td>
                                <td class="py-2">{{ $p->jenisZakat->nama ?? '-' }}</td>
                                <td class="py-2 text-right font-medium">{{ $p->jumlah_format }}</td>
                                <td class="py-2 text-center">
                                    <x-badge :type="$p->status">{{ ucfirst($p->status) }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-gray-400">Tiada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Ringkasan Bulanan --}}
        <x-card title="Ringkasan Bulanan (6 Bulan Terakhir)">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 text-gray-600 font-medium">Bulan</th>
                            <th class="text-center py-2 text-gray-600 font-medium">Bilangan Transaksi</th>
                            <th class="text-right py-2 text-gray-600 font-medium">Jumlah Kutipan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ringkasanBulanan as $r)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2">{{ $r->bulan }}</td>
                                <td class="py-2 text-center">{{ $r->bilangan }}</td>
                                <td class="py-2 text-right font-medium">RM {{ number_format($r->jumlah_kutipan, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-gray-400">Tiada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
