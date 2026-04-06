@extends('layouts.app')

@section('title', 'Senarai Pembayar Zakat')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Senarai Pembayar Zakat</h1>
        <a href="{{ route('pembayar.create') }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg transition">
            + Daftar Pembayar Baru
        </a>
    </div>

    {{-- Borang carian --}}
    <form action="{{ route('pembayar.index') }}" method="GET" class="mb-6">
        <div class="flex gap-2">
            <input type="text" name="carian" value="{{ $carian ?? '' }}"
                   placeholder="Cari nama atau No. KP..."
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition">
                Cari
            </button>
            @if ($carian)
                <a href="{{ route('pembayar.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                    Padam Carian
                </a>
            @endif
        </div>
    </form>

    {{-- Jadual pembayar --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">No. IC</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">No. Tel</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">E-mel</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Pendapatan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-emerald-800 uppercase">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pembayars as $index => $pembayar)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $pembayars->firstItem() + $index }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $pembayar->nama }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $pembayar->ic_format }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $pembayar->no_tel }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $pembayar->email ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $pembayar->pendapatan_format }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('pembayar.show', $pembayar) }}"
                                       class="text-emerald-600 hover:text-emerald-800 font-medium">Lihat</a>
                                    <a href="{{ route('pembayar.edit', $pembayar) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium">Kemaskini</a>
                                    <form action="{{ route('pembayar.destroy', $pembayar) }}" method="POST"
                                          onsubmit="return confirm('Adakah anda pasti mahu memadam pembayar ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 font-medium">Padam</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                @if ($carian)
                                    Tiada pembayar dijumpai untuk carian "{{ $carian }}".
                                @else
                                    Tiada rekod pembayar. <a href="{{ route('pembayar.create') }}" class="text-emerald-600 hover:underline">Daftar pembayar baru</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination dan kiraan --}}
    @if ($pembayars->hasPages())
        <div class="mt-4">
            {{ $pembayars->links() }}
        </div>
    @endif

    <div class="mt-3 text-sm text-gray-500">
        Menunjukkan {{ $pembayars->firstItem() ?? 0 }} hingga {{ $pembayars->lastItem() ?? 0 }}
        daripada {{ $pembayars->total() }} rekod.
    </div>
@endsection
