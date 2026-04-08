@extends('layouts.app')

@section('title', 'Senarai Jenis Zakat')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Senarai Jenis Zakat</h1>
        <a href="{{ route('jenis-zakat.create') }}"
           class="mt-2 sm:mt-0 inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jenis Zakat
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-gray-600 font-medium">#</th>
                        <th class="text-left px-6 py-3 text-gray-600 font-medium">Nama</th>
                        <th class="text-right px-6 py-3 text-gray-600 font-medium">Kadar (%)</th>
                        <th class="text-center px-6 py-3 text-gray-600 font-medium">Pembayaran</th>
                        <th class="text-center px-6 py-3 text-gray-600 font-medium">Status</th>
                        <th class="text-center px-6 py-3 text-gray-600 font-medium">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($jenisZakats as $i => $jenis)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-gray-500">{{ $jenisZakats->firstItem() + $i }}</td>
                            <td class="px-6 py-3 font-medium text-gray-800">{{ $jenis->nama }}</td>
                            <td class="px-6 py-3 text-right">{{ number_format($jenis->kadar, 2) }}%</td>
                            <td class="px-6 py-3 text-center">{{ $jenis->pembayarans_count }}</td>
                            <td class="px-6 py-3 text-center">
                                @if($jenis->is_aktif)
                                    <x-badge type="aktif">Aktif</x-badge>
                                @else
                                    <x-badge type="tidak_aktif">Tidak Aktif</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('jenis-zakat.show', $jenis) }}" class="text-blue-600 hover:text-blue-800" title="Lihat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('jenis-zakat.edit', $jenis) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('jenis-zakat.destroy', $jenis) }}" class="inline" onsubmit="return confirm('Adakah anda pasti untuk memadam jenis zakat ini?')">
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
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Tiada jenis zakat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jenisZakats->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $jenisZakats->links() }}
            </div>
        @endif
    </div>
@endsection
