@extends('layouts.app')

@section('title', 'Senarai Laluan Berdaftar')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Senarai Laluan Berdaftar</h1>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Kaedah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">URI</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Pengawal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($laluans as $laluan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $laluan['nama'] }}</td>
                            <td class="px-4 py-3 text-sm">
                                @foreach ($laluan['kaedah'] as $kaedah)
                                    @php
                                        $warna = match($kaedah) {
                                            'GET' => 'bg-emerald-100 text-emerald-800',
                                            'POST' => 'bg-blue-100 text-blue-800',
                                            'PUT', 'PATCH' => 'bg-amber-100 text-amber-800',
                                            'DELETE' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold {{ $warna }}">
                                        {{ $kaedah }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">/{{ $laluan['uri'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 font-mono text-xs">{{ $laluan['pengawal'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="mt-4 text-sm text-gray-500">Jumlah laluan: {{ count($laluans) }}</p>
@endsection
