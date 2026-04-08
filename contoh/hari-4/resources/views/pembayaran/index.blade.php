@extends('layouts.app')

@section('title', 'Senarai Pembayaran')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Senarai Pembayaran</h1>
        <div class="flex space-x-2 mt-2 sm:mt-0">
            <a href="{{ route('eksport.pembayaran', ['status' => request('status')]) }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Eksport CSV
            </a>
            <a href="{{ route('pembayaran.create') }}"
               class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Pembayaran
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('pembayaran.index') }}" class="flex flex-wrap gap-2">
            <input type="text" name="carian" value="{{ request('carian') }}"
                   placeholder="Cari nama pembayar..."
                   class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="sah" {{ request('status') == 'sah' ? 'selected' : '' }}>Sah</option>
                <option value="batal" {{ request('status') == 'batal' ? 'selected' : '' }}>Batal</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition">
                Tapis
            </button>
            @if(request('carian') || request('status'))
                <a href="{{ route('pembayaran.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-gray-600 font-medium">#</th>
                        <th class="text-left px-6 py-3 text-gray-600 font-medium">No. Resit</th>
                        <th class="text-left px-6 py-3 text-gray-600 font-medium">Pembayar</th>
                        <th class="text-left px-6 py-3 text-gray-600 font-medium">Jenis Zakat</th>
                        <th class="text-right px-6 py-3 text-gray-600 font-medium">Jumlah</th>
                        <th class="text-left px-6 py-3 text-gray-600 font-medium">Tarikh</th>
                        <th class="text-left px-6 py-3 text-gray-600 font-medium">Cara Bayar</th>
                        <th class="text-center px-6 py-3 text-gray-600 font-medium">Status</th>
                        <th class="text-center px-6 py-3 text-gray-600 font-medium">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pembayarans as $i => $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-gray-500">{{ $pembayarans->firstItem() + $i }}</td>
                            <td class="px-6 py-3 font-mono text-xs">{{ $p->no_resit }}</td>
                            <td class="px-6 py-3 font-medium text-gray-800">{{ $p->pembayar->nama ?? '-' }}</td>
                            <td class="px-6 py-3">{{ $p->jenisZakat->nama ?? '-' }}</td>
                            <td class="px-6 py-3 text-right font-medium">{{ $p->jumlah_format }}</td>
                            <td class="px-6 py-3">{{ $p->tarikh_bayar->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 capitalize">{{ $p->cara_bayar }}</td>
                            <td class="px-6 py-3 text-center">
                                <x-badge :type="$p->status">{{ ucfirst($p->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('pembayaran.show', $p) }}" class="text-blue-600 hover:text-blue-800" title="Lihat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('pembayaran.edit', $p) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('pembayaran.destroy', $p) }}" class="inline" onsubmit="return confirm('Adakah anda pasti untuk memadam pembayaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Padam">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-6 py-8 text-center text-gray-400">Tiada pembayaran ditemui.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pembayarans->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $pembayarans->links() }}
            </div>
        @endif
    </div>
@endsection
