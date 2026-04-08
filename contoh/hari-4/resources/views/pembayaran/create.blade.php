@extends('layouts.app')

@section('title', 'Tambah Pembayaran')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Rekod Pembayaran Baru</h1>
        <p class="text-gray-500 mt-1">Isi maklumat pembayaran zakat.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        @if($errors->any())
            <x-alert type="error">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form method="POST" action="{{ route('pembayaran.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="pembayar_id" class="block text-sm font-medium text-gray-700 mb-1">Pembayar <span class="text-red-500">*</span></label>
                    <select id="pembayar_id" name="pembayar_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                        <option value="">-- Pilih Pembayar --</option>
                        @foreach($pembayars as $pembayar)
                            <option value="{{ $pembayar->id }}" {{ old('pembayar_id') == $pembayar->id ? 'selected' : '' }}>
                                {{ $pembayar->nama }} ({{ $pembayar->ic_format }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="jenis_zakat_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Zakat <span class="text-red-500">*</span></label>
                    <select id="jenis_zakat_id" name="jenis_zakat_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                        <option value="">-- Pilih Jenis Zakat --</option>
                        @foreach($jenisZakats as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('jenis_zakat_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }} ({{ number_format($jenis->kadar, 2) }}%)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-1">Jumlah (RM) <span class="text-red-500">*</span></label>
                    <input type="number" id="jumlah" name="jumlah" value="{{ old('jumlah') }}" step="0.01" min="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>

                <div>
                    <label for="tarikh_bayar" class="block text-sm font-medium text-gray-700 mb-1">Tarikh Bayar <span class="text-red-500">*</span></label>
                    <input type="date" id="tarikh_bayar" name="tarikh_bayar" value="{{ old('tarikh_bayar', date('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>

                <div>
                    <label for="cara_bayar" class="block text-sm font-medium text-gray-700 mb-1">Cara Bayar <span class="text-red-500">*</span></label>
                    <select id="cara_bayar" name="cara_bayar"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                        <option value="">-- Pilih Cara Bayar --</option>
                        <option value="tunai" {{ old('cara_bayar') == 'tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="kad" {{ old('cara_bayar') == 'kad' ? 'selected' : '' }}>Kad</option>
                        <option value="fpx" {{ old('cara_bayar') == 'fpx' ? 'selected' : '' }}>FPX</option>
                        <option value="online" {{ old('cara_bayar') == 'online' ? 'selected' : '' }}>Online</option>
                    </select>
                </div>

                <div>
                    <label for="no_resit" class="block text-sm font-medium text-gray-700 mb-1">No. Resit <span class="text-red-500">*</span></label>
                    <input type="text" id="no_resit" name="no_resit" value="{{ old('no_resit', $noResit) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50" readonly>
                </div>

                <div class="md:col-span-2">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="sah" {{ old('status') == 'sah' ? 'selected' : '' }}>Sah</option>
                        <option value="batal" {{ old('status') == 'batal' ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center space-x-3 mt-6">
                <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                    Simpan
                </button>
                <a href="{{ route('pembayaran.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
