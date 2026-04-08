@extends('layouts.app')

@section('title', 'Tambah Jenis Zakat')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tambah Jenis Zakat</h1>
        <p class="text-gray-500 mt-1">Isi maklumat jenis zakat baru.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-xl">
        @if($errors->any())
            <x-alert type="error">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form method="POST" action="{{ route('jenis-zakat.store') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Zakat <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>

                <div>
                    <label for="kadar" class="block text-sm font-medium text-gray-700 mb-1">Kadar (%) <span class="text-red-500">*</span></label>
                    <input type="number" id="kadar" name="kadar" value="{{ old('kadar') }}" step="0.0001" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>

                <div>
                    <label for="penerangan" class="block text-sm font-medium text-gray-700 mb-1">Penerangan</label>
                    <textarea id="penerangan" name="penerangan" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('penerangan') }}</textarea>
                </div>

                <div class="flex items-center">
                    <input type="hidden" name="is_aktif" value="0">
                    <input type="checkbox" id="is_aktif" name="is_aktif" value="1" {{ old('is_aktif', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                    <label for="is_aktif" class="ml-2 text-sm text-gray-700">Aktif</label>
                </div>
            </div>

            <div class="flex items-center space-x-3 mt-6">
                <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                    Simpan
                </button>
                <a href="{{ route('jenis-zakat.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
