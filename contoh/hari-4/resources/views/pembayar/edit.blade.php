@extends('layouts.app')

@section('title', 'Edit Pembayar')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Pembayar</h1>
        <p class="text-gray-500 mt-1">Kemaskini maklumat pembayar zakat.</p>
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

        <form method="POST" action="{{ route('pembayar.update', $pembayar) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Penuh <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $pembayar->nama) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>

                <div>
                    <label for="no_ic" class="block text-sm font-medium text-gray-700 mb-1">No. IC (12 digit) <span class="text-red-500">*</span></label>
                    <input type="text" id="no_ic" name="no_ic" value="{{ old('no_ic', $pembayar->no_ic) }}" maxlength="12"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>

                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat <span class="text-red-500">*</span></label>
                    <textarea id="alamat" name="alamat" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>{{ old('alamat', $pembayar->alamat) }}</textarea>
                </div>

                <div>
                    <label for="no_tel" class="block text-sm font-medium text-gray-700 mb-1">No. Telefon <span class="text-red-500">*</span></label>
                    <input type="text" id="no_tel" name="no_tel" value="{{ old('no_tel', $pembayar->no_tel) }}" maxlength="15"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mel</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $pembayar->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                    <input type="text" id="pekerjaan" name="pekerjaan" value="{{ old('pekerjaan', $pembayar->pekerjaan) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label for="pendapatan_bulanan" class="block text-sm font-medium text-gray-700 mb-1">Pendapatan Bulanan (RM)</label>
                    <input type="number" id="pendapatan_bulanan" name="pendapatan_bulanan" value="{{ old('pendapatan_bulanan', $pembayar->pendapatan_bulanan) }}" step="0.01" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="md:col-span-2">
                    <label for="gambar" class="block text-sm font-medium text-gray-700 mb-1">Gambar Profil</label>
                    @if($pembayar->gambar)
                        <div class="mb-2">
                            <img src="{{ Storage::url($pembayar->gambar) }}" alt="{{ $pembayar->nama }}" class="w-20 h-20 rounded-full object-cover border-2 border-emerald-200">
                            <p class="text-xs text-gray-500 mt-1">Gambar semasa. Pilih fail baru untuk menggantikan.</p>
                        </div>
                    @endif
                    <input type="file" id="gambar" name="gambar" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG. Maksimum 2MB.</p>
                    @error('gambar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center space-x-3 mt-6">
                <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                    Kemaskini
                </button>
                <a href="{{ route('pembayar.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
