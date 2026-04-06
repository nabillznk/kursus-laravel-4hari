@extends('layouts.app')

@section('tajuk', 'Kemaskini ' . $pembayar->nama . ' — Sistem Zakat Kedah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('pembayar.index') }}" class="hover:text-emerald-600 transition-colors">Pembayar</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('pembayar.show', $pembayar) }}" class="hover:text-emerald-600 transition-colors">{{ $pembayar->nama }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Kemaskini</span>
    </nav>

    {{-- Pengepala --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kemaskini Pembayar</h1>
        <p class="text-sm text-gray-500 mt-1">Kemaskini maklumat untuk <span class="font-medium text-gray-700">{{ $pembayar->nama }}</span></p>
    </div>

    {{-- Borang --}}
    <form action="{{ route('pembayar.update', $pembayar) }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Nama Penuh --}}
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">
                Nama Penuh <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" id="nama" value="{{ old('nama', $pembayar->nama) }}"
                   class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                          {{ $errors->has('nama') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                          focus:ring-2">
            @error('nama')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- No. Kad Pengenalan --}}
        <div>
            <label for="no_ic" class="block text-sm font-medium text-gray-700 mb-1">
                No. Kad Pengenalan <span class="text-red-500">*</span>
            </label>
            <input type="text" name="no_ic" id="no_ic" value="{{ old('no_ic', $pembayar->no_ic) }}"
                   maxlength="12"
                   class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                          {{ $errors->has('no_ic') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                          focus:ring-2"
                   placeholder="850101145678">
            @error('no_ic')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Alamat --}}
        <div>
            <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">
                Alamat <span class="text-red-500">*</span>
            </label>
            <textarea name="alamat" id="alamat" rows="3"
                      class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                             {{ $errors->has('alamat') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                             focus:ring-2">{{ old('alamat', $pembayar->alamat) }}</textarea>
            @error('alamat')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- No. Telefon & E-mel --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="no_tel" class="block text-sm font-medium text-gray-700 mb-1">
                    No. Telefon <span class="text-red-500">*</span>
                </label>
                <input type="text" name="no_tel" id="no_tel" value="{{ old('no_tel', $pembayar->no_tel) }}"
                       maxlength="15"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                              {{ $errors->has('no_tel') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                              focus:ring-2"
                       placeholder="0124567890">
                @error('no_tel')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    E-mel
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $pembayar->email) }}"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                              {{ $errors->has('email') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' }}
                              focus:ring-2"
                       placeholder="contoh@email.com">
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Pekerjaan & Pendapatan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-1">
                    Pekerjaan
                </label>
                <input type="text" name="pekerjaan" id="pekerjaan" value="{{ old('pekerjaan', $pembayar->pekerjaan) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                       placeholder="cth: Guru, Jurutera, Peniaga">
                @error('pekerjaan')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pendapatan_bulanan" class="block text-sm font-medium text-gray-700 mb-1">
                    Pendapatan Bulanan (RM)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm">RM</span>
                    </div>
                    <input type="number" name="pendapatan_bulanan" id="pendapatan_bulanan"
                           value="{{ old('pendapatan_bulanan', $pembayar->pendapatan_bulanan) }}"
                           step="0.01" min="0"
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                           placeholder="0.00">
                </div>
                @error('pendapatan_bulanan')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Butang --}}
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('pembayar.show', $pembayar) }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700 transition-colors duration-200">
                Kemaskini
            </button>
        </div>
    </form>

</div>
@endsection
